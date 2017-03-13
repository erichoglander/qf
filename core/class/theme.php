<?php
/**
 * Contains theme class
 */

/**
 * Theme class
 *
 * A base to be extended by themes
 * 
 * @author Eric Höglander
 */
class Theme extends Model {
  
  /**
   * Name of the theme
   * @var string
   */
  public $name;

  /**
   * Css files
   * @var array
   */
  public $css = [];

  /**
   * Js files
   */
  public $js = [];


  /**
   * Constructor
   */
  public function construct() {
    if (!$this->name)
      throw new Exception("Missing theme name.");
    $this->loadFiles();
  }

  /**
   * Renders a theme template
   * @param  string $part
   * @param  array  $vars
   * @return string
   */
  public function render($part, $vars = []) {
    $template = $this->getTemplate($part);
    if (!$template)
      throw new Exception("Unable to find ".$part." template for ".$this->name." theme");
    $this->preRender($part, $vars);
    if ($part == "html") {
      foreach ($this->css as $css) {
        if (strpos($css, "http") === 0)
          $url = $css;
        else
          $url = fileUrl("theme/".$this->name."/css/".$css);
        if ($url)
          $vars["css"][] = $url;
      }
      foreach ($this->js as $js) {
        if (strpos($js, "http") === 0)
          $url = $js;
        else
          $url = fileUrl("theme/".$this->name."/js/".$js);
        if ($url)
          $vars["js"][] = $url;
      }
    }
    extract($vars);
    ob_start();
    include $template;
    return ob_get_clean();
  }


  /**
   * Load theme files
   */
  protected function loadFiles() {
  }

  /**
   * Called before rendering a theme template
   * @param  string $part
   * @param  array  &$vars
   */
  protected function preRender($part, &$vars) {
  }

  /**
   * Get file path of a theme template
   * @param  string $name
   * @return string
   */
  protected function getTemplate($name) {
    return filePath("theme/".$this->name."/template/".$name.".php");
  }

};