<?php
/**
 * Contains the updater controller
 */
/**
 * Updater controller
 * @author Eric Höglander
 */
class Updater_Controller_Core extends Controller {
	
	/**
	 * The access list
	 * @param  string $action
	 * @param  array  $args
	 * @return array
	 */
	public function acl($action, $args = []) {
		return ["update"];
	}

	/**
	 * Perform pending updates
	 *
	 * Usually only accessible through CLI
	 * Usually only database updates
	 * @see    \Updater_Model_Core::getUpdates()
	 * @see    \Updater_Model_Core::runUpdate()
	 * @return string
	 */
	public function updateAction($args = []) {
		$updates = $this->Model->getUpdates();
		$confirm = !empty($args[0]);
		$n = count($updates);
		if ($n) {
			$handle = fopen("php://stdin", "r");
			print t("There are :n new updates. Continue? (y/n)", "en", [":n" => $n])." ";
			if ($confirm) {
				print "y".PHP_EOL;
			}
			else {
				$confirm = trim(fgets($handle)) == "y";
			}
			if ($confirm) {
				foreach ($updates as $Update) {
					$vars = [":update" => $Update->nr(), ":part" => $Update->part()];
					if ($this->Model->runUpdate($Update))
						print t("Performed update :part :update", "en", $vars).PHP_EOL;
					else
						return t("Update :part :update failed", "en", $vars);
				}
			}
			else {
				return t("Aborting");
			}
		}
		$tn = $this->Model->updateTranslations();
		if ($tn)
			return t(":n translations added", "en", [":n" => $tn]);
		if ($n || $tn)
			return t("Update complete");
		else
			return t("No new updates");
	}

}