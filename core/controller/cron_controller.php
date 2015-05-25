<?php
class Cron_Controller_Core extends Controller {
	
	public function acl($action) {
		return ["cronRun"];
	}

	public function indexAction() {
		$Cron = newClass("Cron", $this->Db);
		$start = microtime(true);
		$Cron->run();
		$time = round(microtime(true) - $start, 4);
		setmsg(t("Cron completed in :sec seconds", "en", [":sec" => $time]), "success");
		addlog($this->Db, "cron", t("Cron completed in :sec seconds", "en", [":sec" => $time]));
		redirect();
	}

}