<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }
require_once __DIR__.'/Validation.php';

class FormHelper {
	use Validation;
	
	public $action = "";
	public $data = array();
	public $errors = array();
	public $in_fieldset = false;
	public $prefix = "";
	public $namespace = null;
	public $modal = false;
	public $activity_wall = false;
	
	public function __construct($action = null, $prefix = "") {
		if ($action === null) $action = $_SERVER['REQUEST_URI'];
		$this->prefix = $prefix;
		$this->context = $context;
		$this->action = $action;
		$this->modal = is_modal();
	}
	
	public function start($options = array()) {
		if ($options['multipart']) {
			$extra = ' enctype="multipart/form-data"';
		}
		if ($options['id']) {
			$id = "id=\"{$this->h($options['id'])}\"";
		}
		if ($options['class'] !== null) {
			$class = $this->h($options['class']);
		} else {
			$class = 'form-horizontal';
		}
		if ($this->modal) { $class .= ' modal-content'; }
		if ($options['modal_class']) {
			$modal_class = " ".$this->h($options['modal_class']);
		}
		if (!blank($this->prefix)) { $prefix = " data-prefix='{$this->h($this->prefix)}' "; }
		if (!blank($options['context'])) { $context= " data-context='".h($options['context'])."' "; }
		if (!blank($options['contexttwo'])) { $context2= " data-contexttwo='".h($options['contexttwo'])."' "; }
		return ($this->modal?"<div class='modal-dialog$modal_class'>":'').'<form method="post" '.$id.' action="'.$this->h($this->action).'" class="'.$class.'"'.$extra.$prefix.$context.$context2.'>';
	}
	
	public function end() {
		$close = $this->in_fieldset ? ($this->modal?'</div>':'</fieldset>') : '';
		$this->in_fieldset = false;
		return $close.'</form>'.($this->modal?"</div>":'');
	}
	
	public function fieldset($title = "", $close = null) {
		if ($close === null) $close = $this->in_fieldset;
		if ($this->activity_wall) {
			$wall = '<div class="col-main">';
		}
		$this->in_fieldset = true;
		if ($this->modal) {
			return ($close?'</div>':'').'<div class="modal-header"><button type="button" class="close" data-dismiss="modal">×</button>'.display_title($title, 'h4', 'modal-title').'</div><div class="modal-body">'.$wall;
		}
		else {
			return ($close?'</fieldset>':'').(blank($title)?'':display_title($title))."<fieldset>".$wall;
		}
	}
	
	public function toolbar($links) {
		return '<div class="toolbar">'.implode("", $links).'</div>';
	}
	
	public function value_for($name, $default = null) {
		if (false && array_key_exists($name, $_POST)) { return unslash($_POST[$name]); }
		else if (isset($this->data[$name])) { return $this->data[$name]; }
		else if (!is_null($default)) { return $default; }
		else { return ''; }
	}
	
	public function data_for($options) {
		$ret = '';
		if (array_key_exists('data', $options) && is_array($options['data'])) {
			foreach($options['data'] as $k => $v) {
				$ret .= " data-$k=\"".h($v)."\" ";
			}
		}
		return $ret;
	}
	
	public function id_from_name($name) {
		return (blank($this->namespace) ? '' : preg_replace('/\[(.*?)\]/', '_\1', "{$this->namespace}_")).preg_replace('/\[(.*?)\]/', '_\1', $name);
	}
	
	public function with_namespace($name) {
		if (blank($this->namespace)) return $name;
		else return "{$this->namespace}[".preg_replace('/^(.*?)\[(.*)\]$/', '$1][$2', $name)."]";
	}
	
	public function field($name, $options = array(), $field_only = false) {
		$options = $options + (array) $this->data->properties($name);
		$func = (blank($options['type'])?'text':$options['type']).($field_only?'_field':'');
		
		switch($options['type']) {
			case 'checkboxes':
			case 'radios':
			case 'select':
				$values = is_array($options['values']) ? $options['values'] : $this->data->values($name);
				return $this->$func($name, $values, $options);
			default:
				return $this->$func($name, $options);
		}
	}
	
