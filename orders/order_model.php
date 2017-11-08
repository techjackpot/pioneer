<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

class Order extends Model {
	
	public static $table = "orders";
	public static $properties = [
		'status' => [ 'type' => 'select', 'required' => true, 'storestring' => true, 'values' => ['With Customer', 'Pending', 'Processed']  ],
		'billto_state' => [ 'label' => 'State / Province' ],
		'shipto_state' => [ 'label' => 'State / Province' ],
		'shipping_code' => [ 'label' => 'Shipping Method', 'type' => 'select', 'required' => true, 'storestring' => true, 'values' => ['', 'Customer Pickup', 'Hold for Truck', 'Crate & LTL (Additional charges will apply)', 'Packaged & UPS (If available)']  ],
		'customer_po' => [ 'label' => 'Customer PO#', 'required' => true ],
		'dateprocessed' => [ 'label' => 'Date Processed' ],
		'updated_at' => [ 'label' => 'Modified' ],
	];
	public static $search_fields = ['customer_po', 'customer_code', 'shipping_code', 'job_number', 'billto_name', 'shipto_name', 'first_name', 'last_name'];
	
	public static function join_fields() {
		return array_merge(parent::join_fields(), [
		   'users.first_name',
		   'users.last_name',
		   'users.organization_id',
		   'frameproducts.name product_name',
		   'frameproducts.turnaround_days',
		   'frameproducts.quantity_limit',
		]);
	}
	
	public static function join_tables() {
		return array_merge(parent::join_tables(), [
			"LEFT JOIN users ON users.id = orders.user_id",
			"LEFT JOIN frameproducts ON frameproducts.id = orders.frameproduct_id",
		]);
	}
	
	public function &country_values() {
		return get_countries();
	}
	
	public function cart_quantity() {
		$qtyarr = sqlla("SELECT `components` FROM `orders_lines` WHERE `order_id` = %s", $this['id']);
		$qtycart = array_sum($qtyarr);
		return $qtycart;
	}
	
	public function billto() {
		$address = (!empty($this['shipto_name']) ? h($this->display('billto_name')).'<br>' : '').
		(!empty($this['shipto_address1']) ? h($this->display('billto_address1')).'<br>' : '').
		(!empty($this['shipto_address2']) ? h($this->display('billto_address2')).'<br>' : '').
		(!empty($this['billto_city']) ? h($this->display('billto_city')).', ' : '').(!empty($this['billto_state']) ? h($this->display('billto_state')).' ' : '').(!empty($this['billto_zip']) ? h($this->display('billto_zip')) : '');
		return $address;
	}
	
	public function shipto() {
		$address = (!empty($this['shipto_name']) ? h($this->display('shipto_name')).'<br>' : '').
		(!empty($this['shipto_address1']) ? h($this->display('shipto_address1')).'<br>' : '').
		(!empty($this['shipto_address2']) ? h($this->display('shipto_address2')).'<br>' : '').
		(!empty($this['shipto_city']) ? h($this->display('shipto_city')).', ' : '').(!empty($this['shipto_state']) ? h($this->display('shipto_state')).' ' : '').(!empty($this['shipto_zip']) ? h($this->display('shipto_zip')) : '');
		return $address;
	}
	
