<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

class File extends Model {
	public static $table = "files";
	public static $properties = [
		'file' => ['label' => 'File','type' => 'file', 'required' => true],
		'name' => ['required' => true],
		'content' => ['type' => 'textarea'],
		'sort_id' => ['cast' => 'int'],
		'updated_at' => ['label' => 'Modified'],
	];
	public static $search_fields = ['name', 'content'];

	public function name() {
		return $this['name'];
	}
	
}
