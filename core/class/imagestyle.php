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
   * Saved image path
   * @var string
   */
  public $dest;
  
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
   * Return the uri of a specific style of an image
   * @param  string $name Style name
   * @param  string $src Original file source
   * @return string Ex: styles/upload/myfile_18as0c19.jpg
   */
  public function styleUri($name, $src) {
    $info = pathinfo(strtolower($src));
    $sum = substr(md5(filesize($fname)."_checksum_".$src), 0, 8);
    return "styles/".$name."/".$info["filename"]."_".$sum.".".$info["extension"];
  }
  
  /**
   * Return the url of a specific style of an image
   * @param  string $name Style name
   * @param  string $src Original file source
   * @return string
   */
  public function styleUrl($name, $src) {
    return $this->uri()."/".$this->styleUri($name, $src);
  }
  
  /**
   * Return the path of a specific style of an image
   * @param  string $name Style name
   * @param  string $src Original file source
   * @return string
   */
  public function stylePath($name, $src) {
    return $this->path()."/".$this->styleUri($name, $src);
  }
  
  /**
   * Perform style operations
   * @param  string $name Name of style
   * @param  bool   $save
   * @return string|bool  url to image if $save is true
   */
  public function style($name, $save = true) {
    if (!$this->styleExists($name))
      return null;
    $this->info = pathinfo(strtolower($this->src));
    if ($save) {
      $target_path = $this->stylePath($name, $this->src);
      $target_url = $this->styleUrl($name, $this->src);
      if (file_exists($target_path)) {
        list($this->width, $this->height) = getimagesize($target_path);
        $this->dest = $target_path;
        return $target_url;
      }
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
      $info = pathinfo($target_path);
      if (!file_exists($info["dirname"]))
        mkdir($info["dirname"], 0775, true);
      if (!$this->save($target_path))
        return null;
      return $target_url;
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
   * Scale and crop image to cover given dimensions
   * Will scale and crop to given aspect ratio if image is smaller
   * Library: GD
   * @see scaleCrop
   * @param int $w
   * @param int $h
   */
  public function scaleCropRatio($w, $h) {
    if (!$this->im)
      return;
    if ($w < $this->width && $h < $this->height) {
      $this->scaleCrop($w, $h);
      return;
    }
    $src_ratio = $this->width/$this->height;
    $end_ratio = $w/$h;
    $x = $y = 0;
    // If the target image is wider
    if ($src_ratio <= $end_ratio) {
      $w = $this->width;
      $h = $w/$end_ratio;
      $y = ($this->height-$h)/2;
    }
    else if ($src_ratio > $end_ratio) {
      $h = $this->height;
      $w = $h*$end_ratio;
      $x = ($this->width-$w)/2;
    }
    $im = imagecreatetruecolor($w, $h);
    $this->setAlpha($im);
    imagecopyresampled($im, $this->im, 0, 0, $x, $y, $this->width, $this->height, $this->width, $this->height);
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
   * Add border of given color to image
   * Library: GD
   * @param int   $w left/right padding
   * @param int   $h top/bottom padding
   * @param array $color
   */
  public function border($w, $h, $color = null) {
    if (!$this->im || (!$w && !$h))
      return;
    if (!$color)
      $color = [255,255,255];
    $str = "#";
    foreach ($color as $dec) {
      $hex = dechex($dec);
      if (strlen($hex) == 1)
        $hex = "0".$hex;
      $str.= $hex;
    }
    $cp_w = $this->width;
    $cp_h = $this->height;
    $this->width+= $w*2;
    $this->height+= $h*2;
    $im = imagecreatetruecolor($this->width, $this->height);
    if (!empty($color)) {
      $color = imagecolorallocate($im, $color[0], $color[1], $color[2]);
      imagefill($im, 0, 0, $color);
    }
    $this->setAlpha($im);
    imagecopyresampled($im, $this->im, $w, $h, 0, 0, $cp_w, $cp_h, $cp_w, $cp_h);
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
    $this->width = $w;
    $this->height = $h;
  }
  
  /**
   * Scale and crop image to cover given dimensions
   * Will scale and crop to given aspect ratio if image is smaller
   * Library: Imagick
   * @see imagickScaleCrop
   * @param int $w
   * @param int $h
   */
  public function imagickScaleCropRatio($w, $h) {
    if (!$this->im)
      return;
    if ($w < $this->width && $h < $this->height) {
      $this->imagickScaleCrop($w, $h);
      return;
    }
    $src_ratio = $this->width/$this->height;
    $end_ratio = $w/$h;
    $x = $y = 0;
    // If the target image is wider
    if ($src_ratio <= $end_ratio) {
      $w = $this->width;
      $h = $w/$end_ratio;
      $y = ($this->height-$h)/2;
    }
    else if ($src_ratio > $end_ratio) {
      $h = $this->height;
      $w = $h*$end_ratio;
      $x = ($this->width-$w)/2;
    }
    $this->im->cropImage($w, $h, $x, $y);
    $this->width = $w;
    $this->height = $h;
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
    $this->width = $w;
    $this->height = $h;
    $this->im->thumbnailImage($this->width, $this->height, true, true);
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
    if ($w >= $this->width && $h >= $this->height)
      return;
    $this->width = $w;
    $this->height = $h;
    $this->im->thumbnailImage($this->width, $this->height, true);
  }
  
  /**
   * Add border of given color to image
   * Library: Imagick
   * @param int   $w left/right padding
   * @param int   $h top/bottom padding
   * @param array $color
   */
  public function imagickBorder($w, $h, $color = null) {
    if (!$this->im || (!$w && !$h))
      return;
    if (!$color)
      $color = [255,255,255];
    $str = "#";
    foreach ($color as $dec) {
      $hex = dechex($dec);
      if (strlen($hex) == 1)
        $hex = "0".$hex;
      $str.= $hex;
    }
    $this->width+= $w*2;
    $this->height+= $h*2;
    $this->im->borderImage($str, $w, $h);
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
    $this->dest = $dest;
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