	public function emailconfirmation() {
		$customer = User::get($this['user_id']);
		
		if ($customer['organization_id'])
		{
			$repemails = [];
			$org = Organization::get($customer['organization_id']);
			
			foreach ($org['rep_ids'] as $repid)
			{
				$rep = User::get($repid);
				if ($rep)
				$repemails[] = $rep['email'];
			}
		}
		
		if ($customer)
		{
			$mail = new Mail();
			$mail->to = $customer;
			
			$mail->bcc = array_merge (array(ADMIN_EMAIL, SUPERVISOR_EMAIL), $repemails);

			$mail->subject = 'Pioneer Quick Ship Order Confirmation';
			$content = '<a href="'.SITE_URL.'"><img src="'.SITE_URL.'/images/pioneer-email-header.png" border="0" /></a><br><br>
			Thank you for your order, we will review your order shortly and email you once it is finalized. Please see links below to review your order.<br /><br />';
			
			$content .= 'View your order confirmation: <a href="'.SITE_URL.'/orderconfirmation/'.$this['uniqueid'].'">'.SITE_URL.'/orderconfirmation/'.$this['uniqueid'].'</a><br /><br />';
			
			$content .= 'Printable Pioneer Order Form: <a href="'.SITE_URL.'/orderform/'.$this['uniqueid'].'">'.SITE_URL.'/orderform/'.$this['uniqueid'].'</a><br /><br />';
				
			$content .= '<br />*********************<br />
	Pioneer Industries<br />
	Tel:  (201) 933-1900<br />
	Fax: (201) 933-9580<br />
	<a href="http://www.pioneerindustries.com/">www.pioneerindustries.com<a/><br /><br />';
			
			$mail->content = $content;
			$mail->send();
		}
	}
	
	public function display_dateprocessed() {
		return format_dt(strtotime($this['dateprocessed']),'m/d/Y, g:ia');
	}	
	
	public function before_save() {

	}
	
	public function after_save() {

	}
	
}

class OrderLine extends Model {
	
	public static $table = "orders_lines";
	public static $properties = [
		'quantity' => [ 'type' => 'select', 'required' => true, 'default' => 1 ],
		'series' => [ 'label' => "Series", 'type' => 'select', 'required' => true, 'allow_blank' => true, 'load' => true ],
		'backbend' => [ 'label' => "Backbend", 'type' => 'select', 'required' => true, 'allow_blank' => true ],
		
		'gage' => [ 'label' => "Gage", 'type' => 'select', 'required' => true, 'allow_blank' => true ],
		'matl' => [ 'label' => "Material", 'type' => 'select', 'required' => true, 'allow_blank' => true ],
		'thk' => [ 'label' => "Door Thickness", 'type' => 'select', 'required' => true, 'allow_blank' => true ],
		'rabbet' => [ 'label' => "Rabbet Type", 'type' => 'select', 'required' => true, 'allow_blank' => true ],
		'type' => [ 'label' => "Type", 'type' => 'select', 'required' => true, 'allow_blank' => true ],
		'gb' => [ 'label' => "Shipped Loose Glazing Bead with screw holes (10ft Lengths)", 'type' => 'checkbox', 'default' => false, 'display' => 'bool', 'cast' => 'bool', 'process' => 'bool' ],
		'cj' => [ 'label' => "Communicating Hardware Locations", 'type' => 'checkbox', 'default' => false, 'display' => 'bool', 'cast' => 'bool', 'process' => 'bool' ],
		'dtch' => [ 'label' => "Dutch Frame Hardware Locations", 'type' => 'checkbox', 'default' => false, 'display' => 'bool', 'cast' => 'bool', 'process' => 'bool' ],
		'openings' => [ 'label' => "# of Openings", 'type' => 'select', 'required' => true, 'allow_blank' => true ],
		
		'depth' => [ 'label' => "Jamb Depth", 'type' => 'select', 'required' => true, 'allow_blank' => true ],
		'specialdepth' => [ 'label' => "Special Depth", 'required' => true ],
		'width' => [ 'label' => "Width", 'type' => 'select', 'required' => true, 'allow_blank' => true ],
		'specialwidth' => [ 'label' => "Special Width", 'required' => true ],
		'height' => [ 'label' => "Height", 'type' => 'select', 'required' => true, 'allow_blank' => true ],
		'specialheight' => [ 'label' => "Special Height", 'required' => true ],
		'strike' => [ 'label' => "Strike", 'type' => 'select', 'required' => true, 'allow_blank' => true ],
		'estkdrawing' => ['label' => "Upload ESTK PDF Drawing", 'type' => 'file', 'required' => true],
		
		'loc' => [ 'label' => "Location", 'type' => 'select', 'required' => true, 'allow_blank' => true ],
		'second' => [ 'label' => "Secondary", 'type' => 'select', 'required' => true, 'allow_blank' => true ],
		'hand' => [ 'label' => "Hand", 'type' => 'select', 'required' => true, 'allow_blank' => true ],
		'profile' => [ 'label' => "Face Options", 'type' => 'select', 'required' => true, 'allow_blank' => true ],
		'label' => [ 'label' => "Label", 'type' => 'select', 'required' => true, 'allow_blank' => true ],
		'assy' => [ 'label' => "Assembly", 'type' => 'select', 'required' => true, 'allow_blank' => true ],
		'anc' => [ 'label' => "Anchor", 'type' => 'select', 'required' => true, 'allow_blank' => true ],
		'hinge' => [ 'label' => "Hinge", 'type' => 'select', 'required' => true, 'allow_blank' => true ],
		'hingeqty' => [ 'label' => "Quantity per Jamb", 'type' => 'select', 'required' => true, 'allow_blank' => true ],
		'hingeloc' => [ 'label' => "Location", 'type' => 'select', 'required' => true, 'allow_blank' => true ],
		'closer' => [ 'label' => "Closer", 'type' => 'select', 'required' => true, 'allow_blank' => true ],
		'bolt' => [ 'label' => "Bolt", 'type' => 'select', 'required' => true, 'allow_blank' => true ],
		'frameproduct_id' => ['foreign' => 'Order', 'load' => false],
		'item' => ['virtual' => true],
		'drawing' => ['label' => "Upload Elevation PDF Drawing", 'type' => 'file', 'required' => true],
		'dps' => ['label' => "Door Position Switch Drawing", 'type' => 'file'],
		'ptp' => ['label' => "Power Transfer Drawing", 'type' => 'file'],
		'add' => ['label' => "Tag(s)"],
		//'ancf' => ['virtual' => true, 'type' => 'select'],
		//'ancdw' => ['virtual' => true, 'type' => 'select'],
		'updated_at' => [ 'label' => 'Modified' ],
	];
	//public static $search_fields = ['name'];
	
