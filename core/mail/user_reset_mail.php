<?php
class UserReset_Mail_Core extends MailMessage {
	
	public function prepare($vars = []) {
		$this->Mail->subject = "Account password reset";
		$this->Mail->message = 
			'<p>A password reset has been requested for your account at '.$this->Config->getSiteName().'</p>'.
			'<p>To change your password follow this link: <a href="'.$vars["link"].'">'.$vars["link"].'</a>'.
			'<br>The link will be invalid after 24 hours.</p>'.
			'<p>If you did not request a password reset you can ignore this message.</p>';
	}

}