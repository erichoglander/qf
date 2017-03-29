<?php
/**
 * Contains the form class
 */

/**
 * Form class
 *
 * Goal is to be flexible, extendable, and easy to use.
 * @see  \FormItem
 * @see  structure
 * @author Eric Höglander
 */
class Form extends Model {

  /**
   * HTML attributes for form element
   * @var array
   */
  protected $attributes = [
    "method" => "POST",
    "action" => "",
    "class" => "form"
  ];

  /**
   * Variables that can be used when creating structure
   * @see structure
   * @var array
   */
  protected $vars;

  /**
   * Name of the form
   * @var string
   */
  protected $name;

  /**
   * Form items
   * @var array
   */
  protected $items;

  /**
   * If true, form will be submitted with ajax
   * @var bool
   */
  protected $ajax = false;

  /**
   * If true, applies a no-input automatic spamfilter
   * @var bool
   */
  protected $spamfilter = false;

  /**
   * If true, focuses widnow on first item error
   * @var bool
   */
  protected $error_focus = false;

  /**
   * Contains error messages
   * @var array
   */
  protected $errors = [];

  /**
   * Will be rendered before the form
   * @var string
   */
  protected $prefix;

  /**
   * Will be rendered after the form
   * @var string
   */
  protected $suffix;
  
  /**
   * Any js files to be included with form
   * @var array
   */
  protected $js = [];


  /**
   * Constructor
   * @param array $vars
   */
  public function construct($vars = []) {
    $this->attributes["action"] = BASE_PATH.REQUEST_URI;
    $this->setVars($vars);
  }

  /**
   * Renders the form
   * @return string
   */
  public function render() {
    $path = $this->templatePath();
    $vars = [
      "items" => $this->renderItems(),
      "errors" => $this->getErrors(),
      "attributes" => $this->attributes(),
      "token" => $this->token(),
      "name" => $this->name,
      "prefix" => $this->prefix,
      "suffix" => $this->suffix,
      "ajax" => $this->ajax,
      "js" => $this->js(),
    ];
    return renderTemplate($path, $vars);
  }
  
  /**
   * Renders certain items only
   * @param  array  $items
   * @return string
   */
  public function renderPartly($items) {
    $html = "";
    if (!is_array($items))
      $items = [$items];
    foreach ($items as $item) {
      if (!is_array($item)) {
        $name = $item;
        $render = true;
      }
      else {
        $name = $item[0];
        $render = count($item) == 1;
      }
      foreach ($this->items as $Item) {
        if ($Item->name == $name) {
          if ($render)
            $html.= $Item->render();
          else
            $html.= $Item->renderPartly(array_slice($item, 1));
        }
      }
    }
    return $html;
  }

  /**
   * Get variable from $vars
   * @param  string $name
   * @param  string $def  Default value is variable isn't set
   * @return mixed
   */
  public function get($name, $def = null) {
    if (!array_key_exists($name, $this->vars))
      return $def;
    return $this->vars[$name];
  }

  /**
   * Sets variable in $vars
   * @param string $name
   * @param mixed  $value
   */
  public function set($name, $value) {
    $this->vars[$name] = $value;
    $this->loadStructure();
  }

  /**
   * Replaces all $vars
   * @param array $vars
   */
  public function setVars($vars) {
    $this->vars = $vars;
    $this->loadStructure();
  }
  
  /**
   * Any javascript files that need to be loaded with the form
   * @return array
   */
  public function js() {
    $arr = $this->js;
    if (!empty($this->items)) {
      foreach ($this->items as $item)
        $arr = array_merge($arr, $item->js());
    }
    $srcs = [];
    foreach ($arr as $i => $src)
      $srcs[$src] = '<script src="'.$src.'"></script>';
    return implode("", $srcs);
  }

