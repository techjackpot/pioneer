<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

class FrameProduct extends Model {
	
	public static $table = "frameproducts";
	public static $properties = [
		'name' => [ 'label' => 'Name' ],
		'updated_at' => [ 'label' => 'Modified' ],
	];
	public static $search_fields = ['name'];
	
	public function before_save() {

	}
	
	public function after_save() {

	}
	
}