	public static $theoptions = [ 'gage' => 1,
								 'matl' => 2,
								 'thk' => 3,
								 'rabbet' => 4,
								 'type' => 5,
								 'depth' => 6,
								 'width' => 7,
								 'height' => 8,
								 'strike' => 9,
								 'loc' => 10,
								 'second' => 11,
								 'hand' => 12,
								 'profile' => 13,
								 'label' => 14,
								 'assy' => 15,
								 'anc' => 16,
								 'hinge' => 17,
								 'closer' => 18,
								 'bolt' => 19,
								 'series' => 20,
								 'backbend' => 21,
								 'openings' => 22
								];
	
	public static function join_fields() {
		return array_merge(parent::join_fields(), [
		   'orders.frameproduct_id',
		]);
	}
	
	public static function join_tables() {
		return array_merge(parent::join_tables(), [
			"LEFT JOIN orders ON orders.id = orders_lines.order_id",
		]);
	}
	
	public function quantityvalues($edit) {
		$order = Order::get($_SESSION['OID']);
		
		$qtylimit = $order['quantity_limit'] - $order->cart_quantity();
		
		if ($edit)
		$qtylimit = $qtylimit + $this['quantity'];

		$ret = array();
		foreach (range(($qtylimit <= 0 ? 0 : 1), to_int($qtylimit)) as $n)
		{
			$ret[$n] = $n;
		}
		return $ret;
	}
	
	public function totalcomponents() {
		$rettotal = 1;

		foreach (self::$theoptions as $i=>$v)
		{
			if ($this[$i])
			{
			$rettotal += FrameOptionValue::get($this[$i])->addcomponents();
			}
		}
		
		$rettotal = $rettotal * $this['quantity'];
		
		return $rettotal;
	}
	
	public function hingeqty_values() {
		$ret = array();
		foreach (range(0, 4) as $n)
		{
			$ret[$n] = $n;
		}
		return $ret;
	}
	