  /**
   * Fetches all values from elements
   * @return array
   */
  public function values() {
    $value = [];
    foreach ($this->items as $item) {
      if ($item->submit_data) {
        $val = $item->value();
        if (is_array($val) && !$item->tree) {
          foreach ($val as $k => $v)
            $value[$k] = $v;
        }
        else {
          $value[$item->name] = $val;
        }
      }
    }
    return $value;
  }

  /**
   * Validates the form
   * @return bool
   */
  public function isValidated() {
    if (!empty($this->errors))
      return false;
    if (!$this->verifyToken()) {
      $this->setError(t("Form token expired, please reload the page and try again."));
      return false;
    }
    if (!$this->verifySpamfilter()) {
      $this->setError(t("Spamfilter activated, please reload the page and try again."));
      return false;
    }
    foreach ($this->items as $name => $item) {
      if (!$item->validated())
        return false;
    }
    if (!$this->validate($this->values()))
      return false;
    return true;
  }

  /**
   * Custom validation
   * @param  array $values
   * @return bool
   */
  public function validate($values = []) {
    return true;
  }

  /**
   * Checks if the form is submitted
   * @param  bool $validate If true, validates the form first
   * @return bool
   */
  public function isSubmitted($validate = true) {
    return isset($_POST["form_".$this->name]) && (!$validate || $this->isValidated());
  }

  /**
   * Sets an error message in $errors
   * @param string $msg  Error message
   * @param string $name Name of the element
   */
  public function setError($msg, $name = null) {
    if ($name) {
      $arr = explode("[", str_replace("]", "", $name));
      $n = count($arr);
      $item = $this;
      for ($i=0; $i<$n; $i++) {
        if (!isset($item->items[$arr[$i]]))
          return;
        $item = $item->items[$arr[$i]];
      }
      $item->setError($msg, $name);
    }
    else {
      $this->errors[] = $msg;
    }
  }

  /**
   * Getter for $errors
   * @return array
   */
  public function getErrors() {
    return $this->errors;
  }

  /**
   * The main structure of the form
   *
   * This method should be extended by the specific form
   * <code>
   * return [
   *   "name" => "my_form",
   *   "items" => [
   *     "title" => [
   *       "type" => "text",
   *       "label" => "Title",
   *       "filter" => ["strip_tags", "trim"],
   *       "required" => true,
   *       "value" => "Some default title"",
   *     ],
   *     "actions" => $this->defaultActions()
   *   ]
   * ];
   * </code>
   *
   * @see  loadStructure
   * @return array
   */
  protected function structure() {
    return [
      "name" => "default_form",
      "attributes" => [
        "method" => "POST",
        "action" => "",
        "class" => "form",
      ],
    ];
  }

  /**
   * Loads the form structure
   * @see structure
   */
  protected function loadStructure() {
    $args = func_get_args();
    if (empty($args))
      $structure = $this->structure();
    else
      $structure = call_user_func_array([$this, "structure"], func_get_args());
    if (empty($structure["name"]))
      throw new Exception("No name given for form");
    if (!empty($structure["attributes"])) {
      foreach ($structure["attributes"] as $key => $val)
        $this->attributes[$key] = $val;
      unset($structure["attributes"]);
    }
    $items = $structure["items"];
    unset($structure["items"]);
    foreach ($structure as $key => $val)
      $this->{$key} = $val;
    foreach ($items as $name => $item)
      $this->loadItem($name, $item);
    if ($this->hasFileItem())
      $this->attributes["enctype"] = "multipart/form-data";
    // Add no-input spam filter protection
    // Check for javascript, cookies, and time of submission
    if ($this->spamfilter) {
      $id = "sf_".substr($this->token(), 3, 8);
      $item = [
        "type" => "markup",
        "value" => '
          <input type="hidden" id="'.$id.'" name="_sf" value="'.REQUEST_TIME.'">
          <script>document.getElementById("'.$id.'").value+= "_sfjs";</script>',
      ];
      $_COOKIE["sf"] = 1;
      $this->loadItem("_sf", $item);
    }
  }

