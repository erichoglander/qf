<?php
/**
 * Contains the cron controller
 */
/**
 * Cron controller
 */
class Cron_Controller_Core extends Controller {
	
	/**
	 * The access list
	 * @param  string $action
	 * @param  array  $args
	 * @return array
	 */
	public function acl($action, $args = []) {
		return ["cronRun"];
	}

	/**
	 * Run cron
	 */
	public function indexAction() {
		$time = microtime(true);
		$this->Model->run();
		$time = round(microtime(true) - $time, 4);
		setmsg(t("Cron completed in :sec seconds", "en", [":sec" => $time]), "success");
		addlog("cron", "Cron completed in ".$time." seconds");
		redirect();
	}

}