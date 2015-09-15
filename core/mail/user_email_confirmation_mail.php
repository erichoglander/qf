<?php
class UserEmailConfirmation_Mail extends MailMessage {
	
	public function prepare($vars = []) {
		$this->Mail->subject = "Account e-mail confirmation";
		$this->Mail->message = 
			'<p>Thank you for your registration at '.$this->Config->getSiteName().'<p>'.
			'<p>To confirm your e-mail address and complete your registration follow this link: <a href="'.$vars["link"].'">'.$vars["link"].'</a></p>'.
			'<p>If you do not confirm your e-mail address within 24 hours your account will be deactivated.</p>';
	}

}