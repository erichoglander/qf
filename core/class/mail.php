<?php
class Mail_Core {

	public $from, $to, $subject, $message;
	public $include_signature = true;
	public $attachments = [];
	public $html = true;

	protected $Config;


	public function __construct($Db) {
		$this->Db = &$Db;
		$this->from = "info@".BASE_DOMAIN;
		$this->Config = newClass("Config");
		$this->setDefaultHeaders();
	}

	public function signature() {
		return '<hr><p>'.SITE_URL.'</p>';
	}

	public function send() {

		if (!$this->from || !$this->to || !$this->message || !$this->subject)
			return false;

		if ($this->html)
			$this->setHeader("Content-Type", "text/html; charset=UTF-8");

		$this->setHeader("From", $this->from);
		$this->setHeader("Reply-To", $this->from);
		$this->setHeader("Return-Path", $this->from);

		$data = [
			"headers" => $this->headers,
			"message" => $this->message,
		];

		$this->prepareAttachments();

		$subject = "=?UTF-8?B?".base64_encode($this->subject)."?=";
		$message = $this->message.$this->signature();
		$headers = "";

		foreach ($this->headers as $key => $val)
			$headers.= $key.": ".$val."\r\n";

		if (!$this->mail($this->to, $subject, $message, $headers)) {
			addlog($this->Db, "mail", "Mail failed ".$this->to." (".$this->subject.")", $data, "error");
			return false;
		}
		else {
			addlog($this->Db, "mail", "Mail sent to ".$this->to." (".$this->subject.")", $data, "success");
			return true;
		}
	}

	public function setDefaultHeaders() {
		$this->setHeaders($this->defaultHeaders());
	}
	public function defaultHeaders() {
		return [
			"Mime-Version" => "1.0",
			"Date" => date("D, j M Y H:i:s O"),
			"Content-Type" => "text/plain; charset=UTF-8",
			"X-Mailer" => "PHP/".phpversion(),
			"X-MSMail-Priority" => "High",
		];
	}

	public function setHeader($key, $val) {
		$this->headers[$key] = $val;
	}
	public function setHeaders($arr) {
		foreach ($arr as $key => $val)
			$this->setHeader($key, $val);
	}

	public function attach($file) {
		$this->attachments[] = $file;
	}


	protected function mail($to, $subject, $message, $headers) {
		// return mail($this->to, $subject, $message, $headers);
		setmsg($message);
		return true;
	}
	protected function prepareAttachments() {
		if (!empty($this->attachments)) {
			$hash = md5(time().microtime());
			$tag = "PHP-mail=".$hash;
			$this->setHeader("Content-Type", 'multipart/mixed; boundary="'.$tag.'"');

			$pre =
			PHP_EOL.'--'.$tag.
			PHP_EOL.'Content-Type: text/'.($this->html ? "html" : "plain").'; charset=UTF-8'.
			PHP_EOL.'Content-Transfer-Encoding: 7bit'.
			PHP_EOL.PHP_EOL;

			$post = PHP_EOL.PHP_EOL.'--'.$tag.'--';

			$str = '';
			foreach ($this->attachments as $i => $attachment) {
				$info = (object) pathinfo($attachment);
				$file = chunk_split(base64_encode(file_get_contents($attachment)));
				$str.=
					PHP_EOL.PHP_EOL.'--'.$tag.
					PHP_EOL.'Content-Type: '.mime_content_type($attachment).'; name="'.$info->basename.'"'.
					PHP_EOL.'Content-Transfer-Encoding: base64'.
					PHP_EOL.'Content-Disposition: attachment; filename='.$info->basename.
					PHP_EOL.PHP_EOL.$file;
			}
			$this->message = $pre.$this->message.$str.$post;
		}
	}

}