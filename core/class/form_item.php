<?php
/**
 * Contains the form item class
 */

/**
 * Form item class
 *
 * @see    \Form 
 * @see    \Form::structure
 * @author Eric Höglander
 */
class FormItem {
  
  /**
   * Name of the element
   * @var string
   */
  public $name;

  /**
   * Child elements
   * @var array
   */
  public $items;

  /**
   * If element can have multiple inputs
   * @var bool
   */
  public $multiple;
  
  /**
   * If a new item should be displayed even if there are existing items
   * @see $multiple
   * @var bool
   */
  public $multiple_new;
  
  /**
   * If a new item should be displayed if there are none yet
   * @see $multiple
   * @var bool
   */
  public $multiple_new_empty = true;

  /**
   * If parent element can have multiple inputs
   * @see $multiple
   * @var bool
   */
  public $parent_multiple;

  /**
   * If false, data will not be collected
   * @see \Form::values
   * @var boolean
   */
  public $submit_data = true;

  /**
   * If child data should be nested
   * @var bool
   */
  public $tree = true;

  /**
   * Element type
   * @var string
   */
  public $type;

  /**
   * Element label
   * @var string
   */
  public $label;

  /**
   * Element item label
   * @var string
   */
  public $item_label;

  /**
   * Element description
   * @var string
   */
  public $description;

  /**
   * Element icon
   * @see \FontAwesome\Icon
   * @var string
   */
  public $icon;

  /**
   * If true, the element is disabled and returns the predefined value
   * @var bool
   */
  public $disabled = false;

  /**
   * If true, renders tools to sort elements
   * @see $multiple
   * @var bool
   */
  public $sortable = false;

  /**
   * Text of "add item"-button
   * @see $multiple
   * @var string
   */
  public $add_button = "Add item";

  /**
   * Text of "delete item"-button
   * @see $multiple
   * @var string
   */
  public $delete_button = "Remove";
  
  /**
   * Javascript function to call after an item has been dynamically added
   * @var string
   */
  public $add_callback;
  
  /**
   * Javascript function to call after an item has been dynamically removed
   * @var string
   */
  public $delete_callback;

  /**
   * If input is required
   * @var bool
   */
  public $required;

  /**
   * If element should be focused on load
   * @var bool
   */
  public $focus;

  /**
   * HTML attributes of the input element
   * @var array
   */
  public $attributes = [];

  /**
   * HTML attributes of the item element
   * @var array
   */
  public $item_attributes = [];

  /**
   * Default value
   * @var mixed
   */
  public $value;

  /**
   * Possible value options
   * 
   * Used for elements such as checkboxes, radios, and select
   * @var array
   */
  public $options;

  /**
   * The empty option text
   * @see $options
   * @var string
   */
  public $empty_option;
  
  /**
   * Whether to validate a multiple choice value
   * @see $options
   * @var bool
   */
  public $validate_option = true;

  /**
   * What filter(s) should be applied to the value
   * @see \Io_Core
   * @var string|array
   */
  public $filter = "trim";

  /**
   * What validation(s) should be used
   * @see \Io_Core
   * @var string|array
   */
  public $validation;

  /**
   * Any validation error
   * @var string
   */
  public $validation_error;

  /**
   * Custom template path
   * @var string
   */
  public $template;

  /**
   * If form has been submitted
   * @see \Form\loadItem
   * @var boolean
   */
  public $submitted = false;

  /**
   * Will be rendered before the item element
   * @var string
   */
  public $prefix;

  /**
   * Will be rendered after the item element
   * @var string
   */
  public $suffix;

  /**
   * Will be rendered before the input element
   * @var string
   */
  public $input_prefix;

  /**
   * Will be rendered after the input element
   * @var string
   */
  public $input_suffix;

  /**
   * Any extra item class
   * @var string
   */
  public $item_class;
  
  /**
   * Any extra item class to wrapper of a 'multiple' item
   * @var string
   */
  public $wrap_class;

  /**
   * The element structure
   * @see loadStructure
   * @var array
   */
  protected $structure = [];

  /**
   * Structure used for children if $multiple is set
   * @see $mutiple
   * @see loadStructure
   * @var array
   */
  protected $item_structure = [];

