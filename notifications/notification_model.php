<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

class Notification extends Model {
	public static $table = "notifications";
	public static $properties = array(
		'sender_id' => array('type' => 'select', 'foreign' => 'User'),
		'user_ids' => array('type' => 'select', 'external' => 'notifications_users', 'foreign' => 'User'),
		
		'updated_at' => array('label' => 'Modified', 'display' => true),
	);
	public static $name_field = '%s.text';
	
	public static function send($model, $user_ids, $text) {
		$notifications = self::create([
			'model_class' => get_class($model),
			'model_id' => $model['id'],
			'model_name' => $model->name(),
			'text' => $text,
			'sender_id' => ChangeLog::$record_user ? user_id() : null,
			'user_ids' => $user_ids,
		]);
		
	}
	
	public function __get($key) {
		if ( $key === 'model' ) {
			$class = $this['model_class'];
			$this->$key = $this["{$key}_id"] === null ? null : new $class($this["{$key}_id"]);
			return $this->$key;
		}
		else return parent::__get($key);
	}

	public function after_create() {
		$mail = new Mail();
		$mail->to = $this['user_ids'];
		$mail->subject = "Notification: {$this['text']}";
		$mail->content = "<p>You have received a notification on BCIU for ".h($this->model->name()).".</p><p>".h($this['text'])."</p>";
		$mail->send();
	}
	
	public static function recent_for_user($user_id = null) {
		if ($user_id === null) { $user_id = user_id(); }
		return self::find([
			'where' => "notifications.id IN (SELECT notification_id FROM notifications_users WHERE user_id = ".sanitize($user_id).")",
			'limit' => 10,
			'sort' => 'created_at desc',
		]);
	}
	
}
