<?php
class Cron_Controller_Core extends Controller {
	
	public function acl($action) {
		return ["cronRun"];
	}

	public function indexAction() {
		$time = microtime(true);
		$this->Model->run();
		$time = round(microtime(true) - $time, 4);
		setmsg(t("Cron completed in :sec seconds", "en", [":sec" => $time]), "success");
		addlog("cron", "Cron completed in ".$time." seconds");
		redirect();
	}

}