	public function textarea($name, $options = array()) {
		return $this->block($name, $this->textarea_field($name, $options), $options);
	}
	
	public function textarea_field($name, $options = array()) {
		$id = array_key_exists('id', $options) ? "input_{$options['id']}" : "input_{$this->id_from_name($name)}";
		$class = array_key_exists('class', $options) ? "input_textarea {$options['class']}" : "input_textarea input-block-level";
		$style = array_key_exists('style', $options) ? " {$options['style']}" : "";
		$disabled = $options['disabled'] ? 'disabled="disabled"' : '';
		$readonly = $options['readonly'] ? 'readonly="readonly"' : '';
		$data = $this->data_for($options);
		
		$rows = intval($options['rows']) or $rows = 5;
		$columns = intval($options['columns']) or $columns = 20;
		
		$value = array_key_exists('value', $options) ? $options['value'] : $this->value_for($name, $options['default']);
		
		return "<textarea $disabled $readonly $data name=\"{$this->with_namespace($name)}\" class=\"{$class}\" style=\"{$style}\" rows=\"$rows\" columns=\"$columns\" id=\"{$this->h($this->prefix.$id)}\">{$this->h($value)}</textarea>";
	}
	
	public function text($name, $options = array()) {
		return $this->block($name, $this->text_field($name, $options), $options);
	}
	
	public function text_field($name, $options = array()) {
		$type = array_key_exists('type', $options) ? $options['type'] : 'text';
		$id = array_key_exists('id', $options) ? "input_{$options['id']}" : "input_{$this->id_from_name($name)}";
		$class = array_key_exists('class', $options) ? "input_$type {$options['class']}" : "input_$type input-block-level";
		$style = array_key_exists('style', $options) ? " {$options['style']}" : "";
		$disabled = $options['disabled'] ? 'disabled="disabled"' : '';
		$readonly = $options['readonly'] ? 'readonly="readonly"' : '';
		$placeholder = $options['placeholder'] ? " placeholder='{$this->h($options['placeholder'])}' " : '';
		$data = $this->data_for($options);

		
		if ($type != 'password') { $value = array_key_exists('value', $options) ? $options['value'] : $this->value_for($name, $options['default']); }
		
		if (array_key_exists('prepend', $options)) {
			$input_before = '<span class="input-prepend"><span class="add-on">'.$options['prepend'].'</span>';
	      $input_after = '</span>';
		}
		else if (array_key_exists('append', $options)) {
			$input_before = '<span class="input-append">';
	      $input_after = '<span class="add-on">'.$options['append'].'</span></span>';
		}
		
		return "$input_before<input type=\"{$type}\" $disabled $placeholder $data $readonly name=\"{$this->with_namespace($name)}\" class=\"{$class}\" style=\"{$style}\" id=\"{$this->h($this->prefix.$id)}\" ".($value ? "value=\"{$this->h($value)}\"" : "")." />$input_after";
	}
	
	public function hidden($name, $value = null, $options = array()) {
		$id = array_key_exists('id', $options) ? "input_{$options['id']}" : "input_{$this->id_from_name($name)}";
		$class = array_key_exists('class', $options) ? "input_hidden {$options['class']}" : "input_hidden";
		$disabled = $options['disabled'] ? 'disabled="disabled"' : '';
		$data = $this->data_for($options);
		
		if ($value === null) { $value = $this->value_for($name, $options['default']); }
		
		return "<input type=\"hidden\" $disabled $data name=\"{$this->with_namespace($name)}\" class=\"{$class}\" id=\"{$this->h($this->prefix.$id)}\" value=\"{$this->h($value)}\" />";
	}
	
	public function password($name, $options = array()) {
		$options['type'] = 'password';
		return $this->text($name, $options);
	}
	
