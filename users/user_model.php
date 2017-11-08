<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

function has_permission($key) {
	static $permissions;
	if ($permissions === null) {
		$permissions = array();
		if (is_logged()) {
			$query = sql("SELECT p.key FROM users_permissions p INNER JOIN users_groups_permissions gp ON gp.permission_id = p.id WHERE gp.group_id = (SELECT group_id FROM users WHERE id = %s)", user_id());
			while (list($v) = $query->fetch_row()) { $permissions[$v] = true; }
		}
	}
	return (user_type() >= User::STAFF && $permissions[$key]) || user_type() >= User::ADMIN;
}

function require_permission($key) {
	if (!has_permission($key)) error_403();
}

class User extends Model {
	const DISABLED = 0;
	const CUSTOMER = 1;
	const STAFF = 2;
	const ADMIN = 3;
	const SUPER_ADMIN = 4;
	
	public static $table = "users";
	public static $properties = [
		'username' => [ 'required' => true, 'validate' => 'uniqueness' ],
		'password' => [ 'display' => true, 'required' => false ],
		'customer_number' => [ 'required' => true, 'load' => true, 'virtual' => true ],
		'email' => [ 'label' => 'Email Address', 'validate' => ['email','uniqueness'], 'required' => true ],
		'group_id' => [ 'type' => 'select', 'label' => 'Permissions', 'foreign' => 'PermissionGroup' ],
		'organization_id' => [ 'required' => true, 'type' => 'select', 'foreign' => 'Organization', 'load' => true, 'allow_blank' => true],
		'type_id' => [ 'type' => 'select', 'required' => true, 'default' => 1, 'values' => ['Disabled', 'Customer', 'Staff', 'Admin', 'Super Admin']  ],
		'status_id' => [ 'type' => 'select', 'foreign' => 'UsrStatuss', 'default' => 1  ],
		'name' => [ 'sort' => array('last_name', 'first_name') ],		
		'updated_at' => [ 'label' => 'Modified', ],
	];
	public static $search_fields = ['first_name', 'last_name', 'title', 'email'];
	public static $name_field = 'concat(%1$s.first_name," ",%1$s.last_name)';
	
	public static function join_fields() {
		return array_merge(parent::join_fields(), [
		   'organizations.customer_number customer_number',
		]);
	}
	
	public static function join_tables() {
		return array_merge(parent::join_tables(), [
			"LEFT JOIN organizations ON organizations.id = users.organization_id",
		]);
	}
	
	public function customer_number_display(){
		if (!empty($this['customer_number']))
		{
			return $this['customer_number'];
		}
	}
		
	public function name_display() {
		$name = "{$this['first_name']} {$this['last_name']}";
		return $name === " " ? $this['username'] : $name;
	}
	
	public function password_display() {
		return $this['password'] === null ? null : '••••••';
	}
	
	public function notes_changelog() {
		return $this['notes'] === null ? null : truncate($this['notes']);
	}
	
	public function phone_country_code_display(){
		if (!empty($this['phone_country_code']))
		{
			return convertcountrycode($this['phone_country_code']);
		}
	}
	
	public function mobile_country_code_display(){
		if (!empty($this['mobile_country_code']))
		{
			return convertcountrycode($this['mobile_country_code']);
		}
	}
	
	public function fax_country_code_display(){
		if (!empty($this['fax_country_code']))
		{
			return convertcountrycode($this['fax_country_code']);
		}
	}
	
	public function hash_password() {
		if (!blank($this['password'])) {
			$salt = strtr(base64_encode(mcrypt_create_iv(16, MCRYPT_DEV_URANDOM)), '+', '.');
			$this['password'] = crypt($this['password'], '$2y$10$'.$salt.'$');
		}
		else {
			unset($this['password']);
		}
		unset($this['password_confirmation']);
	}
	
	public function name() {
		return $this->name_display();
	}

	public function before_save() {
		
	}
	
	public function after_save() {

	}

	public function send_password_reset() {
		$new_password = base_convert(rand(), 10, 36).base_convert(rand(), 10, 36);
		
		$this['password'] = $new_password;
		$this->hash_password();
		$this->save();
		
		$mail = new Mail();
		$mail->to = $this;
		$mail->subject = 'Password Reset';
		$mail->content = "Your password on ".SITE_TITLE." has been reset to $new_password";
		$mail->send();
	}
	
	public function &state_values() {
		return get_regions();
	}
	
	public function &country_values() {
		return get_countries();
	}
	
	public function &city_values() {
		return get_cities();
	}
	
	public function confirmation_emails($type) {
		
		switch ($type) {
			case "attendee-confirmation":
				
			break;
			case "approved-attending":
				
			break;
		}
		
	}
	
}

class PermissionGroup extends Model {
	public static $table = "users_groups";

	public static $properties = array(
		'name' => array('required' => true),
		'permission_ids' => array('external' => true),
		
		'updated_at' => array('label' => 'Modified'),
	);

	public function name() {
		return $this['name'];
	}
	
	public function permission_ids_display() {
		if (!$this['permission_ids']) return null;
		else return sqlr("SELECT GROUP_CONCAT(`name` SEPARATOR ', ') FROM users_permissions WHERE id in (".implode(',', array_map('intval', $this['permission_ids'])).")");
	}
	
	public function permission_ids_get() {
		return many_to_many('groups', $this['id'], 'permissions', 'users_groups_permissions');
	}
	
	public function permission_ids_set() {
		return update_many_to_many('groups', $this['id'], 'permissions', $this['permission_ids'], 'users_groups_permissions');
	}
	
}

function super_admin_user_ids() {
	return to_int(sqlla("SELECT id FROM users WHERE type_id = %s", User::SUPER_ADMIN));
}

class UsrStatuss extends Model {
	public static $table = "users_statuss";
	
}

class UsersActive extends User {
	public static function where_sql() {
		return "users.status_id = 1";
	}
}