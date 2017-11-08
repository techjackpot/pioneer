<?php
if (!defined('INDEX')) { require __DIR__.'/../index.php'; exit; }

trait Validation {
	
	public function validate_required() {
		foreach (func_get_args() as $index=>$key) {
			if (blank($this->data[$key])) $this->errors[$key][] = 'is required';
		}
	}
	
	public function validate_email() {
		foreach (func_get_args() as $index=>$key) {
			if (!blank($this->data[$key]) && !preg_match('/^[^@]+@[^@]+(\.[^@]+)+$/', $this->data[$key])) $this->errors[$key][] = 'is an invalid email';
		}
	}
	
	public function validate_uniqueness($table, $id) {
		if (is_null($id)) { $id = $this->data['id']; }
		foreach (func_get_args() as $index=>$key) {
			if ($index < 2) continue;
			if (!blank($this->data[$key]) && sqlr("SELECT 1 FROM `$table` WHERE `$key` = %s".($id?" AND `id` <> %s":''), $this->data[$key], $id)) {
				$this->errors[$key][] = 'is already in use';
			}
		}
	}
	
	public function validate_confirmation() {
		foreach (func_get_args() as $index=>$key) {
			if (!blank($this->data[$key]) && $this->data[$key] !== $this->data["{$key}_confirmation"]) $this->errors["{$key}_confirmation"][] = 'does not match';
		}
	}
	
	public function validate_length($min, $max) {
		if ($min !== null && $max !== null) $msg = "must be between $min and $max characters long";
		else if ($min !== null) $msg = "must be at least $min characters long";
		else if ($max !== null) $msg = "must be at most $max characters long";
		
		foreach (func_get_args() as $index=>$key) {
			if ($index <= 1) continue;
			if (!blank($this->data[$key])) {
				if ($min !== null && strlen($this->data[$key]) < $min) { $this->errors[$key][] = $msg; continue; }
				if ($max !== null && strlen($this->data[$key]) > $max) { $this->errors[$key][] = $msg; continue; }
			}
		}
	}
	
	public function validate_inclusion($array) {
		foreach (func_get_args() as $index=>$key) {
			if ($index < 1) { continue; }
			if (!blank($this->data[$key]) && !in_array($this->data[$key], $array, true)) $this->errors[$key][] = 'is an invalid value';
		}
	}
	
	public function validate_format($regex) {
		foreach (func_get_args() as $index=>$key) {
			if ($index < 1) { continue; }
			if (!blank($this->data[$key]) && !preg_match($regex, $this->data[$key])) $this->errors[$key][] = 'is an invalid format';
		}
	}
	
	public function validate_date() {
		foreach (func_get_args() as $index=>$key) {
			if (!blank($this->data[$key]) && !strtotime($this->data[$key])) $this->errors[$key][] = 'is an invalid date';
		}
	}
	
	public function validate_required_file() {
		foreach (func_get_args() as $index=>$key) {
			if (!array_key_exists($key, $_FILES) || $_FILES[$key]['error'] === UPLOAD_ERR_NO_FILE) { $this->errors[$key][] = 'is required'; }
		}
	}
	
	public function validate_valid_image() {
		foreach (func_get_args() as $index=>$key) {
			if (array_key_exists($key, $_FILES) && $_FILES[$key]['error'] === UPLOAD_ERR_OK) { // checking if upload was success
				if (!preg_match('/\.(jpe?g|png|gif|bmp)$/i', basename(unslash($_FILES[$key]['name'])))) { $this->errors[$key][] = 'is not an image'; }
			}
		}
	}
	
	public function validate_file_pdf() {
		foreach (func_get_args() as $index=>$key) {
			$type = "application/pdf";
			if (array_key_exists($key, $_FILES) && $_FILES[$key]['error'] === UPLOAD_ERR_OK) { // checking if upload was success
				$mimetype = @mime_content_type(unslash($_FILES[$key]['tmp_name']));
				if ($mimetype != $type) { $this->errors[$key][] = 'is not the correct file format: '.$type; }
			}
		}
	}
	
	
		
	public function validate_valid_file() {
		foreach (func_get_args() as $index=>$key) {
			if (array_key_exists($key, $_FILES) && $_FILES[$key]['error'] !== UPLOAD_ERR_NO_FILE && $_FILES[$key]['size'] > 0) { // checking if upload was attempted
				switch($_FILES[$key]['error']) {
					case UPLOAD_ERR_OK: break;
					case UPLOAD_ERR_INI_SIZE: $this->errors[$key][] = 'is too large'; break;
					case UPLOAD_ERR_FORM_SIZE: $this->errors[$key][] = 'is too large'; break;
					default: $this->errors[$key][] = 'couldn\'t be uploaded'; break;
				}
			}
		}
	}
	
	public function validate_image_dimensions($images, $width, $height) {
		foreach ($images as $index=>$key) {
			if (array_key_exists($key, $_FILES) && $_FILES[$key]['error'] !== UPLOAD_ERR_NO_FILE && $_FILES[$key]['size'] > 0) { // checking if upload was attempted
				list($widthcheck, $heightcheck, $type, $attr) = getimagesize($_FILES[$key]['tmp_name']);
				if ($width != $widthcheck)
				{
					$this->errors[$key][] = 'needs to be '.$width.' pixels wide, but it is '.$widthcheck.'px';
				}
				
				if ($height != $heightcheck)
				{
					$this->errors[$key][] = 'needs to be '.$height.' pixels tall, but it is '.$heightcheck.'px';
				}
			}
		}
	}
	
}