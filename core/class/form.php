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
   * Constructor
   * @param array         $vars
   */
  public function construct($vars = []) {
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
    ];
    return renderTemplate($path, $vars);
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
      $this->setError(t("Form token expired, please try to submit the form again."));
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
    $class = newClass($cname, $this->Db, $this->Io, $item);
    if (!$class)
      $class = new FormItem($this->Db, $this->Io, $item);
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