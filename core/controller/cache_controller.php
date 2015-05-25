<?php
class Cache_Controller_Core extends Controller {
	
	public function acl($action) {
		return ["cacheClear"];
	}

	public function clearAction() {
		$this->Cache->clear();
		setmsg(t("Cache cleared!"), "success");
		redirect();
	}

}