<?php
class Error_Controller extends Controller {
	
	public function databaseFail() {
		return $this->busy();
	}

	public function busy() {
		return "Server busy";
	}

	public function notFound() {
		return "404 - Not found";
	}

	public function accessDenied() {
		return "403 - Forbidden";
	}

};