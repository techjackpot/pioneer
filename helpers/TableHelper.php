<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

class TableHelper {
	public $model = null;
	public $data = null;
	public $filters = array();
	
	public $default_sort = array('updated_at', 'DESC');
	public $per_page = 30;
	
	public function __construct($model = null) {
		$this->model = $model;
	}
	
	public function find_params() {
		$filters = array();
		foreach($this->filters as $key) {
			if (array_key_exists($key, $_GET)) {
				if ($key === 'search') {
					$filters['search'] = unslash($_GET['search']);
				} else {
					
					$getarr = @explode(",", $_GET[$key]);			
					if (is_array($getarr))
					{
						foreach($getarr as $itm)
						{
							$filterarr[] = key_is_id($itm) ? to_int($itm) : unslash($itm);
						}
						$filters['where'][$key] = $filterarr;
					}
					else
					{
						$filters['where'][$key] = key_is_id($key) ? to_int($_GET[$key]) : unslash($_GET[$key]);
					}
				}
			}
		}
		
		return $filters + array( 'total' => true ) + $this->sort_sql();
	}
	
	public function header($name, $sort = null, $label = null) {
		$model_name = &$this->model;
		list($sort_key, $_, $direction) = $this->compute_sort();
		if ($sort === null) $sort = $model_name::properties($name, 'sort');
		if ($label === null) $label = $this->h($model_name::label($name));
		if ($sort === false) { echo $label; }
		else {
			if ($name === $sort_key) { $dir = $direction === 'DESC' ? ' ▼' : ' ▲'; }
			else { $dir = ''; }
			echo "<a href='".$this->url_with_sort($name, $name === $sort_key ? ($direction === 'DESC') : null )."'>{$label}$dir</a>";
		}
	}
	
	public function actions($model) {
		echo "<div class='dropdown'>
					<b class='caret'></b>
					<a class='dropdown-toggle' data-toggle='dropdown' data-target='#' href='#'>Actions</a>
					<ul class='dropdown-menu'>";
		$actions = func_num_args() > 1 ? array_slice(func_get_args(), 1) : array('edit', 'delete');
		foreach( $actions as $index => $value) {
			if ($value === 'edit') {
				echo "<li><a href='".admin_url($model, $model['id']).($model['append'] ? '?'.$model['append'] : '')."'>".icon('edit', 'Edit')."</a></li>";
			}
			else if ($value === 'delete') {
				echo "<li><a href='".admin_url($model, -$model['id']).($model['append'] ? '?'.$model['append'] : '')."' data-confirm='true' rel='nofollow'>".icon('trash', 'Delete')."</a></li>";
			}
			else if ($value === null) {
				echo "<li class='divider'></li>";
			}
			else {
				echo "<li>".sprintf($value, $model['id'])."</li>";
			}
		}
		echo "</ul>
			</div>";
	}
	
