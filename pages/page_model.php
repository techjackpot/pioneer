<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

class Page extends Model {
	public static $table = "pages";
	public static $properties = [
		'name' => ['required' => true],
		'menu_title' => ['required' => true],
		'show_menu' => ['label' => 'Show in Menu?', 'type' => 'checkbox', 'display' => 'bool', 'cast' => 'bool', 'process' => 'bool'],
		'content' => ['type' => 'textarea'],
		'image' => ['label' => 'Left Image','type' => 'file'],
		'parent_id' => ['type' => 'select', 'foreign' => 'Page' ],
		'sort_id' => ['cast' => 'int'],
		'meta_title' => ['label' => 'Metadata Title'],
		'meta_description' => ['label' => 'Metadata Description', 'type' => 'textarea'],
		'meta_keywords' => ['label' => 'Metadata Keywords', 'type' => 'textarea'],
		'uri' => ['label' => 'Link URI'],
		'external_link' => ['label' => 'External Link URL'],
		'updated_at' => ['label' => 'Modified'],
	];
	public static $search_fields = ['name', 'content'];

	public function name() {
		return $this['name'];
	}
	
}
