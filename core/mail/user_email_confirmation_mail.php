<?php
class UserEmailConfirmation_Mail extends MailMessage {
	
	public function prepare($vars = []) {
		$link = SITE_URL."/user/confirm_email/".$vars["id"]."/".$vars["link"];
		$this->Mail->subject = "Account e-mail confirmation";
		$this->Mail->message = 
			'<p>Thank you for your registration at '.$this->Config->getSiteName().'<p>'.
			'<p>To confirm your e-mail address and complete your registration follow this link: <a href="'.$link.'">'.$link.'</a></p>'.
			'<p>If you do not confirm your e-mail address within 24 hours your account will be deactivated.</p>';
	}

}