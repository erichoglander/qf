<?php
class Imagestyle_Core {
	
	public $src;
	public $dir = "public";
	public $width, $height;
	public $styles = [
		"upload" => ["scale" => [600, 300]],
	];
	
	protected $info;
	protected $im;
	
	
	public function __construct($src = null) {
		$this->src = $src;
	}
	
	public function styleExists($name) {
		return array_key_exists($name, $this->styles);
	}
	public function style($name) {
		if (!$this->styleExists($name))
			return null;
		$info = pathinfo(strtolower($this->src));
		$dir = "styles/".$name;
		$fname = $info["basename"];
		$path = $this->path();
		$uri = $this->uri();
		$target_path = $path."/".$dir."/".$fname;
		$target_uri = $uri."/".$dir."/".$fname;
		if (file_exists($target_path))
			return $target_uri;
		if (!$this->loadSource())
			return null;
		foreach ($this->styles[$name] as $method => $params)
			call_user_func_array([$this, $method], $params);
		if (!file_exists($path."/".$dir))
			mkdir($path."/".$dir, 0774, true);
		if (!$this->save($target_path))
			return null;
		return $target_uri;
	}
	
	public function uri() {
		return ($this->dir == "private" ? PRIVATE_URI : PUBLIC_URI)."/images";
	}
	public function path() {
		return ($this->dir == "private" ? PRIVATE_PATH : PUBLIC_PATH)."/images";
	}
	
	public function scaleCrop($w, $h) {
		if (!$this->im)
			return;
		if ($w > $this->width && $h > $this->height)
			return;
		$src_ratio = $this->width/$this->height;
		$end_ratio = $w/$h;
		$x = $y = 0;
		if ($src_ratio <= $end_ratio) {
			$cp_w = $w;
			$cp_h = $w/$src_ratio;
			$y = ($h-$cp_h)/2;
		}
		else if ($src_ratio > $end_ratio) {
			$cp_w = $h*$src_ratio;
			$cp_h = $h;
			$x = ($w-$cp_w)/2;
		}
		$im = imagecreatetruecolor($w, $h);
		$this->setAlpha($im);
		imagecopyresampled($im, $this->im, $x, $y, 0, 0, $cp_w, $cp_h, $this->width, $this->height);
		$this->width = $w;
		$this->height = $h;
		$this->im = $im;
	}
	
	public function scale($w = 0, $h = 0) {
		if (!$this->im)
			return;
		$ratio = $this->width/$this->height;
		if ($w && $h) {
			if ($w >= $this->width && $h >= $this->height)
				return;
			$new_ratio = $w/$h;
			if ($new_ratio < $ratio)
				$h = 0;
			else
				$w = 0;
		}
		if ($w && $w < $this->width) 
			$h = $w/$ratio;
		else if ($h && $h < $this->height) 
			$w = $h*$ratio;
		else 
			return;
		$im = imagecreatetruecolor($w, $h);
		$this->setAlpha($im);
		imagecopyresampled($im, $this->im, 0, 0, 0, 0, $w, $h, $this->width, $this->height);
		$this->width = $w;
		$this->height = $h;
		$this->im = $im;
	}
	
	public function loadSource() {
		if (!file_exists($this->src))
			return false;
		$this->info = pathinfo(strtolower($this->src));
		if ($this->info["extension"] == "jpg" || $this->info["extension"] == "jpeg")
			$this->im = imagecreatefromjpeg($this->src);
		else if ($this->info["extension"] == "png")
			$this->im = imagecreatefrompng($this->src);
		else if ($this->info["extension"] == "gif")
			$this->im = imagecreatefromgif($this->src);
		else
			return false;
		list($this->width, $this->height) = getimagesize($this->src);
		return true;
	}
	
	public function save($dest) {
		try {
			if ($this->info["extension"] == "jpg" || $this->info["extension"] == "jpeg")
				$this->im = imagejpeg($this->im, $dest, 100);
			else if ($this->info["extension"] == "png")
				$this->im = imagepng($this->im, $dest, 1);
			else if ($this->info["extension"] == "gif")
				$this->im = imagegif($this->im, $dest);
		}
		catch(Exception $e) {
			return false;
		}
		if (!$this->im)
			return false;
		return true;
	}
	
	
	protected function setAlpha(&$im) {
		if ($this->info["extension"] == "png") {
			$opacity = imagecolorallocatealpha($im, 0, 0, 0, 127);
			imagefill($im, 0, 0, $opacity);
			imagealphablending($im, true);
			imagesavealpha($im, true);
		}
	}
	
};