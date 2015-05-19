<?php
class MailMessage {
	
	protected $Db, $Mail;


	public function __construct($Db) {
		$this->Db = &$Db;
		$this->Config = newClass("Config");
		$this->Mail = newClass("Mail");
	}

	public function send($to, $vars = []) {
		$this->Mail->to = $to;
		$this->prepare($vars);
		return $this->Mail->send();
	}
	

	protected function prepare($vars = []) {
		
	}

}