  /**
   * Loads a form item
   * @see    \FormItem
   * @param  string $name
   * @param  array $item
   */
  protected function loadItem($name, $item) {
    if (empty($item["type"]))
      throw new Exception("No type given for form item ".$name);
    $item["name"] = $name;
    $item["submitted"] = $this->isSubmitted(false);
    $item["form_name"] = $this->name;
    $a = explode("_", $item["type"]);
    $cname = "";
    foreach ($a as $b)
      $cname.= ucwords($b);
    $cname.= "_FormItem";
    $class = $this->newClass($cname, $item);
    if (!$class) 
      $class = $this->newClass("FormItem", $item);
    $this->items[$name] = $class;
  }

  /**
   * Returns structure for common actions
   *
   * By default, it returns a "Save" and "Cancel" button
   *
   * @param  string $submit Text in submit button
   * @param  string $cancel Text in cancel button
   * @return array
   */
  protected function defaultActions($submit = null, $cancel = null) {
    if (!$submit)
      $submit = t("Save");
    if (!$cancel)
      $cancel = t("Cancel");
    return [
      "type" => "actions",
      "items" => [
        "submit" => [
          "type" => "submit",
          "value" => $submit,
        ],
        "cancel" => [
          "type" => "button",
          "value" => $cancel,
          "attributes" => [
            "onclick" => "window.history.go(-1)",
          ],
        ],
      ],
    ];
  }

  /**
   * Check if the form has a file item
   * @return bool
   */
  protected function hasFileItem() {
    if (!empty($this->items)) {
      foreach ($this->items as $item) {
        if ($item->hasFileItem())
          return true;
      }
    }
    return false;
  }

  /**
   * Verify user form token
   * @return bool
   */
  protected function verifyToken() {
    return $_POST["form_token"] === $this->token();
  }

  /**
   * Verify spam filter
   * @return bool
   */
  protected function verifySpamfilter() {
    if (!$this->spamfilter)
      return true;
    if (empty($_POST["_sf"]) || empty($_COOKIE["sf"]))
      return false;
    if (!preg_match("/^([0-9]+)\_sfjs$/", $_POST["_sf"], $m))
      return false;
    if (REQUEST_TIME - $m[0] < 2)
      return false;
    return true;
  }

  /**
   * Retrieves user form token
   * @return string
   */
  protected function token() {
    if (!isset($_SESSION["form_token"]))
      $_SESSION["form_token"] = substr(hash("sha512", rand(1,1000).microtime(true)."qfformtoken"), 4, 32);
    return $_SESSION["form_token"];
  }

  /**
   * Get form attributes
   * @return arr
   */
  protected  function getAttributes() {
    $attr = [];
    foreach ($this->attributes as $key => $val)
      $attr[$key] = $val;
    $class = cssClass("form-".$this->name);
    $attr["name"] = $this->name;
    if (empty($attr["class"]))
      $attr["class"] = $class;
    else
      $attr["class"].= " ".$class;
    if ($this->error_focus)
      $attr["error-focus"] = "1";
    if ($this->ajax)
      $attr["onsubmit"] = "return formAjaxSubmit(this);";
    return $attr;
  }

  /**
   * Renders an array of attributes to a string
   * @param  array $attributes
   * @return string
   */
  protected function attributes($attributes = null) {
    if (!$attributes)
      $attributes = $this->getAttributes();
    $attr = "";
    foreach ($attributes as $key => $val)
      $attr.= $key.'="'.$val.'" ';
    $attr = substr($attr, 0, -1);
    return $attr;
  }

  /**
   * Path to template for rendering
   * @return string
   */
  protected function templatePath() {
    return filePath("template/form/form.php");
  }

  /**
   * Renders all elements
   * @return string
   */
  protected function renderItems() {
    $items = [];
    foreach ($this->items as $name => $item)
      $items[] = $item->render($name);
    return $items;
  }

};