	public function filters() {
		$model_name = &$this->model;
		echo '<form class="form-inline filters" method="GET" action="'. url( $GLOBALS['path'] ) .'?'.$_SERVER['QUERY_STRING'].'">';
		
		if (is_array($_GET))
		{
			foreach($_GET as $i=>$v)
			{
				echo '<input type="hidden" name="'.$i.'" value="'.$v.'" />
				';
			}
		}
		
		$name_displayed = false;
		foreach($this->filters as $key) {
			if ($key === 'search') {
				$query = unslash($_GET['search']);
				echo '<span class="search"><i class="icon-search"></i> Search: &nbsp; <input type="text" name="search" value="'.h($query).'" /></span>';
			}
			else {
				$foreign = $model_name::properties($key, 'foreign');
				$filterprop = $model_name::properties($key, 'filterprop');
				$storestring = $model_name::properties($key, 'storestring');
				if ($foreign)
				{
					$fmodel = new TableHelper($foreign);
					$fvals = $foreign::find();
					$vals = array();
					foreach($fvals as $index => $model) {
						$vals[$model['id']] = $model->display('name');
					}
				}
				else {
					$vals = $model_name::properties($key, 'values');
				}
				
				$getarr = @explode(",", $_GET[$key]);
				
				if (is_array($getarr) && $filterprop == 'multiple')
				{
					foreach($getarr as $itm)
					{
						$get[] = key_is_id($itm) ? to_int($itm) : unslash($itm);
						
						foreach($vals as $id => $val) {
							if ($id == $itm)
							$selecteditems[] = $val;
						}
					}
					
					if (is_array($selecteditems))
					$selecteditems = implode(", ", $selecteditems);
				}
				else
				{
					$get = key_is_id($key) ? to_int($_GET[$key]) : unslash($_GET[$key]);
				}
				
				if (!$name_displayed) {
					echo '<i class="icon-filter"></i> Filters: ';
					$name_displayed = true;
				}
				echo ' <span class="btn-group"> ';
				echo ' <button type="button" data-toggle="dropdown" class="btn dropdown-toggle">'.$model_name::label($key).(is_array($get) && $filterprop == 'multiple' ? ': '.$selecteditems : (blank($get)?'' : ': '.($storestring ? $get :$vals[$get]))).' <span class="caret"></span></button> ';
				echo ' <ul class="dropdown-menu"> ';
					echo '<li'.($get === null ? ' class ="active"':'').'><a href="'. url( $GLOBALS['path'] ) .'">No filter</a></li>';

					foreach($vals as $id => $val) {
					$getarray = $_GET;
					unset($getarray[$key]);
					$getString = urldecode(http_build_query($getarray));
					
					$selectedids = "";
					$gettmp = $get;
					if (is_array($get) && $filterprop == 'multiple')
					{
					if (in_array($id, $gettmp))
					{
						foreach ($gettmp as $getitem)
						{
							if ($getitem != $id)
							$selectedids[] = $getitem;
						}
						if (is_array($selectedids))
						$selectedids = implode(",", $selectedids);
						else
						$selectedids = $selectedids[0];
						
					}
					else
					{
						$selectedids = (is_array($gettmp) ? implode(",", $gettmp)."," : (!empty($gettmp[0]) ? $gettmp[0]."," : "")).$id;
					}
						
					
					echo '<li'.(in_array($id, $get) ? ' class ="active"':'').'><a href="'. url( $GLOBALS['path'] ) .'?' . $getString .(!empty($getString) ? "&" : ""). $key .'='. $selectedids . '">'.h($val).'</a></li>';
					}
					else if ($storestring)
					echo '<li'.($get === $val ? ' class ="active"':'').'><a href="'. url( $GLOBALS['path'] ) .'?' . $getString .(!empty($getString) ? "&" : ""). $key .'='. h($val) . '">'.h($val).'</a></li>';
					else
					echo '<li'.($get === $id ? ' class ="active"':'').'><a href="'. url( $GLOBALS['path'] ) .'?' . $getString .(!empty($getString) ? "&" : ""). $key .'='. h($id) . '">'.h($val).'</a></li>';
					}
				echo ' </ul> ';
				echo ' </span> ';
			}
		}
		echo '</form>';
	}
	
	public function export_url() {
		$params = $_GET;
		$params['export'] = 'csv';
		return url( $GLOBALS['path'] ).'?'.http_build_query($params);
	}
	
	private $_sort = null;
	public function compute_sort() {
		if ($this->_sort === null) {
			if (blank($_GET['sort'])) {
				list($sort_key, $direction) = $this->default_sort;
			}
			else {
				$sort_key = preg_replace('/[^\w]+/', '', $_GET['sort']);
				if (strtolower($_GET['sort_dir']) === 'desc') { $direction = 'DESC'; }
				else if (strtolower($_GET['sort_dir']) === 'asc') { $direction = 'ASC'; }
				else { $direction = null; }
			}
			$model_name = &$this->model;
		
			$sort = $model_name::properties($sort_key, 'sort') or $sort = $sort_key;
			if ($direction === null) {
				$direction = $model_name::properties($sort_key, 'sort_descending') ? 'DESC' : 'ASC';
			}
			
			$this->_sort = array($sort_key, $sort, $direction);
		}
		return $this->_sort;
	}
	