	public function info($name, $value = null, $options = array()) {
		$id = array_key_exists('id', $options) ? "input_{$options['id']}" : "input_{$this->id_from_name($name)}";
		$class = array_key_exists('class', $options) ? "text {$options['class']}" : "text";
		if ($value === null) $value = $this->value_for($name);
		
		return $this->block($name, "<div id='{$this->h($this->prefix.$id)}' class='$class'>$value</div>", $options);
	}
	
	function bool($name, $options = array(), $data = null) {
		$id = array_key_exists('id', $options) ? "input_{$options['id']}" : "input_{$this->id_from_name($name)}";	
		$value = array_key_exists('value', $options) ? $options['value'] : $this->value_for($name, $options['default']);
			
		$element = '<label class="radio"><input type="radio" name="'.$this->with_namespace($name).'" value="1" '.($value?' checked="checked"':'').' /> Yes</label> <label class="radio"><input type="radio" name="'.$this->with_namespace($name).'" value="0" '.(!$value?' checked="checked"':'').' /> No</label>';	
		return $this->block($name, $element, $options);
	}
	
	
	public function checkbox($name, $options = array()) {
		return $this->block($name, $this->checkbox_field($name, $options), array('label' => $options['block_label']) + $options);
	}
	
	public function checkbox_field($name, $options = array()) {
		$label = array_key_exists('label', $options) ? $this->h($options['label']) : $this->label_from_name($name);
		$type = array_key_exists('type', $options) ? $options['type'] : 'checkbox';
		$class = array_key_exists('class', $options) ? "input_$type {$options['class']}" : "input_$type";
		$disabled = $options['disabled'] ? 'disabled="disabled"' : '';
		$data = $this->data_for($options);
		if (preg_match('/^([-\w]+)\[([-\w]*)\]$/', $name, $m)) {
			$name = $m[1];
			if ($type==='checkbox') $name_append = "[]";
			if (blank($m[2])) {
				if (!array_key_exists('id', $options)) {
					if (array_key_exists('value', $options)) $options['id'] = "$m[1]_{$options['value']}";
					else $options['id'] = $m[1];
				}
			}
			else {
				if (!array_key_exists('value', $options)) {
					if (key_is_id($m[1])) { $options['value'] = to_int($m[2]); }
					else { $options['value'] = $m[2]; }
				}
				if (!array_key_exists('id', $options)) $options['id'] = "$m[1]_$m[2]";
				if (!array_key_exists('empty_value', $options)) $options['empty_value'] = false;
			}
  		}
		$id = array_key_exists('id', $options) ? "input_{$options['id']}" : "input_{$this->id_from_name($name)}";
		
		$empty_value = array_key_exists('empty_value', $options) ? $options['empty_value'] : ($type==='checkbox'?'0':false);
		if ($empty_value !== false && $empty_value !== null) {
			$hidden = "<input type=\"hidden\" name=\"{$this->with_namespace($name)}\" value=\"{$this->h($empty_value)}\" />";
		}
		$value = array_key_exists('value', $options) ? $options['value'] : '1';
		
		$value_for = $this->value_for($name, $options['default']);
		
		if (is_array($value_for)) { $checked = in_array($value, $value_for, true); }
		else { $checked = strval($value) === strval($value_for); }
		if ($checked) $checked = 'checked="checked"';
		else $checked = '';
		
		if ($options['inline']) $label_class = ' inline';
		
		return "<label class=\"checkbox$label_class\">{$hidden}<input type=\"{$type}\" $disabled $data name=\"{$this->with_namespace($name)}{$name_append}\" class=\"{$class}\" id=\"{$this->h($this->prefix.$id)}\" value=\"{$this->h($value)}\" {$checked} /> $label</label>";
	}
	
	public function radio($name, $options = array()) {
		$options['type'] = 'radio';
		return $this->checkbox($name, $options);
	}
	