	/*
	public function ancf_values() {
		var_dump(self::getthevalues('16'));
	}
	
	public function ancdw_values() {
		var_dump(self::getthevalues('16'));
	}
	*/
	
	public function series_values() {
		return self::getthevalues('20');
	}
	
	public function series_display() {
		if ($this['series'])
		return FrameOptionValue::get($this['series'])->abbreviation();
	}
	
	public function gage_values() {
		return self::getthevalues('1');
	}
	
	public function gage_display() {
		if ($this['gage'])
		return FrameOptionValue::get($this['gage'])->abbreviation();
	}
	
	public function matl_values() {
		return self::getthevalues('2');
	}
	
	public function matl_display() {
		if ($this['matl'])
		return FrameOptionValue::get($this['matl'])->abbreviation();
	}
	
	public function thk_values() {
		return self::getthevalues('3');
	}
	
	public function thk_display() {
		if ($this['thk'])
		return FrameOptionValue::get($this['thk'])->abbreviation();
	}
	
	public function rabbet_values() {
		return self::getthevalues('4');
	}
	
	public function rabbet_display() {
		if ($this['rabbet'])
		return FrameOptionValue::get($this['rabbet'])->abbreviation();
	}
	
	public function type_values() {
		return self::getthevalues('5');
	}
	
	public function type_display() {
		if ($this['type'])
		return FrameOptionValue::get($this['type'])->abbreviation();
	}
	
	public function openings_values() {
		return self::getthevalues('22');
	}
	
	public function openings_display() {
		if ($this['openings'])
		return FrameOptionValue::get($this['openings'])->abbreviation();
	}
	
	public function backbend_values() {
		return self::getthevalues('21');
	}
	
	public function backbend_display() {
		if ($this['backbend'])
		return FrameOptionValue::get($this['backbend'])->abbreviation();
	}
	
	public function depth_values() {
		return self::getthevalues('6');
	}
	
	public function depth_display() {
		if ($this['depth'])
		return FrameOptionValue::get($this['depth'])->abbreviation();
	}
	
	public function width_values() {
		return self::getthevalues('7');
	}
	
	public function width_display() {
		if ($this['width'])
		return FrameOptionValue::get($this['width'])->abbreviation();
	}
	
	public function height_values() {
		return self::getthevalues('8');
	}
	
	public function height_display() {
		if ($this['height'])
		return FrameOptionValue::get($this['height'])->abbreviation();
	}
	
	public function strike_values() {
		return self::getthevalues('9');
	}
	
	public function strike_display() {
		if ($this['strike'])
		return FrameOptionValue::get($this['strike'])->abbreviation();
	}
	
	public function loc_values() {
		return self::getthevalues('10');
	}
	
	public function loc_display() {
		if ($this['loc'])
		return FrameOptionValue::get($this['loc'])->abbreviation();
	}
	
	public function second_values() {
		return self::getthevalues('11');
	}
	
	public function second_display() {
		if ($this['second'])
		return FrameOptionValue::get($this['second'])->abbreviation();
	}
	
	public function hand_values() {
		return self::getthevalues('12');
	}
	
	public function hand_display() {
		if ($this['hand'])
		return FrameOptionValue::get($this['hand'])->abbreviation();
	}
	
	public function profile_values() {
		return self::getthevalues('13');
	}
	
	public function profile_display() {
		if ($this['profile'])
		return FrameOptionValue::get($this['profile'])->abbreviation();
	}
	
	public function label_values() {
		return self::getthevalues('14');
	}
	
	public function label_display() {
		if ($this['label'])
		return FrameOptionValue::get($this['label'])->abbreviation();
	}
	
	public function assy_values() {
		return self::getthevalues('15');
	}
	
	public function assy_display() {
		if ($this['assy'])
		return FrameOptionValue::get($this['assy'])->abbreviation();
	}
	
	public function anc_values() {
		return self::getthevalues('16');
	}
	
	public function anc_display() {
		if ($this['anc'])
		return FrameOptionValue::get($this['anc'])->abbreviation();
	}
	
	public function hinge_values() {
		return self::getthevalues('17');
	}
	
