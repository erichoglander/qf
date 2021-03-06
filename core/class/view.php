<?php
/**
 * Contains view class
 */

/**
 * View class
 * @author Eric Höglander
 */
class View_Core extends Model {

  /**
   * Html object
   * @var \Html_Core
   */
  public $Html;
  
  /**
   * Name of the controller
   * @var string
   */
  protected $controller_name;

  /**
   * Name of the view
   * @var string
   */
  protected $name;


  /**
   * Constructor
   * @param string $controller_name
   * @param string $name
   * @param array  $variables
   */
  public function construct($controller_name, $name, $variables = []) {
    $this->controller_name = $controller_name;
    $this->name = $name;
    $this->variables = $variables;
    if ($this->Db) {
      $this->Html = $this->newClass("Html");
      $this->Html->title = ucwords($controller_name)." ".$name;
    }
  }

  /**
   * Renders the view
   * @return string
   */
  public function render() {
    if ($this->Html) {
      if (!empty($this->variables["html"])) {
        foreach ($this->variables["html"] as $key => $var)
          $this->Html->{$key} = $var;
        unset($this->variables["html"]);
      }
      if (IS_FRONT_PAGE)
        $this->Html->body_class[] = "front";
      $this->Html->body_class[] = cssClass("page-".$this->controller_name."-".$this->name);
      $this->Html->body_class[] = cssClass("controller-".$this->controller_name);
      $this->Html->body_class[] = cssClass("view-".$this->name);
      $this->Html->body_class[] = cssClass("lang-".LANG);
      $this->Html->preView();
    }
    $output = $this->renderContent();
    if ($this->Html) {
      $this->Html->content = $output;
      $output = $this->Html->renderHtml();
    }
    return $output;
  }
  
  /**
   * Renders the view content
   * @return string
   */
  public function renderContent() {
    $path = $this->path();
    extract($this->variables);
    ob_start();
    include $path;
    return ob_get_clean();
  }


  /**
   * Returns the filepath to the view
   * @return string
   */
  protected function path() {
    $path = filePath("view/".$this->controller_name."/".$this->name.".php");
    if ($path)
      return $path;
    else
      throw new Exception("Unable to find view ".$this->controller_name."/".$this->name);
  }

};