	public function file($name, $options = array()) {
		$id = array_key_exists('id', $options) ? "input_{$options['id']}" : "input_{$this->id_from_name($name)}";
		$class = array_key_exists('class', $options) ? "input_file {$options['class']}" : "input_file";
		$disabled = $options['disabled'] ? 'disabled="disabled"' : '';
		$accept = $options['accept'] ? 'accept="'.$options['accept'].'"' : '';
		$data = $this->data_for($options);
		
		
		if ($this->data[$name]) {
			$filename = basename($this->data[$name]);
			$delete = <<<HTML
<div class='help-block'>
	{$this->h($filename)} &nbsp; &nbsp; <label class="checkbox inline"><input type="checkbox" value="1" name="{$this->with_namespace($name)}_delete" /> Delete?</label>
</div>
HTML;
			
		} 
		
		return $this->block($name, "<input type=\"file\" $disabled $accept $data name=\"{$this->with_namespace($name)}\" class=\"{$class}\" id=\"{$this->h($this->prefix.$id)}\" />$delete", $options);
	}
	
	public function checkboxes($name, $data = null, $options = array()) {
		return $this->block($name, $this->select_field($name, $data, $options, true), $options);
	}
	public function checkboxes_field($name, $data = null, $options = array()) {
		return $this->select_field($name, $data, $options, true);
	}
	
	public function radios($name, $data = null, $options = array()) {
		return $this->block($name, $this->select_field($name, $data, $options, 2), $options);
	}
	public function radios_field($name, $data = null, $options = array()) {
		return $this->select_field($name, $data, $options, 2);
	}
	
	public function select($name, $data = null, $options = array()) {
		return $this->block($name, $this->select_field($name, $data, $options), $options);
	}
	
