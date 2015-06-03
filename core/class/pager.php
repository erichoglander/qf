<?php
class Pager_Core {

	public $page = 1;
	public $ppp = 30;
	public $span = 7;
	public $get = "page";
	
	protected $num = 1;
	protected $sep;
	protected $url;


	public function __construct() {
		$this->url = "/".preg_replace("/[\?|\&]".$this->get."\=[0-9]+/", "", substr($_SERVER['REQUEST_URI'], 1));
		$this->sep = (strpos($this->url, "?") ? "&" : "?");
	}

	public function url($x) {
		return $this->url.($x == 1 ? "" : $this->sep.$this->get."=".$x);
	}

	public function tag($x, $html, $class = null) {
		return '<a href="'.$this->url($x).'" class="page'.($class ? " ".$class : "").'">'.$html.'</a>';
	}

	public function start() {
		return ($this->page-1)*$this->ppp;
	}

	public function setNum($num) {
		$this->num = $num;
		$this->pages = ceil($this->num/$this->ppp);
		$this->page = (empty($_GET[$this->get]) ? 0 : abs((int) $_GET[$this->get]));
		if ($this->page < 1)
			$this->page = 1;
		else if ($this->page > $this->pages)
			$this->page = $this->pages;
	}
	
	public function render() {
		if ($this->pages < 2)
			return "";
		$min = max(1, $this->page-$this->span);
		$max = min($this->pages, $this->page+$this->span);
		$html = '<div class="pager">';
		if ($this->page != 1) {
			$html.= $this->tag(1, FontAwesome\Icon("angle-double-left"), "page-first");
			$html.= $this->tag($this->page-1, FontAwesome\Icon("angle-left"), "page-previous");
		}
		for ($i=$min; $i<=$max; $i++) {
			if ($i == $this->page)
				$html.= '<span class="page page-current">'.$i.'</span>';
			else
				$html.= $this->tag($i, $i, "page-".$i);
		}
		if ($this->page != $this->pages) {
			$html.= $this->tag($this->page+1, FontAwesome\Icon("angle-right"), "page-next");
			$html.= $this->tag($this->pages, FontAwesome\Icon("angle-double-right"), "page-last");
		}
		$html.= '</div>';
		return $html;
	}

}