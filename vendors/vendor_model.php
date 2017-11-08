<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

class Vendor extends Model {
	
	public static $table = "vendors";
	public static $properties = [
		'name' => [ 'label' => 'Name' ],
		'type' => [ 'type' => 'select', 'required' => true, 'storestring' => true, 'values' => ['dist', 'sales']  ],
		'updated_at' => [ 'label' => 'Modified' ],
	];
	public static $search_fields = ['name', 'rep_name', 'city', 'state', 'zip', 'county', 'country'];
	
	public function project_preview() {
		$return = '<div class="vendorinfo distributor">
			'.($this['type'] == 'sales' ? '<h5>SALESPERSON</h5>' : '<h5>DISTRIBUTOR</h5>').'
			'.(!empty($this['name']) ? '<h6>'.h($this->display("name")).'</h6>' : '').'
			'.(!empty($this['rep_name']) ? '<div class="repname">'.h($this->display("rep_name")).'</div>' : '').'
			<div class="address">'.$this->output_address().'</div>
			<div class="phone">'.h($this->display("phone")).'</div>
			<div class="email">'.h($this->display("external_email")).'</div>
			<div class="distance"></div>
		</div>';
		return $return;
	}
	
	public function output_address($twolines = true) {
		return (!empty($this['address1']) ? $this->display('address1') : '').(!empty($this['address2']) && !empty($this['address1']) ? ' '.$this->display('address2') : '').($twolines && !empty($this['address1']) ? '<br>' : '').(!empty($this['city']) ? $this->display('city') : '').(!empty($this['state']) ? (!empty($this['city']) ? ', ' : '').$this->display('state') : '').(!empty($this['zip']) ? (!empty($this['state']) ? ' ' : '').substr($this->display('zip'), 0, strpos($this->display('zip'), "-")) : '');
	}
	
	
	public function before_save() {

	}
	
	public function after_save() {

	}
	
}