	public function select_field($name, $data, $options = array(), $checkboxes = false) {
		$id = array_key_exists('id', $options) ? "input_{$options['id']}" : "input_{$this->id_from_name($name)}";
		$class = array_key_exists('class', $options) ? $options['class'] : ($checkboxes ? 'checkboxes' : 'input-medium');
		$value = array_key_exists('value', $options) ? $options['value'] : $this->value_for($name, $options['default']);
		$storestring = array_key_exists('storestring', $options) ? true : false;
		$ajax = array_key_exists('ajax', $options) ? true : false;
		$disabled = $options['disabled'] ? 'disabled="disabled"' : '';
		
		$allow_blank = $options['allow_blank'] !== null ? $options['allow_blank'] : !$options['required'];
		if ($allow_blank && !$options['multiple'] && !$checkboxes) $opts = '<option'.(blank($value)?' selected="selected"':'').' value="">'.$this->h($options['blank_text']).'</option>';
		
		
		
		if ($data === null || is_array($data)) {
			if ($data === null) {
				$data = array_combine(array_keys($value), array_keys($value));
				$value = array_keys(array_filter($value));
			}
			
			if (!$checkboxes && ($ajax && $value)) {
				//var_dump($value);
				$selected = $value['id'];
				$opts .= '<option '.$option_data.' value="'.h($value['id']).'" selected="selected" >'.h($value['name']).'</option>';
			}
			
			foreach($data as $opt_id => $opt_name) {
				if (is_array($value)) { $selected = in_array($opt_id, $value, true); }
				else { $selected = $opt_id == $value; }
				//else { $selected = $opt_id === $value; }
				
				if ($checkboxes === 2) {
					$opts .= '<label class="radio'.($options['inline']?' inline':'').'"><input type="radio" name="'.$this->with_namespace($name).'" value="'.$this->h($opt_id).'"'.($selected?' checked="checked"':'').'/> '.$this->h($opt_name).'</label>';
				}
				else if ($checkboxes) {
					$opts .= '<label class="checkbox'.($options['inline']?' inline':'').'"><input type="checkbox" name="'.$this->with_namespace($name).'[]" value="'.$this->h($opt_id).'"'.($selected?' checked="checked"':'').'/> '.$this->h($opt_name).'</label>';
				}
				else if ($storestring) {
					$selected = $opt_name === $value;
					$opts .= '<option value="'.$this->h($opt_name).'"'.($selected?' selected="selected"':'').'>'.$this->h($opt_name).'</option>';
				}
				else {
					//$selected = $opt_name === $value;
					$opts .= '<option value="'.$this->h($opt_id).'"'.($selected?' selected="selected"':'').'>'.$this->h($opt_name).'</option>';
					//$opts .= '<option value="'.$this->h($opt_name).'"'.($selected?' selected="selected"':'').'>'.$this->h($opt_name).'</option>';
				}
			}
		}
		else if ($data instanceof mysqli_result) { // If SQL resource
			$key_is_id = key_is_id($name);
			$limitx = 0;
			
			while($row = $data->fetch_array()) {
				$limit++;
				$opt_id = $row[0]; $opt_name = $row[1];
						
				if ($key_is_id) $opt_id = to_int($opt_id);
				
				if (is_array($value)) { $selected = in_array($opt_id, $value, true); }

				else { $selected = $opt_id == $value; }
				//else { $selected = $opt_id === $value; }
				
				$option_data = '';
				if ($options['option_data']) {
					foreach($options['option_data'] as $key) {
						$option_data .= " data-$key=\"{$row[$key]}\"";
					}
				}
				if ($checkboxes === 2) {
					$opts .= '<label class="radio'.($options['inline']?' inline':'').'"><input '.$option_data.' type="radio" name="'.$this->with_namespace($name).'" value="'.$this->h($opt_id).'"'.($selected?' checked="checked"':'').'/> '.$this->h($opt_name).'</label>';
				}
				else if ($checkboxes) {
					$opts .= '<label class="checkbox'.($options['inline']?' inline':'').'"><input '.$option_data.' type="checkbox" name="'.$this->with_namespace($name).'[]" value="'.$this->h($opt_id).'"'.($selected?' checked="checked"':'').'/> '.$this->h($opt_name).'</label>';
				}
				else if ($ajax) {
					if ($selected)
					{
						$opts .= '<option '.$option_data.' value="'.$this->h($opt_id).'"'.($selected?' selected="selected"':'').'>'.$this->h($opt_name).'</option>';
					}
				}
				else {
					//if ($limit <= 10905 || $selected) // TEMP ADDED FOR SPEED
					$opts .= '<option '.$option_data.' value="'.$this->h($opt_id).'"'.($selected?' selected="selected"':'').'>'.$this->h($opt_name).'</option>';
				}
				
			}
		}
		
		if ($options['multiple']) { $multiple = ' multiple="multiple"'; $element_name = $this->with_namespace($name)."[]"; }
		else { $element_name = $this->with_namespace($name); }
		
		if ($checkboxes) {
			return "<div class=\"{$class}\" id=\"{$this->h($this->prefix.$id)}\">$opts</div>";
		}
		else {
			return "<select $disabled name=\"{$element_name}\" class=\"{$class}\" id=\"{$this->h($this->prefix.$id)}\"$multiple>$opts</select>";
		}
	}
	
	public function errors($name) {
		if ($this->data instanceof Model) {
			return $this->data->errors[$name];
		}
		else {
			return $this->errors[$name];
		}
	}
	
