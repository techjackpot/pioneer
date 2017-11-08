<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

class FrameOption extends Model {
	
	public static $table = "frameoptions";
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

class FrameOptionValue extends Model {
	
	public static $table = "frameoptionvalues";
	public static $properties = [
		'name' => [ 'label' => 'Name' ],
		'frameoption_id' => [ 'type' => 'select', 'foreign' => 'FrameOption', 'load' => true],
		'frameproduct_ids' => [ 'type' => 'select', 'multiple' => true, 'foreign' => 'FrameProduct', 'external' => 'frameoptionvalues_frameproducts', 'label' => 'Applicable Products' ],
		'addcomponents' => [ 'label' => 'Additional Components' ],
		'updated_at' => [ 'label' => 'Modified' ],
	];
	public static $search_fields = ['name', 'abbreviation'];
	
	public function abbreviation() {
		return $this['abbreviation'];
	}
	
	public function addcomponents() {
		return $this['addcomponents'];
	}
	
	public function before_save() {
		
	}
	
	public function after_save() {
		
	}
	
}