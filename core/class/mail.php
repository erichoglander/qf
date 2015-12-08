<?php
/**
 * Contains the mail class
 */

/**
 * The mail class
 * @author Eric Höglander
 */
class Mail_Core {

	/**
	 * Sender e-mail address
	 * @var string
	 */
	public $from;

	/**
	 * Receiver e-mail address
	 * @var string
	 */
	public $to;

	/**
	 * The e-mail subject
	 * @var string
	 */
	public $subject;

	/**
	 * The e-mail body
	 * @var string
	 */
	public $message;

	/**
	 * If true, adds a signature to the end of the message
	 * @see signature
	 * @var boolean
	 */
	public $include_signature = true;

	/**
	 * File attachments
	 * @var array
	 */
	public $attachments = [];

	/**
	 * If true, send e-mail as HTML
	 * @var bool
	 */
	public $html = true;


	/**
	 * Config object
	 * @var \Config_Core
	 */
	protected $Config;

	/**
	 * Database object
	 * @var \Db_Core
	 */


	/**
	 * Constructor
	 * @param \Db_Core $Db
	 */
	public function __construct($Db) {
		$this->Db = $Db;
		$this->Config = newClass("Config");
		$this->setDefault();
		$this->setDefaultHeaders();
	}
	
	
	/**
	 * Set default values
	 */
	public function setDefault() {
		$this->from = "info@".BASE_DOMAIN;
	}

	/**
	 * The signature to append to the end of the message
	 * @return string
	 */
	public function signature() {
		return '<hr><p>'.SITE_URL.'</p>';
	}

	/**
	 * Prepare and send e-mail
	 * @return bool
	 */
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
		$message = $this->message;
		if ($this->include_signature)
			$message.= $this->signature();
		$headers = "";

		foreach ($this->headers as $key => $val)
			$headers.= $key.": ".$val."\r\n";

		if (!$this->mail($this->to, $subject, $message, $headers, "-f".$this->from)) {
			addlog("mail", "Mail failed ".$this->to." (".$this->subject.")", $data, "error");
			return false;
		}
		else {
			addlog("mail", "Mail sent to ".$this->to." (".$this->subject.")", $data, "success");
			return true;
		}
	}

	/**
	 * Sets default headers
	 */
	public function setDefaultHeaders() {
		$this->setHeaders($this->defaultHeaders());
	}

	/**
	 * Default headers
	 * @return array
	 */
	public function defaultHeaders() {
		return [
			"Mime-Version" => "1.0",
			"Date" => date("D, j M Y H:i:s O"),
			"Content-Type" => "text/plain; charset=UTF-8",
			"X-Mailer" => "PHP/".phpversion(),
			"X-MSMail-Priority" => "High",
		];
	}

	/**
	 * Sets an e-mail header
	 * @param string $key
	 * @param string $val
	 */
	public function setHeader($key, $val) {
		$this->headers[$key] = $val;
	}

	/**
	 * Setter for $header
	 * @param array $arr
	 */
	public function setHeaders($arr) {
		foreach ($arr as $key => $val)
			$this->setHeader($key, $val);
	}

	/**
	 * Attach a file to e-mail
	 * @see    prepareAttachments
	 * @param  string             $file Path to file
	 */
	public function attach($file) {
		$this->attachments[] = $file;
	}


	/**
	 * Send the mail
	 * @see mail()
	 * @param  string $to
	 * @param  string $subject
	 * @param  string $message
	 * @param  string $headers
	 * @return bool
	 */
	protected function mail($to, $subject, $message, $headers, $params) {
		return mail($this->to, $subject, $message, $headers, $params);
	}

	/**
	 * Prepare and encode attachments
	 */
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