  /**
   * Name of parent element
   * @var string
   */
  protected $parent_name;

  /**
   * Element error
   * @var string
   */
  protected $error;

  /**
   * Database object
   * @var \Db_Core
   */
  protected $Db;

  /**
   * Io object
   * @var \Io_Core
   */
  protected $Io;


  /**
   * Contructor
   * @param \Db_Core $Db
   * @param \Io_Core $Io
   * @param array    $structure
   */
  public function __construct($Db, $Io, $structure) {
    $this->Db = $Db;
    $this->Io = $Io;
    $this->empty_option = t("- Choose -");
    $this->loadStructure($structure);
  }

  /**
   * Setter for $error
   * @param string $msg
   */
  public function setError($msg) {
    $this->error = $msg;
  }

  /**
   * Getter for $error
   * @return string
   */
  public function getError() {
    return $this->error;
  }
  
  /**
   * Check if any child item contains an error
   * @return bool
   */
  public function childError() {
    if ($this->items !== null) {
      foreach ($this->items as $item) {
        if ($item->getError() || $item->childError())
          return true;
      }
    }
    return false;
  }

  /**
   * Returns element value
   * @see    $filter
   * @see    filter
   * @param  bool $filter whether or not to apply filter
   * @return mixed
   */
  public function value($filter = true) {
    if (!$this->submitted)
      return $this->value;
    if ($this->items !== null) {
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
    }
    else {
      $value = $this->itemValue();
      if ($filter && $this->filter && $this->options === null && !$this->emptyValue($value))
        $value = $this->filter($value, $this->filter);
    }
    return $value;
  }

  /**
   * If the element or children has any input
   * @see    value
   * @see    emptyValue
   * @return bool
   */
  public function hasValue() {
    if ($this->items === null) {
      return !$this->emptyValue($this->value());
    }
    else {
      foreach ($this->items as $item) 
        if ($item->hasValue())
          return true;
    }
    return false;
  }

  /**
   * If element or children has a file input
   * @return bool
   */
  public function hasFileItem() {
    if (!empty($this->items)) {
      foreach ($this->items as $item) {
        if ($item->hasFileItem())
          return true;
      }
    }
    return false;
  }

  /**
   * Validates the element and it's children
   * @see    validate
   * @param  bool $req If false, skips "required" checks
   * @return true
   */
  public function validated($req = true) {
    if (!$this->submitted)
      return false;
    if ($this->items !== null) {
      if (!$this->hasValue() && !$this->required)
        $req = false;
      foreach ($this->items as $item)
        if (!$item->validated($req))
          return false;
    }
    else {
      $value = $this->value(false);
      if ($req && $this->required && !$this->hasValue()) {
        $this->setError(t("Field is required"));
        return false;
      }
      if ($this->validation && !$this->emptyValue($value) && !$this->validate($value, $this->validation))
        return false;
      if ($this->options !== null && $this->validate_option) {
        if (!is_array($value))
          $value = [$value];
        foreach ($value as $val) {
          if ($val !== null && (is_array($val) || !array_key_exists($val, $this->options()))) {
            $this->setError(t("Invalid option"));
            return false;
          }
        }
      }
    }
    return true;
  }

  /**
   * Renders the element
   * @return string
   */
  public function render() {
    $path = $this->templateItemPath();
    $vars = [
      "name" => $this->name,
      "input_name" => $this->inputName(),
      "label" => $this->label,
      "description" => $this->description,
      "prefix"=> $this->prefix,
      "suffix" => $this->suffix,
      "input_prefix" => $this->input_prefix,
      "input_suffix" => $this->input_suffix,
      "attributes" => $this->itemAttributes(),
      "items" => $this->renderItems(),
      "input" => $this->renderInput(),
      "add_button" => $this->renderAddButton(),
      "delete_button" => $this->renderDeleteButton(),
      "icon" => $this->icon,
      "sortable" => $this->sortable,
      "error" => $this->getError(),
      "multiple" => $this->multiple,
    ];
    if (is_callable([$this, "preRender"]))
      $this->preRender($vars);
    return renderTemplate($path, $vars);
  }
  