	public function sort_sql() {
		$page = intval($_GET['page']) or $page = 1;
		$pages = intval($_GET['pages']) or $pages = 1;
		$ret = '';
		
		list($sort_key, $sort, $direction) = $this->compute_sort();
		if (is_array($sort)) {
			foreach($sort as $index => $val) {
				if ($index >= 1) $ret .= ", ";
				$ret .= "$val $direction";
			}
		}
		else if ($sort == "updated_at" || $sort == "created_at" || $sort == "status_code" || $sort == "organization_id" || $sort == "country" || $sort == "title" || $sort == "venue_id" || $sort == "id" || $sort == "type_id" || $sort == "amount" || $sort == "status_id") { $ret .= "$sort $direction"; }
		else if ($sort !== false ) { $ret .= " if(".$sort." = '' or ".$sort." is null,1,0),".$sort." ".$direction; } // Enhance sort to explude blanks in beginning
		//$ret .= ", `id` $direction";
		
		return $this->per_page ? array( 'sort' => $ret, 'limit' => ((($page-1)*$this->per_page).",".($this->per_page*$pages))) : array( 'sort' => $ret );
	}
	
	public function url_with_sort($key, $ascending = null) {
		$params = $_GET;
		$params['sort'] = $key;
		unset($params['page']);
		if ($ascending === null) { unset($params['sort_dir']); }
		elseif ($ascending) { $params['sort_dir'] = 'asc'; }
		else { $params['sort_dir'] = 'desc'; }
		
		return BASE_PATH.implode('/', array_map('urlencode', $GLOBALS['path'])).'?'.http_build_query($params, '', '&amp;');
	}
	
	public function pagination($total) {
		if (!$where === null && !blank($this->scope)) { $where = $this->scope; }
		$current = intval($_GET['page']) or $current = 1;
		$pages = ceil($total / $this->per_page);
		if ($pages == 1 || $pages == 0) return;
	
		$show = range(max(1, $current-2), min(intval($pages), $current+2));
	
		$first = $show[0];
		if ($first-10 > 1) { array_unshift($show, $current-10); }
		if ($first == 3) { array_unshift($show, 1, 2); }
		else if ($first == 2) { array_unshift($show, 1); }
		else if ($first > 3) { array_unshift($show, 1, null); }
	
		$last = $show[count($show)-1];
		if ($last+10 < $pages) { array_push($show, $current+10); }
		if ($last == $pages-2) { array_push($show, $pages-1, $pages); }
		else if ($last == $pages-1) { array_push($show, $pages); }
		else if ($last < $pages-2) { array_push($show, null, $pages); }
	
		$query = $_GET;
		$url = BASE_PATH.implode('/', array_map('urlencode', $GLOBALS['path']));
	
		if ($current > 1) {
			$query['page'] = $current-1;
			$lis .= '<li><a href="'.$url.'?'.http_build_query($query, '', '&amp;').'">← Previous</a></li>';
		}
		foreach($show as $i) {
			if (empty($i)) {
				$lis .= '<li class="disabled"><a>…</a></li>';
			}
			else {
				$query['page'] = $i;
				$lis .= "<li".($current===$i?' class="active"':'')."><a href=\"$url?".http_build_query($query, '', '&amp;')."\">$i</a></li>";
			}
		}
		if ($current < $pages) {
			$query['page'] = $current+1;
			$lis .= '<li><a href="'.$url.'?'.http_build_query($query, '', '&amp;').'">Next →</a></li>';
		}
		return '<div class="pagination pagination-centered"><ul>'.$lis.'</ul></div>';
	}
	
	public function ajax_pagination( $total ) {
		$pages = intval($_GET['pages']) or $pages = 1;
		return $total > $this->per_page ? '<p class="js-paginator text-center" data-page="'.$pages.'"><a href="#" class="load-more">Scroll to load more</a></p>' : '';
	}
	
	public function activity_wall_title() {
		$model = $this->model;
		return '<a href="'.BASE_PATH.'admin/changelog/'.($model::$table).'" class="pull-right">View All</a>Activity Wall';
	}
	
	public function no_records_message($count, $cols, $msg = null, $activity_wall = false) {
		if (!$count) {
			$model_name = &$this->model;
			if ($msg === null) { $msg = 'No records found'; }
			echo '<tr>';
			echo '<td colspan="'.($cols).'"><div class="text-center muted">'.$msg.'</div></td>';
			if ($activity_wall) { echo '<td rowspan="2" class="activity-wall">'; ChangeLog::display_model_all($model_name::$table); echo '</td>'; }
			echo '</tr>';
		}
	}
	
	public function export_fields($fields) {
		$model = $this->model;
		$ret = [];
		foreach($fields as $field => $default) {
			$ret[] = [ $field, $model::label($field), $default ];
		}		
		return $ret;
	}
	
	public static function h() {
		return call_user_func_array("htmlspecialchars", func_get_args());
	}
}