	public function block($name, $control = null, $options = array()) {
		$label = array_key_exists('label', $options) ? $this->h($options['label']) : $this->label_from_name($name);
		$id = array_key_exists('id', $options) ? $options['id'] : "control_{$this->id_from_name($name)}";
		$input_id = array_key_exists('id', $options) ? "input_{$options['id']}" : "input_{$this->id_from_name($name)}";
		$group_class = array_key_exists('group_class', $options) ? " {$options['group_class']}" : "";
		$control_class = array_key_exists('control_class', $options) ? " {$options['control_class']}" : "";
		$nolabel = array_key_exists('nolabel', $options) ? $this->h($options['nolabel']) : false;
		
		$validate = array_key_exists('validate', $options) ? $options['validate'] : array();
		if (!is_array($validate)) { $validate = array($validate); }
		if ($options['required']) {
			$label = "<span class=\"required\">*</span> $label";
			$validate[] = 'required';
		}
		if (!blank($validate)) { $validate = ' data-validate="'.implode(',', $validate).'" '; }
		else { $validate = ''; }
		
		if ($errors = $this->errors($name)) {
			$error_class = ' error';
			$error_text .= '<span class="help-block help-error">'.$this->h(implode(', ',$errors)).'</span>';
		}
		if ($options['actions']) {
			$actions = '<div class="item-actions">'.implode(' ', $options['actions']).'</div>';
		}
		if ($options['hide']) {
			$hide = 'style="display: none;"';
		}
	 
		$help_block .= !empty($options['info']) ? '<span class="help-inline">'.$options['info'] .'</span>' : '';
		$help_block .= !empty($options['description']) ? '<p class="help-block">'.$options['description'].'</p>' : '';
		
		if ($nolabel == false)
		$label_block = '<label class="control-label" for="'.$this->h($this->prefix.$input_id).'">'.$label.'</label>';
		else
		{
		$group_class .= ' nomargincontrol';
		$control_class .= ' nomargincontrol';
		}
		
		if ($control === null) {
			return <<<HTML
<div class="control-group{$group_class}{$error_class}" id="{$this->h($this->prefix.$id)}" $validate $hide> {$actions}
	{$label_block}
	<div class="controls{$control_class}">	
HTML;
		}
		else {
			return <<<HTML
<div class="control-group{$group_class}{$error_class}" id="{$this->h($this->prefix.$id)}" $validate $hide> {$actions}
	{$label_block}
	<div class="controls{$control_class}">{$control} {$error_text} {$help_block}</div> 
</div>	
HTML;
		}
	}
	
	public function endblock() {
		return '</div></div>';
	}
	
	public function submit($text = null, $name = null, $buttons = [], $customclass = null) {
		if ($text === null) $text = 'Save changes';
		if ($name === null) $name = 'save';
		if ($this->activity_wall && $this->in_fieldset) {
			ob_start();
			$model = $this->data;
			echo '</div><div class="col-sidebar"><div class="activity-wall-title"><a href="'.BASE_PATH.'admin/changelog/'.($model::$table).'/'.$this->data['id'].'" class="pull-right">View All</a>Activity Wall</div><div class="activity-wall">';
			ChangeLog::display_model($this->data);
			echo '</div></div>';
			$wall = ob_get_contents();
			ob_end_clean();
		}
		$buttons = $buttons ? ' <span class="form-extra-actions">'.implode(' ', $buttons).'</span> ' : '';
		if ($this->modal) {
			$close = $this->in_fieldset ? '</div>' : '';
			$this->in_fieldset = false;
			return $wall.$close.'<div class="modal-footer"> '.$buttons.'
            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            <button type="submit" name="'.$this->h($name).'" class="'.($customclass ? $customclass : 'btn btn-primary').'">'.$text.'</button>
          </div>';
		}
		else {
			return $wall."<div class='form-actions'><button name='{$this->h($name)}' value='submit' class='".($customclass ? $customclass : 'btn btn-primary')."' type='submit'>{$text}</button> $buttons </div>";	
		}
	}
	
	public function post_rows($first) {
		$args = is_array($first) ? $first : func_get_args();
		$ret = array();
		
		$post = blank($this->namespace) ? $_POST : $_POST[$this->namespace];
		foreach ($args as $index=>$key) {
			$ret[$key] = presence(unslash($post[$key]));
		}
		cast_ids_to_int($ret);
		return $ret;
	}
	
	public function post_rows_multi($first) {
		$args = is_array($first) ? $first : func_get_args();
		$ret = array();
		
		$post = blank($this->namespace) ? $_POST : $_POST[$this->namespace];
		if ($post) {
			foreach($post as $index => $name) {
				$index2 = is_numeric($index) ? intval($index) : $index;
				foreach ($args as  $key) {
					$ret[$index2][$key] = presence(unslash($post[$index][$key]));
				}
			}
		}
		
		return $ret;
	}
	