  /**
   * Renders certain items only
   * @param  array  $items
   * @return string
   */
  public function renderPartly($items) {
    $html = "";
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
   * Loads the element structure
   * @see    structure
   * @see    \Form\loadItem
   * @param  array $structure
   */
  protected function loadStructure($structure) {
    if (is_callable([$this, "loadDefault"]))
      $this->loadDefault();
    if (is_callable([$this, "preStructure"]))
      $this->preStructure($structure);
    $this->structure = $structure;
    if (!empty($structure["attributes"])) {
      foreach ($structure["attributes"] as $key => $val)
        $this->attributes[$key] = $val;
    }
    $properties = $structure;
    unset($properties["attributes"]);
    unset($properties["items"]);
    if (!empty($properties["multiple"]))
      unset($properties["sortable"]);
    foreach ($properties as $key => $val)
      $this->{$key} = $val;
    if ($this->multiple) {
      $value = $this->value();
      if (empty($value) && $this->multiple_new_empty || $this->multiple_new && !$this->submitted)
        $value[] = null;
      $this->items = [];
      $st = $structure;
      $st["parent_name"] = $this->inputName();
      $st["multiple"] = false;
      $st["parent_multiple"] = true;
      $item_label = (!empty($st["item_label"]) ? $st["item_label"] : null);
      unset($st["label"]);
      unset($st["description"]);
      unset($st["prefix"]);
      unset($st["suffix"]);
      foreach ($value as $i => $val) {
        if (!empty($structure["value"]) && array_key_exists($i, $structure["value"]))
          $st["value"] = $structure["value"][$i];
        else
          unset($st["value"]);
        if (!empty($structure["labels"]) && array_key_exists($i, $structure["labels"]))
          $st["label"] = $structure["labels"][$i];
        else
          $st["label"] = $item_label;
        if (!empty($structure["placeholders"]) && array_key_exists($i, $structure["placeholders"])) {
          if (is_array($structure["placeholders"][$i]))
            $st["placeholders"] = $structure["placeholders"][$i];
          else
            $st["attributes"]["placeholder"] = $structure["placeholders"][$i];
        }
        unset($st["focus"]);
        $this->loadItem($i, $st);
      }
      unset($st["filter"]);
      unset($st["validation"]);
      unset($st["submitted"]);
      unset($st["value"]);
      $st["label"] = $item_label;
      $st["form_item_class"] = get_class($this);
      $this->item_structure = $st;
    }
    else {
      if (isset($structure["items"])) {
        $this->items = [];
        foreach ($structure["items"] as $name => $item) {
          if (!empty($structure["value"]) && array_key_exists($name, $structure["value"]))
            $item["value"] = $structure["value"][$name];
          if (!empty($structure["placeholders"]) && array_key_exists($name, $structure["placeholders"])) {
            if (is_array($structure["placeholders"][$name]))
              $item["placeholders"] = $structure["placeholders"][$name];
            else
              $item["attributes"]["placeholder"] = $structure["placeholders"][$name];
          }
          $item["parent_name"] = $this->inputName();
          $this->loadItem($name, $item);
        }
      }
    }
  }

  /**
   * Loads a child element
   * @see    loadStructure
   * @param  string $name
   * @param  array $item
   */
  protected function loadItem($name, $item) {
    if (empty($item["type"]))
      throw new Exception("No type given for form item ".$name);
    $item["name"] = $name;
    $item["submitted"] = $this->submitted;
    $item["form_name"] = $this->form_name;
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
   * Applies a filter to a value
   * @see    \Io_Core::filter
   * @param  mixed $value
   * @param  string|array $filter
   * @return mixed
   */
  protected function filter($value, $filter) {
    return $this->Io->filter($value, $filter);
  }

  /**
   * Validates a value and sets an error if it fails
   * @see    \Io_Core::validate
   * @param  mixed $value
   * @param  string|array $validation
   * @return bool
   */
  protected function validate($value, $validation) {
    if (!$this->Io->validate($value, $validation)) {
      if ($this->validation_error)
        $this->setError($this->validation_error);
      else
        $this->setError($this->Io->getError());
      return false;
    }
    return true;
  }

  /**
   * Checks if the value is "empty"
   * @param  mixed $val
   * @return bool
   */
  protected function emptyValue($val) {
    $is_arr = is_array($val);
    return ($is_arr && empty($val) || !$is_arr && strlen($val) === 0);
  }

  /**
   * Fetches the value from $_POST
   * @return mixed
   */
  protected function postValue() {
    if ($this->disabled)
      return $this->value;
    $data = $_POST;
    if ($this->parent_name) {
      $arr = explode("[", str_replace("]", "", $this->parent_name));
      foreach ($arr as $f) {
        if (!array_key_exists($f, $data))
          $data = [];
        else
          $data = $data[$f];
      }
    }
    return (isset($data[$this->name]) ? $data[$this->name] : null);
  }

  /**
   * Fetches the specific item value
   * @see    postValue
   * @return mixed
   */
  protected function itemValue() {
    return $this->postValue();
  }

  /**
   * Combines options into one array
   * @see    $options
   * @see    $empty_option
   * @return array
   */
  protected function options() {
    if ($this->options === null)
      return null;
    $options = $this->options;
    if ($this->empty_option !== null)
      $options = ["" => $this->empty_option]+$options;
    return $options;
  }

  /**
   * Checks if element type can be considered as text
   * @return bool
   */
  protected function isTextfield() {
    $arr = ["text", "textarea", "password", "tel", "url", "email", "search", "number"];
    return in_array($this->inputType(), $arr);
  }

  /**
   * The type to be used for the input element
   * @return string
   */
  protected function inputType() {
    return $this->type;
  }

  /**
   * The css classes to be used for the input element
   * @return string
   */
  protected function inputClass() {
    $class = cssClass("form-".$this->inputType());
    if ($this->isTextfield())
      $class.= " form-textfield";
    return $class;
  }

  /**
   * The name to be used for the input element
   * @see    $parent_name
   * @see    $name
   * @return string
   */
  protected function inputName() {
    if (!$this->tree)
      return $this->parent_name;
    return ($this->parent_name ? $this->parent_name."[".$this->name."]" : $this->name);
  }

  /**
   * The css classes to be used for the item element
   * @see    $item_class
   * @return string
   */
  protected function itemClass() {
    $type = $this->type;
    if ($this->multiple)
      $type = "container";
    $class = "form-item ".cssClass("form-type-".$type)." ".cssClass("form-name-".$this->name);
    if ($this->icon)
      $class.= " form-item-icon";
    if ($this->isTextfield())
      $class.= " form-item-textfield";
    if ($this->required)
      $class.= " form-item-required";
    if ($this->error)
      $class.= " form-item-error";
    if ($this->sortable)
      $class.= " form-item-sortable";
    if ($this->childError())
      $class.= " form-item-child-error";
    if (!$this->multiple && !empty($this->item_class))
      $class.= " ".$this->item_class;
    if ($this->multiple && !empty($this->wrap_class))
      $class.= " ".$this->wrap_class;
    return $class;
  }

  /**
   * Get an array of HTML attributes for the input element
   * @return array
   */
  protected function getAttributes() {
    $attr = [];
    $attr["type"] = $this->inputType();
    $attr["name"] = $this->inputName();
    if ($this->disabled)
      $attr["disabled"] = true;
    foreach ($this->attributes as $key => $val)
      $attr[$key] = $val;
    if (empty($attr["class"]))
      $attr["class"] = $this->inputClass();
    else
      $attr["class"].= " ".$this->inputClass();
    return $attr;
  }

  /**
   * Combines the HTML attributes to a string
   * @see    getAttributes
   * @return string
   */
  protected function attributes() {
    $attributes = $this->getAttributes();
    $attr = [];
    foreach ($attributes as $key => $val) {
      if ($val === true)
        $attr[] = $key;
      else if ($val !== false)
        $attr[] = $key.'="'.$val.'"';
    }
    return implode(" ", $attr);
  }

  /**
   * Get an array of HTML attributes for the item element
   * @return array
   */
  protected function getItemAttributes() {
    $attr = [];
    foreach ($this->item_attributes as $key => $val)
      $attr[$key] = $val;
    if (empty($attr["class"]))
      $attr["class"] = $this->itemClass();
    else
      $attr["class"].= " ".$this->itemClass();
    return $attr;
  }

  /**
   * Combines the HTML attributes to a string
   * @see    getAttributes
   * @return string
   */
  protected function itemAttributes() {
    $attributes = $this->getItemAttributes();
    $attr = [];
    foreach ($attributes as $key => $val) {
      if ($val === true)
        $attr[] = $key;
      else if ($val !== false)
        $attr[] = $key.'="'.$val.'"';
    }
    return implode(" ", $attr);
  }

  /**
   * Path to the item template
   * @return string
   */
  protected function templateItemPath() {
    $prefix = "form_item";
    $d = "__";
    $names = [];
    if ($this->template) {
      $names[] = $prefix.$d.$this->type.$d.$this->template;
      if ($this->type != $this->inputType())
        $names[] = $prefix.$d.$this->inputType().$d.$this->template;
      $names[] = $prefix.$d.$this->template;
    }
    $names[] = $prefix.$d.$this->type;
    $names[] = $prefix;
    foreach ($names as $name) {
      $path = DOC_ROOT."/extend/template/form/".$name.".php";
      if (file_exists($path))
        return $path;
    }
    foreach ($names as $name) {
      $path = DOC_ROOT."/core/template/form/".$name.".php";
      if (file_exists($path))
        return $path;
    }
    return null;
  }

  /**
   * Path to the input template
   * @return string
   */
  protected function templateInputPath() {
    $prefix = "form_input";
    $d = "__";
    $names = [];
    if ($this->template) {
      $names[] = $prefix.$d.$this->type.$d.$this->template;
      if ($this->type != $this->inputType())
        $names[] = $prefix.$d.$this->inputType().$d.$this->template;
      $names[] = $prefix.$d.$this->template;
    }
    $names[] = $prefix.$d.$this->type;
    if ($this->type != $this->inputType())
      $names[] = $prefix.$d.$this->inputType();
    $names[] = $prefix;
    foreach ($names as $name) {
      $path = DOC_ROOT."/extend/template/form/".$name.".php";
      if (file_exists($path))
        return $path;
    }
    foreach ($names as $name) {
      $path = DOC_ROOT."/core/template/form/".$name.".php";
      if (file_exists($path))
        return $path;
    }
    return null;
  }

  /**
   * Render child elements
   * @return string
   */
  protected function renderItems() {
    if ($this->items === null)
      return null;
    $items = [];
    foreach ($this->items as $item)
      $items[$item->name] = $item->render();
    return $items;
  }

  /**
   * Render input element
   * @return string
   */
  protected function renderInput() {
    if ($this->items !== null)
      return null;
    $path = $this->templateInputPath();
    $vars = [
      "attributes" => $this->attributes(),
      "name" => $this->inputName(),
      "focus" => $this->focus,
      "empty_option" => $this->empty_option,
      "options" => $this->options(),
      "value" => $this->value(false),
    ];
    if (is_callable([$this, "preRenderInput"]))
      $this->preRenderInput($vars);
    return renderTemplate($path, $vars);
  }

  /**
   * Render "Add item"-button
   * @see    $add_button
   * @return string
   */
  protected function renderAddButton() {
    if (!$this->multiple || !$this->add_button) 
      return null;
    $data = $this->item_structure;
    $json = htmlspecialchars(json_encode($data), ENT_QUOTES);
    $last_item = count($this->items)-1;
    return '<div class="form-button form-add-button btn" last_item="'.$last_item.'" onclick="formAddButton(this, '.$json.')">'.$this->add_button.'</div>';
  }

  /**
   * Render "Delete item"-button
   * @see    $delete_button
   * @return string
   */
  protected function renderDeleteButton() {
    if (!$this->parent_multiple || !$this->delete_button)
      return null;
    $callback = ($this->delete_callback ? "'".$this->delete_callback."'" : "null");
    $add = ($this->multiple_new_empty ? "true" : "false");
    return '<div class="form-button form-delete-button btn btn-danger" onclick="formDeleteButton(this, '.$callback .', '.$add.')">'.$this->delete_button.'</div>';
  }

};