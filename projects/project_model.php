<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

class Project extends Model {
	
	public static $table = "projects";
	public static $properties = [
		'name' => [ 'label' => 'Name' ],
		'image' => [ 'type' => 'file'],
		'updated_at' => [ 'label' => 'Modified' ],
	];
	public static $search_fields = ['name'];
	
	public function project_preview() {
		return '<div class="projectitem">
			<div class="projinfo">
			<h3>'.h($this->display("name")).'</h3>
			<div class="projlocation">'.h($this->display("location")).'</div>
			</div>
			<div class="projimg" style="background-image: url(\''.$this->display("image").'\')"></div>
		</div>';
	}
	
	public function before_save() {

	}
	
	public function after_save() {

	}
	
}