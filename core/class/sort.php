<?php
class Sort_Core {
	
	public $sort, $order;
	
	
	public function __construct($sorts, $sort = null, $order = "asc") {
		$this->sorts = $sorts;
		if ($sort) {
			$this->sort = $sort;
			$this->order = $order;
		}
		$this->get();
	}
	
	public function get() {
		$sort = (array_key_exists("sort", $_GET) ? $_GET["sort"] : null);
		$order = (array_key_exists("order", $_GET) ? $_GET["order"] : null);
		if ($sort && in_array($sort, $this->sorts)) {
			$this->sort = $sort;
			if ($order)
				$this->order = ($order == "desc" ? "desc" : "asc");
		}
	}
	
	public function url($str, $def = "asc") {
		$url = REQUEST_URI;
		$url = preg_replace("/[\?|\&]sort\=[^\&]+/i", "", $url);
		$url = preg_replace("/\&order\=[a-z0-9\-\_]+/i", "", $url);
		if ($str == $this->sort)
			$order = ($this->order == "desc" ? "asc" : "desc");
		else
			$order = $def;
		$url.= (strpos($url, "?") ? "&" : "?")."sort=".$str."&order=".$order;
		return BASE_URL.$url;
	}
	
	public function sql() {
		if (!$this->sort)
			return null;
		return $this->sort." ".$this->order;
	}
	
}