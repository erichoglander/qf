<?php
class UserReset_Mail extends MailMessage {
	
	public function prepare($vars = []) {
		$link = SITE_URL."/user/change-password/".$vars['id']."/".$vars['link'];
		$this->Mail->subject = "Account password reset";
		$this->Mail->message = 
			'<p>A password reset has been requested for your account at '.$this->Config->getSiteName().'<p>'.
			'<p>To change your password follow this link: <a href="'.$link.'">'.$link.'</a>'.
			'<br>The link will be invalid after 24 hours.</p>'.
			'<p>If you did not request a password reset you can ignore this message.</p>';
	}

}