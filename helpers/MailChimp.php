<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

class MailChimp {
	
	public static function call($method, $args) {
		static $endpoint;
		$args['api_key'] = settings('mailchimp_api_key');
		if (!$endpoint) {
			list(, $datacenter) = explode('-', $args['api_key']);
			$endpoint = sprintf("https://%s.api.mailchimp.com/2.0", $datacenter);
		}
		
		$url = $endpoint.'/'.$method.'.json';
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
		curl_setopt($ch, CURLOPT_USERAGENT, 'PHP-MCAPI/2.0');                
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($args));
		$result = curl_exec($ch);
		curl_close($ch);
		
		return $result ? json_decode($result, true) : false;
	}
	
	public static function lists_create( ) {
		
		
	}
	
}

trait MailChimpSync {
	
	public function mailchimp_sync() {
		if (blank($this['mailchimpid'])) {
			MailChimp::call('lists/')
		}
		
		
	}
	
	
}