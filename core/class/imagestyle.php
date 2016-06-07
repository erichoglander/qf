<?php
/**
 * Contains imagestyle class
 */

/**
 * Imagestyle class
 *
 * Performs various image transformations
 *
 * @author Eric Höglander
 */
class Imagestyle_Core {
  
  /**
   * Original image path
   * @var string
   */
  public $src;
  
  /**
   * Where to save resulting image
   * @var string public or private
   */
  public $dir = "public";
  
  /**
   * Width of image
   * @var int
   */
  public $width;
  
  /**
   * Height of image
   * @var int
   */
  public $height;
  
  /**
   * Array of available image styles
   * @var array
   */
  public $styles = [
    "upload" => ["scale" => [600, 300]],
  ];
  
  /**
   * File info
   * @var array
   */
  protected $info;
  
  /**
   * Image resource
   * @var resource
   */
  protected $im;
  
  /**
   * Which image library to use. GD will be used if null or unknown.
   * @var string
   */
  protected $lib = "gd";
  
  
  /**
   * Constructor
   * @param string $src
   */
  public function __construct($src = null) {
    $this->src = $src;
    if (class_exists("Imagick"))
      $this->lib = "imagick";
  }
  
  /**
   * Check if a style exists in $styles
   * @param string $name
   * @return bool
   */
  public function styleExists($name) {
    return array_key_exists($name, $this->styles);
  }
  
  /**
   * Perform style operations
   * @param  string $name Name of style
   * @param  bool   $save
   * @return string
   */
  public function style($name, $save = true) {
    if (!$this->styleExists($name))
      return null;
    $this->info = pathinfo(strtolower($this->src));
    $dir = "styles/".$name;
    $fname = $this->info["basename"];
    $path = $this->path();
    $uri = $this->uri();
    if ($save) {
      $target_path = $path."/".$dir."/".$fname;
      $target_uri = $uri."/".$dir."/".$fname;
      if (file_exists($target_path))
        return $target_uri;
    }
    if (!$this->loadSource())
      return null;
    foreach ($this->styles[$name] as $method => $params) {
      $func = $this->lib.ucwords($method);
      if (!is_callable([$this, $func]))
        $func = $method;
      if (!is_callable([$this, $func]))
        continue;
      call_user_func_array([$this, $func], $params);
    }
    if ($save) {
      if (!file_exists($path."/".$dir))
        mkdir($path."/".$dir, 0774, true);
      if (!$this->save($target_path))
        return null;
      return $target_uri;
    }
    return true;
  }
  
  /**
   * Returns uri of target directory
   * @return string
   */
  public function uri() {
    return ($this->dir == "private" ? PRIVATE_URI : PUBLIC_URI)."/images";
  }
  
  /**
   * Returns path of target directory
   * @return string
   */
  public function path() {
    return ($this->dir == "private" ? PRIVATE_PATH : PUBLIC_PATH)."/images";
  }
  
  /**
   * Scale and crop image to cover given dimensions
   * Library: GD
   * @param int $w
   * @param int $h
   */
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
  
  /**
   * Scale image to fit inside given dimensions and expand with a background
   * Library: GD
   * @param int   $w
   * @param int   $h
   * @param array $bg
   */
  public function scaleExpand($w, $h, $bg = null) {
    if (!$this->im)
      return;
    $src_ratio = $this->width/$this->height;
    $end_ratio = $w/$h;
    $x = $y = 0;
    if ($src_ratio <= $end_ratio) {
      $cp_w = $h*$src_ratio;
      $cp_h = $h;
      $x = ($w-$cp_w)/2;
    }
    else if ($src_ratio > $end_ratio) {
      $cp_w = $w;
      $cp_h = $w/$src_ratio;
      $y = ($h-$cp_h)/2;
    }
    $im = imagecreatetruecolor($w, $h);
    if (!empty($bg)) {
      $color = imagecolorallocate($im, $bg[0], $bg[1], $bg[2]);
      imagefill($im, 0, 0, $color);
    }
    $this->setAlpha($im);
    imagecopyresampled($im, $this->im, $x, $y, 0, 0, $cp_w, $cp_h, $this->width, $this->height);
    $this->width = $w;
    $this->height = $h;
    $this->im = $im;
  }
  
  /**
   * Scale image to fit inside given dimensions
   * Library: GD
   * @param int $w Max width
   * @param int $h Max height
   */
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
  