	public function hinge_display() {
		if ($this['hinge'])
		return FrameOptionValue::get($this['hinge'])->abbreviation();
	}
	
	public function hingeloc_values() {
		return self::getthevalues('10');
	}
	
	public function hingeloc_display() {
		if ($this['hingeloc'])
		return FrameOptionValue::get($this['hingeloc'])->abbreviation();
	}
	
	public function closer_values() {
		return self::getthevalues('18');
	}
	
	public function closer_display() {
		if ($this['closer'])
		return FrameOptionValue::get($this['closer'])->abbreviation();
	}
	
	public function bolt_values() {
		return self::getthevalues('19');
	}
	
	public function bolt_display() {
		if ($this['bolt'])
		return FrameOptionValue::get($this['bolt'])->abbreviation();
	}
	
	public function item_display() {
		return $this->display('series')."&nbsp;&nbsp;&nbsp;".$this->display('gage')." &nbsp;".$this->display('matl')." &nbsp;".$this->display('thk')." &nbsp;".$this->display('rabbet')." &nbsp;".$this->display('type')." &nbsp;".$this->display('depth').(!empty($this['specialdepth']) ? "=".$this->display('specialdepth') : '')." &nbsp;".$this->display('width').(!empty($this['specialwidth']) ? "=".$this->display('specialwidth') : '')."x".$this->display('height').(!empty($this['specialheight']) ? "=".$this->display('specialheight') : '')." &nbsp;".$this->display('strike').($this['loc']? "/" : "").$this->display('loc').($this['second']? "/" : "").$this->display('second')." &nbsp;".$this->display('hand')." &nbsp;".$this->display('profile')."<br>".
		"&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"." &nbsp;".$this->display('label').($this['assy']? "/".$this->display('assy') : "").($this['anc']? "/".$this->display('anc') : "")." &nbsp;".$this->display('hinge').($this['hingeqty'] != null ? "/".$this->display('hingeqty') : "").($this['hingeloc']? "/".$this->display('hingeloc') : "")." &nbsp;".$this->display('closer')." &nbsp;".$this->display('bolt')." &nbsp;".(!empty($this['backbend']) && ($this['backbend'] != '187') ? " +".$this->display('backbend') : '').($this['cj'] ? " +CJ" : '').($this['dtch'] ? " +DTCH" : '').($this['gb'] ? " +GB" : '').(!empty($this['openings']) ? " +".$this->display('openings')." openings" : '').(!empty($this['add']) ? "<br>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;TAG / Mark #'s: ".$this->display('add') : '')."<br>".(!empty($this['estkdrawing']) ? '<a href="'.$this->display('estkdrawing').'" target="_blank" class="btn btn-mini btn-info"><i class="icon-white icon-file"></i> ESTK Drawing</a> ' : '').(!empty($this['drawing']) ? '<a href="'.$this->display('drawing').'" target="_blank" class="btn btn-mini btn-info"><i class="icon-white icon-file"></i> Elevation Drawing</a> ' : '').(!empty($this['dps']) ? '<a href="'.$this->display('dps').'" target="_blank" class="btn btn-mini btn-info"><i class="icon-white icon-file"></i> DPS Drawing</a> ' : '').(!empty($this['ptp']) ? '<a href="'.$this->display('ptp').'" target="_blank" class="btn btn-mini btn-info"><i class="icon-white icon-file"></i> PTP Drawing</a> ' : '');
	}
	
	private function getthevalues($fid) {
		return FrameOptionValue::find_names('EXISTS (select 1 from frameoptionvalues_frameproducts where frameoptionvalues.id = frameoptionvalues_frameproducts.frameoptionvalue_id and frameoptionvalues_frameproducts.frameproduct_id = '.sanitize($this['frameproduct_id']).') AND frameoptionvalues.frameoption_id = '.sanitize($fid).'', ['sort' => "sort_order ASC "]);
	}
	
	public function before_save() {
		$this['components'] = self::totalcomponents();
	}
	
	public function after_save() {

	}
	
}