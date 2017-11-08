<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

class CSV {
	public $columns = [];
	
	public function __construct($columns = null) {
		$this->columns = is_array($columns) ? $columns : func_get_args();	
	}
	
	public function conditional_columns($columns) {
		$columns = is_array($columns) ? $columns : func_get_args();
		foreach($columns as $col) {
			if ($_GET['field'][$col]) {
				$this->columns[] = $col;
			}
		}
	}
	
	public function output($data, $name) {
		$GLOBALS['no_template'] = true;
		header('Content-Type: application/csv');
		header('Content-Disposition: attachment; filename='.$name.'_'.date("m-d-Y").'.csv');
		header('Pragma: no-cache');
		
		$out = fopen('php://output', 'w');
		
		$class = $data->class;

		$row = [];
		foreach($this->columns as $key) {
			$row[] = $class::label($key);
		}
		fputcsv($out, $row);
		
		foreach ($data as $model) {
			$row = [];
			foreach($this->columns as $key) {
				$row[] = $model->display($key);
			}
			fputcsv($out, $row);
		}
		
		fclose($out);
	}
	
}