  /**
   * Scale and crop image to cover given dimensions
   * Library: Imagick
   * @param int $w
   * @param int $h
   */
  public function imagickScaleCrop($w, $h) {
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
      $y = ($cp_h-$h)/2;
    }
    else if ($src_ratio > $end_ratio) {
      $cp_w = $h*$src_ratio;
      $cp_h = $h;
      $x = ($cp_w-$w)/2;
    }
    $this->im->thumbnailImage($cp_w, $cp_h, false);
    $this->im->cropImage($w, $h, $x, $y);
  }
  
  /**
   * Scale image to fit inside given dimensions and expand with a background
   * Library: Imagick
   * @param int   $w
   * @param int   $h
   * @param array $bg
   */
  public function imagickScaleExpand($w, $h, $bg = null) {
    if (!$this->im)
      return;
    if (!empty($bg)) {
      $str = "#";
      foreach ($bg as $dec) {
        $hex = dechex($dec);
        if (strlen($hex) == 1)
          $hex = "0".$hex;
        $str.= $hex;
      }
      $this->im->setImageBackgroundColor($str);
    }
    $this->im->thumbnailImage($w, $h, true, true);
  }
  
  /**
   * Scale image to fit inside given dimensions
   * Library: Imagick
   * @param int $w Max width
   * @param int $h Max height
   */
  public function imagickScale($w = 0, $h = 0) {
    if (!$this->im || (!$w && !$h))
      return;
    $ratio = $this->width/$this->height;
    if (!$h) 
      $h = $w/$ratio;
    else if (!$w) 
      $w = $h*$ratio;
    $this->im->thumbnailImage($w, $h, true);
  }
  
  /**
   * Load image
   * @return bool
   */
  public function loadSource() {
    if (!file_exists($this->src))
      return false;
    $this->info = pathinfo(strtolower($this->src));
    list($this->width, $this->height) = getimagesize($this->src);
    if ($this->lib == "imagick") {
      $this->im = new Imagick($this->src);
    }
    else {
      if ($this->info["extension"] == "jpg" || $this->info["extension"] == "jpeg")
        $this->im = imagecreatefromjpeg($this->src);
      else if ($this->info["extension"] == "png")
        $this->im = imagecreatefrompng($this->src);
      else if ($this->info["extension"] == "gif")
        $this->im = imagecreatefromgif($this->src);
      else
        return false;
      if (!$this->im) {
        // Some jpgs are saved as gifs
        if ($this->info["extension"] == "gif") {
          $this->im = imagecreatefromjpeg($this->src);
          if ($this->im)
            $this->info["extension"] = "jpg";
        }
        // And some gifs are saved as jpgs
        else if ($this->info["extension"] == "jpg" || $this->info["extension"] == "jpeg") {
          $this->im = imagecreatefromgif($this->src);
          if ($this->im)
            $this->info["extension"] = "gif";
        }
        if (!$this->im)
          return false;
      }
      $this->setAlpha($this->im);
    }
    return true;
  }
  
  /**
   * Save resulting image
   * @param string $dest Destination
   * @param bool
   */
  public function save($dest) {
    if ($this->lib == "imagick") {
      return $this->im->writeImage($dest);
    }
    else {
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
  }
  
  /**
   * Get content-type based type of image
   * @return string
   */
  public function contentType() {
    if ($this->lib == "imagick" && $this->im)
      return "image/".$this->im->getImageFormat();
    else
      return "image/".$this->info["extension"];
  }
  
  /**
   * Output image data
   */
  public function output() {
    header("Content-Type: ".$this->contentType());
    if ($this->lib == "imagick") {
      print $this->im;
    }
    else {
      try {
        if ($this->info["extension"] == "jpg" || $this->info["extension"] == "jpeg")
          imagejpeg($this->im, null, 100);
        else if ($this->info["extension"] == "png")
          imagepng($this->im, null, 1);
        else if ($this->info["extension"] == "gif")
          imagegif($this->im, null);
      }
      catch(Exception $e) {
        print "Could not generate image";
      }
    }
    exit;
  }
  
  
  /**
   * Set image alpha for png images
   * @see imagealphablending()
   * @see imagesavealpha()
   * @param resource $im
   */
  protected function setAlpha(&$im) {
    if ($this->info["extension"] == "png") {
      $opacity = imagecolorallocatealpha($im, 255, 255, 255, 127);
      imagefill($im, 0, 0, $opacity);
      imagealphablending($im, true);
      imagesavealpha($im, true);
    }
  }
  
};