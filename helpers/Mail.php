<?php
if (!defined('INDEX')) { require dirname(__FILE__).'/../index.php'; exit; }

class Mail {
	const SMTP = 1;
	const MAILCHIMP = 2;
	
	public $attachments = [];
	
	public $to;
	public $from;
	public $cc;
	public $bcc;
	public $subect;
	public $content;
	public $template_id;
	public $method = 1;
	public $data = [];
	
	public function __construct($to = null, $cc = null, $subject = null, $content = null) {
		$this->to = $to;
		$this->cc = $cc;
		$this->bcc = $bcc;
		$this->subject = $subject;
		$this->content = $content;
	}
	
	public function default_template_header() {
		return <<<'EOD'
			<div style="max-width: 800px; margin-left: auto; margin-right: auto;">
EOD;
	}
	
	public function default_template_footer() {
		return <<<'EOD'
			</div>
EOD;
	}
	
	public function process_tags($content) {
		return preg_replace_callback('/\{(.*?)\}/', function($match) {
			if (preg_match('/^\s*([-\w\s]+)(:\s+([-\w\s]+))?\s*$/', $match[1], $m)) {
				$target_text = strtolower($m[1]);
				$property_text = strtolower($m[3]);
				
				$model = $this->data[$target_text];
				
				if (!$model) { return ''; }
				
				if ($property_text) {
					$key = $model->name_from_label($property_text);
					if (!$key) { return ''; }
				
					return h($model->display($key));
				}
				else {
					return h($model->name());
				}
			}
			else return $match[0];
		}, $content);
	}
	
	public function content_with_template() {
		if ($this->template_id) {
			$template = MailingTemplate::get($this->template_id);
			if ($template) {
				return $this->process_tags(str_replace('{content}', $this->content, $template['content']));
			}
			else {
				return $this->process_tags($this->content);
			}
		}
		else {
			return $this->process_tags($this->default_template_header().$this->content.$this->default_template_footer());
		}
	}
	
	public function send() {
		require_once PROJ_ROOT.'/lib/PHPMailer/PHPMailerAutoload.php';
		require_once PROJ_ROOT.'/lib/PHPMailer/extras/class.html2text.php';
		
		if ($this->to === null) { return; }
		$to = is_array($this->to) || ($this->to instanceof ModelIterator) ? $this->to : [ $this->to ];
		$cc = $this->cc;
		$bcc = $this->bcc;
		
		foreach($to as $user) {
			if ( !($user instanceof User) ) { $user = User::get($user); }
			
			$this->data['user'] = $user;
			
			/*if (DEV == true) {
				$to = DEV_EMAIL;
				$subject = "[{$user['email']}] {$this->process_tags($this->subject)}";
			}
			else {*/
				$to = $user['email'];
				$subject = $this->process_tags($this->subject);
			//}

			$mail = new PHPMailer();
			
			if (settings('exchange_server') && settings('exchange_username') && settings('exchange_password'))
			{
				$mail->isSMTP();                                      // Set mailer to use SMTP
				$mail->Host = "".settings('exchange_server')."";  		  // Specify main and backup SMTP servers
				$mail->SMTPAuth = true;                               // Enable SMTP authentication
				$mail->Username = "".settings('exchange_username')."";      // SMTP username
				$mail->Password = "".settings('exchange_password')."";      // SMTP password
				$mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
				$mail->Port = 587;                                    // TCP port to connect to
				$mail->From = "".settings('exchange_username')."";
			}
			else {
				$mail->From = FROM_EMAIL;
			}		
			
			$mail->CharSet = 'utf-8';
			$mail->isHTML(true);
			
			$mail->FromName = SITE_TITLE;
			
			if (is_array($cc))
			{
				foreach ($cc as $ccemail) {
				$mail->addCC(''.$ccemail.'');
				}
			}
			
			if (is_array($bcc))
			{
				foreach ($bcc as $bccemail) {
				$mail->addBCC(''.$bccemail.'');
				}
			}
			
			$mail->addAddress($to, $user->name());

			$mail->Subject = $subject;
			$mail->Body = $this->content_with_template();
			
			$html2text = new html2text($mail->Body);
			$mail->AltBody = $html2text->get_text();
			
			foreach($this->attachments as $filename => $attachment) {
				$mail->addAttachment($attachment, $filename);
			}
			
			if(!$mail->send()) {
				error_message( 'Could not send notice "'.$subject.'" - Error: ' . $mail->ErrorInfo );
			}
			
			if (defined('MAILDEV')) { break; }
		}
		$this->data['user'] = null;
			
	}
	
}
