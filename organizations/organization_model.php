<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

class Organization extends Model {
	const ACTIVE = 1;
	const INACTIVE = 2;
	
	public static $table = "organizations";
	public static $properties = array(
		'name' => array('label' => 'Company Name', 'required' => true),
		'customer_number' => array('label' => 'Customer #', 'required' => true),
		'rep_ids' => [ 'label' => 'Sales Reps', 'type' => 'select', 'multiple' => true, 'foreign' => 'User', 'external' => 'organizations_reps' ],
		//'parent_id' => array('type' => 'select', 'label' => 'Parent Company', 'foreign' => 'Organization', 'scope' => 'id <> %d'),
		'updated_at' => array('label' => 'Modified', 'display' => true),
	);
	public static $search_fields = array('name', 'parent_id', 'phone', 'website');

	public function name() {
		return $this['name'];
	}
	
	public function heirarchy_off($value = 1)
    {
        $this->heirarchy_trigger = $value;
    }
		
	public function rep_ids_values() {
			return User::find_names('`users`.`type_id` > 1');
	}
	
	public function parent_id_values() {
			return self::find_names($this['id'] ? "`left` < '{$this['left']}' OR `right` > '{$this['right']}'" : '');
	}

	public function website_url() {
		if (blank($this['website'])) return;
		
		if (preg_match('/https?:\/\//i', $this['website'])) { return $this['website']; }
		else { return "http://{$this['website']}"; }
	}
	
	public function get_heirarchy() {
			if ($this['parent_id'] === null) { $left = $this['left']; $right = $this['right']; }
			else { list($left, $right) = sqll("SELECT `left`, `right` FROM `organizations` WHERE `left` < %s AND `parent_id` IS NULL ORDER BY `left` DESC LIMIT 1", $this['left']); }
			if ($left + 1 == $right) return null;
			else return sql("SELECT * FROM `organizations` WHERE `left` >= %s AND `right` <= %s ORDER BY `left`", $left, $right);
	}
	
	public function after_save() {
		if ($this->heirarchy_trigger == 1)
		{
			//self::update_heirarchy();
		}
	}
	public function after_delete() {
		sql("UPDATE organizations SET parent_id = %s WHERE parent_id = %s", $this['parent_id'], $this['id']);
		//self::update_heirarchy();
	}
	
	public function notification_emails($ids) {
		foreach ($ids as $id=>$value)
		{
			$u_emails[] = sqlr("SELECT email FROM users WHERE id = %s", $this['id']);
		}
		return $u_emails;
	}
	
	public function after_update() {
		
	}
	
	public function after_comment() {
		
	}
	
	public static function update_heirarchy($parent_id = null, $start = 0) {
		$query = $parent_id === null ? sql("SELECT id FROM organizations WHERE parent_id IS NULL ORDER BY name, id") : sql("SELECT id FROM organizations WHERE parent_id = %s ORDER BY name, id", $parent_id);
		$end = $start;
		while(list($id) = $query->fetch_row()) {
			$left = $end + 1;
			$children = self::update_heirarchy($id, $left);
			$end = $right = $left + $children + 1;
			mysql_update_row('organizations', $id, array('left' => $left, 'right' => $right));
		}
		return $end - $start;
	}
	
}