	/*
	public function process_files() {
		$output_dir = 'var/uploads/';
		$ret = array();
		foreach (func_get_args() as $index=>$key) {
			if (array_key_exists($key, $_FILES) && $_FILES[$key]['error'] == UPLOAD_ERR_OK) { // checking if upload was success
				$basename = basename(unslash($_FILES[$key]['name']));
				$path = $output_dir.$basename;
				for($i=1; file_exists($path); $i++) { $path = $output_dir.$i.'-'.$basename; }
				move_uploaded_file($_FILES[$key]['tmp_name'], $path);
				$this->data[$key] = $path;
			}
			else if ($_POST["{$key}_delete"]) {
				$this->data[$key] = null;
			}
		}
		return $ret;
	}*/
	
	/**
	 * @access public
	 * @method process_files
	 * @param polymorphic (string, string, ...) List of keys for the uploaded files
	 * @param polymorphic (array, array, ...) array with file information 'destination', 'required', 'key'
	 * @return array List of uploaded files in format array('file'=>path, 'type'=>mimetype, 'name'=>original name)
	 */
	public function process_files(/* plymorphic */) {
		$output_dir = 'var/uploads/';
		$ret = array();
		foreach (func_get_args() as $index=>$key) {
			if (is_array($key) && $_FILES[$key]['size'] < 0) {
				$updInfo = $key;
				$key = $updInfo['key'];
			} else $updInfo = array();
			if (array_key_exists($key, $_FILES) && $_FILES[$key]['error'] == UPLOAD_ERR_OK && $_FILES[$key]['size'] > 0) { // checking if upload was success
				if ($updInfo['destination'] instanceof TempFile) {
					$basename = preg_replace('/[^a-zA-Z0-9_.]/', '', $updInfo['destination']->basename());
					$path = (string) preg_replace('/[^a-zA-Z0-9_.]/', '', $updInfo['destination']);
				} else {
					$basename = basename(unslash($_FILES[$key]['name']));
					//$path = /*PROJ_ROOT.*/'/'.$output_dir.$basename;
					$path = PROJ_ROOT.'/'.$output_dir.preg_replace('/[^a-zA-Z0-9_.]/', '', $basename);
					for($i=1; file_exists($path); $i++) { $path = $output_dir.$i.'-'.$basename; }
				}
				
				move_uploaded_file($_FILES[$key]['tmp_name'], $path);
				$path = preg_replace("/^".preg_quote(PROJ_ROOT, '/').'\/*/i', '', $path);
				$path = (substr($path,0,1) != "/" && !empty($path) ? "/".$path : $path);
				$this->data[$key] = $path;
				$ret[$key] = array(
					'file' => $updInfo['destination'] ? "/".preg_replace("/^".preg_quote(PROJ_ROOT, '/').'\/*/i', '', $updInfo['destination']) : "/".preg_replace("/^".preg_quote(PROJ_ROOT, '/').'\/*/i', '', $path),
					'type' => $_FILES[$key]['type'],
					'name' => basename($path),
					'key' => $key
				);
			}
			else if ($_POST["{$key}_delete"]) {
				
				$this->data[$key] = null;
			}
			else if ($updInfo['required']) $this->errors[$key][] = 'is not selected or can not be uploaded';
		}

		return $ret;
	}
	
	public function apply_post() {
		$values = call_user_func_array(array($this, 'post_rows'), func_get_args());
		foreach ($values as $k=>$v) { 
			if (array_key_exists($k, $_FILES) && $_FILES[$k]['size'] <= 0) {
				
			} else {
				$this->data[$k] = $v; 
			}
		}
		return $values;
	}
	
	public static function h() {
		
		return call_user_func_array("htmlspecialchars", func_get_args());
	}
	
	public function label_from_name($name) {
		if ($this->data instanceof Model) {
			return $this->data->label($name);
		}
		else return preg_replace('/ id(s?)$/i', '\1', ucwords(str_replace('_',' ', $name)));
	}

}