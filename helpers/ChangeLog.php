<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

class ChangeLog {
	public static $record_user = true;
	
	private static $type_description = array(
		'create' => 'created',
		'update' => 'modified',
		'delete' => 'deleted',
		'comment' => 'commented on',
	);
	
	public static function display_model($model) {
		$limit = 10;
		
		$model_class = get_class($model);
		
		$query = sql("SELECT changelog.*, users.username FROM changelog
LEFT JOIN users ON changelog.user_id = users.id
WHERE changelog.model_class = '$model_class' and changelog.model_id = %s
ORDER BY changelog.updated_at DESC, changelog.id DESC LIMIT $limit", $model['id']);

		echo "<div class='activity-item'><textarea class='input-block-level input-text-small js-activity-comment' data-model-name='{$model::$table}' data-model-id='{$model['id']}' rows='3' placeholder='Type comments here'></textarea><a class='btn btn-primary' id='js-activity-comment-button' style='color: #fff'>SUBMIT</a></div>";
		while($row = $query->fetch_assoc()) {
			echo self::output_item($row);
		}
	}

	public static function display_model_all($model) {
		$limit = 8;
		
		$query = sql("SELECT changelog.*, users.username FROM changelog
LEFT JOIN users ON changelog.user_id = users.id
WHERE changelog.model_class = '$model'
ORDER BY changelog.updated_at DESC, changelog.id DESC LIMIT $limit");
		
		while($row = $query->fetch_assoc()) {
			echo self::output_item($row);
		}
	}
	
	public static function display_item($id) {
		$row = sqla("SELECT changelog.*, users.username FROM changelog LEFT JOIN users ON changelog.user_id = users.id WHERE changelog.id =  %s", $id);
		return self::output_item($row);
	}
	
	public static function output_row(&$row) {
		$user = $row['user_id'] ? ("<a class='user' href='". admin_url('users', $row['user_id']) ."'>". h($row['username']) ."</a>") : 'System';
		echo "<tr>
			<td class='span4 text-right'>$user ".self::$type_description[$row['submodel_class'] ? 'update' : $row['type']]." <a href='". admin_url($row['model_class'], $row['model_id']) ."'>". h($row['model_name']) ."</a></td>
			<td class='activity-item'>";

		$model_class = $row['submodel_class'] === null ? $row['model_class'] : $row['submodel_class'];
		$model = new $model_class( json_decode($row['state'], true) );
		if ($row['submodel_class']) {
			echo "<div class='submodel'>".ucfirst(self::$type_description[$row['type']]).' '.$model_class::human_name()."</div>";
		}
		if ($row['type'] === 'comment') {
			echo '<div class="comment">'.h($row['previous_state']).'</div>';
		}
		if ($row['type'] === 'update') {
			$old_data = json_decode($row['previous_state'], true);
			$old_model = new $model_class( json_decode($row['state'], true) );
			$old_model->apply($old_data);
			$old_model->apply($old_data); // Apply for a second time for when casting requires other fields
			
			echo "<div class='update'>";
			foreach ($old_data as $key => $value) {
				if ($key === 'updated_at') { continue; }
				$old = $old_model->display_changelog($key);
				$new = $model->display_changelog($key);
				if (is_array($old)) $old = implode(', ', $old);
				if (is_array($new)) $new = implode(', ', $new);
				if ($old === null || $old === "") $old = '<em class="empty">Empty</em>';
				else $old = "<em>".h($old)."</em>";
				if ($new === null || $new === "") $new = '<em class="empty">Empty</em>';
				else $new = "<em>".h($new)."</em>";
				
				echo "<em class='field'>".h($model_class::label($key))."</em>: $old → $new<br/>";
			}
			echo "</div>";
		}
		
		
		echo "</td>
			<td class='span3'>". format_dt( $row['updated_at'] ) ."</td>
		</tr>";
	}
	
	public static function output_item_simple(&$row) {
		
		$ret = '<div class="control-group">
			<label class="control-label">'.format_dt($row['updated_at']).'</label>';
		

			$model_class = $row['submodel_class'] === null ? $row['model_class'] : $row['submodel_class'];
			$contents = "";
			if ($row['type'] === 'update') {
				$model = new $model_class( json_decode($row['state'], true) );
				$old_data = json_decode($row['previous_state'], true);
				$old_model = new $model_class( json_decode($row['state'], true) );
				$old_model->apply($old_data);
				$old_model->apply($old_data); // Apply for a second time for when casting requires other fields
				
				$contents .= '<div class="controls"><p>';
				foreach ($old_data as $key => $value) {
					if ($key === 'updated_at') { continue; }
					$old = $old_model->display_changelog($key);
					$new = $model->display_changelog($key);
					if (is_array($old)) $old = implode(', ', $old);
					if (is_array($new)) $new = implode(', ', $new);
					if ($old === null || $old === "") $old = '<em class="empty">Empty</em>';
					else $old = "<em>".h($old)."</em>";
					if ($new === null || $new === "") $new = '<em class="empty">Empty</em>';
					else $new = "<em>".h($new)."</em>";
					
					$contents .= "<em class='field'>".h($model_class::label($key))."</em>: $old → $new<br/>";
				}
				$contents .= "</p></div>";
			}
			$ret .= $contents;
		
		$ret .= "</div>";
		return $ret;
	}
	
	public static function output_item(&$row) {
		
		$ret = "<div class='activity-item' data-id='{$row['id']}'>
			<div class='time'>".format_dt($row['updated_at'])."</div>";
		
		if ($row['cached']) {
			$ret .= $row['cached'];
		}
		else {
			$model_class = $row['submodel_class'] === null ? $row['model_class'] : $row['submodel_class'];
			
			if ($row['type'] === 'delete') {
				$name = "<b>".h($row['model_name'])."</b>";
			}
			else {
				$name = "<a href='".admin_url($row['model_class'], $row['model_id'])."'>".h($row['model_name'])."</a>";
			}

			$user = $row['user_id'] ? ("<a class='user' href='". admin_url('users', $row['user_id']) ."'>". h($row['username']) ."</a>") : 'System';
			$contents = "<div class='info'>$user ".self::$type_description[$row['submodel_class'] ? 'update' : $row['type']]." ".$name."</div>";
			if ($row['submodel_class']) {
				$model = new $model_class( json_decode($row['state'], true) );
				$contents .= "<div class='submodel'>".ucfirst(self::$type_description[$row['type']]).' '.$model_class::human_name().": ".h($row['submodel_name'])."</div>";
			}
			if ($row['type'] === 'update') {
				$model = new $model_class( json_decode($row['state'], true) );
				$old_data = json_decode($row['previous_state'], true);
				$old_model = new $model_class( json_decode($row['state'], true) );
				$old_model->apply($old_data);
				$old_model->apply($old_data); // Apply for a second time for when casting requires other fields
				
				$contents .= "<div class='update'>";
				foreach ($old_data as $key => $value) {
					if ($key === 'updated_at') { continue; }
					$old = $old_model->display_changelog($key);
					$new = $model->display_changelog($key);
					if (is_array($old)) $old = implode(', ', $old);
					if (is_array($new)) $new = implode(', ', $new);
					if ($old === null || $old === "") $old = '<em class="empty">Empty</em>';
					else $old = "<em>".h($old)."</em>";
					if ($new === null || $new === "") $new = '<em class="empty">Empty</em>';
					else $new = "<em>".h($new)."</em>";
					
					$contents .= "<em class='field'>".h($model_class::label($key))."</em>: $old → $new<br/>";
				}
				$contents .= "</div>";
			}
			else if ($row['type'] === 'comment') {
				$contents .= "<div class='comment'>".h($row['previous_state'])."</div>";
			}
			$ret .= $contents;
			mysql_update_row('changelog', $row['id'], array('cached' => $contents, 'cached_at' => gmdate('Y-m-d H:i:s')));
		}
		$ret .= "</div>";
		return $ret;
	}
	
	public static function insert($model, $type, $previous_state = null) {
		$model_class = get_class($model);
		$model_id = $model['id'];
		$model_name = $model->name();
		$submodel_id = null;
		$submodel_class = null;
		$submodel_name = null;
		if ($model instanceof Model) {
			if ($model::$parent_model) {
				$parent_model = (array) $model::$parent_model;
				foreach($parent_model as $parent_key) {
					if ($model[$parent_key] === null) { continue; }
					$submodel_class = $model_class;
					$submodel_id = $model_id;
					$submodel_name = $model_name;
					$model_class = $model::properties($parent_key, 'foreign');
					$model_id = $model[ $parent_key ];
					$model_name = $model_class::display_name( $model_id );
					break;
				}
			}
		}
		$data = array_diff_key($model->data, array_flip($model::excluded_keys()));
		
		return mysql_insert_into('changelog', array(
			'type' => $type,
			'model_class' => $model_class,
			'model_id' => $model_id,
			'model_name' => $model_name,
			'submodel_class' => $submodel_class,
			'submodel_id' => $submodel_id,
			'submodel_name' => $submodel_name,
			'user_id' => self::$record_user ? user_id() : null,
			'state' => json_encode($data),
			'previous_state' => $previous_state,
			'updated_at' => gmdate('Y-m-d H:i:s')
		));
	}

	public static function create($model) {
		if (user_type() > User::STAFF)
		{
		return self::insert($model, 'create');
		}
	}

	public static function update($model, $old_data) {
		$has_change = false;
		foreach($old_data as $key => $value) {
			if ($key !== 'updated_at') $has_change = true;
		}
		if (!$has_change) { return; }
		if (user_type() > User::STAFF)
		{
		return self::insert($model, 'update', json_encode((array) $old_data));
		}
	}

	public static function delete($model) {
		if (user_type() > User::STAFF)
		{
		return self::insert($model, 'delete');
		}
	}

	public static function comment($model, $comment) {
		if (user_type() > User::STAFF)
		{
		return self::insert($model, 'comment', $comment);
		}
	}

}