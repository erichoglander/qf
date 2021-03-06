<?php
/**
 * Contains the content entity
 */
/**
 * Content entity
 *
 * Used to create configurable and editable content blocks 
 * without manual database interaction.
 * @author Eric Höglander
 */
class Content_Entity_Core extends l10n_Entity {

  /**
   * Acl object
   * @var \Acl_Core
   */
  protected $Acl;
  
  /**
   * User entity
   * @var \User_Entity_Core
   */
  protected $User;


  /**
   * Render the content block
   * @param  string $lang If empty, use current site language
   * @return string
   */
  public function render($lang = null) {
    if (!$lang)
      $lang = LANG;
    if ($this->get("config")["l10n"])
      $data = $this->translate("data", $lang);
    else
      $data = $this->get("data");
    $html = '<div class="content-entity content-entity-'.$this->id().'">';
    if ($this->editAccess())
      $html.= $this->editButton();
    $html.= '<div class="inner">';
    if (!empty($this->get("config")["fields"])) {
      foreach ($this->get("config")["fields"] as $i => $field) {
        $html.= '<div class="field field-'.$field["type"].' field-'.($i+1).'">';
        $html.= $this->renderField($field["type"], (isset($data[$i]) ? $data[$i] : null));
        $html.= '</div>';
      }
    }
    $html.= '</div></div>';
    return $html;
  }

  /**
   * The quick edit button
   * @return string
   */
  public function editButton() {
    return 
      '<a class="edit-btn" href="'.url("content/edit/".$this->id(), true).'">'.
        FontAwesome\Icon("pencil").
      '</a>';
  }
  
  /**
   * Whether or not the content can be localized
   * @return bool
   */
  public function l10n() {
    return !empty($this->get("config")["l10n"]);
  }

  /**
   * Get field configurations
   * @return array
   */
  public function fields() {
    $arr = [];
    foreach ($this->get("config")["fields"] as $field) {
      $arr[] = [
        "type" => $field["type"],
        "title" => $field["title"],
        "description" => $field["description"],
      ];
    }
    return $arr;
  }

  /**
   * Render one field
   * @param  string $field
   * @param  string $value
   * @return string
   */
  public function renderField($field, $value) {
    if ($field == "text") {
      return xss($value);
    }
    if ($field == "textarea") {
      return nl2br(xss($value));
    }
    if ($field == "image") {
      $File = $this->getEntity("File", $value);
      if (!$File->id())
        return null;
      return '<img src="'.$File->url().'" alt="">';
    }
    if ($field == "editor" || $field == "tinymce") {
      return $value;
    }
    if ($field == "link") {
      if (strlen($value)) {
        if ($value[0] == "/")
          $value = substr($value, 1);
        if (preg_match("/^[^\/\.]+\.[^\/\.]+/", $value))
          $value = "http://".$value;
        else
          $value = url($value);
        return '<a href="'.$value.'"></a>';
      }
    }
    return null;
  }
  
  /**
   * Get field form item structure
   * @param  array $field
   * @param  string $value
   * @return array
   */
  public function fieldFormItem($field, $value = null) {
    $item = [
      "type" => $field["type"],
      "label" => $field["title"],
      "value" => $value,
    ];
    if (in_array($field["type"], ["text", "textarea", "link"]))
      $item["value"] = xss($value);
    if ($field["type"] == "link")
      $item["type"] = "text";
    return $item;
  }
  
  /**
   * Save entity
   */
  public function save() {
    // Set files to permanent status
    if ($this->get("config")["l10n"]) {
      foreach ($this->translations() as $lang => $Content) {
        $data = $Content->get("data");
        foreach ($Content->fields() as $i => $field) {
          if ($field["type"] == "image" && !empty($data[$i])) {
            $File = $this->getEntity("File", $data[$i]);
            if ($File->id() && $File->get("status") == 0) {
              $File->set("status", 1);
              $File->save();
            }
          }
        }
      }
    }
    $data = $this->get("data");
    foreach ($this->fields() as $i => $field) {
      if ($field["type"] == "image" && !empty($data[$i])) {
        $File = $this->getEntity("File", $data[$i]);
        if ($File->id() && $File->get("status") == 0) {
          $File->set("status", 1);
          $File->save();
        }
      }
    }
    return parent::save();
  }
  
  /**
   * Delete entity
   */
  public function delete($change_sid = true) {
    // Delete all files
    if ($this->get("config")["l10n"]) {
      foreach ($this->translations() as $lang => $Content) {
        $data = $Content->get("data");
        foreach ($Content->fields() as $i => $field) {
          if ($field["type"] == "image" && !empty($data[$i])) {
            $File = $this->getEntity("File", $data[$i]);
            if ($File->id())
              $File->delete();
          }
        }
      }
    }
    $data = $this->get("data");
    foreach ($this->fields() as $i => $field) {
      if ($field["type"] == "image" && !empty($data[$i])) {
        $File = $this->getEntity("File", $data[$i]);
        if ($File->id())
          $File->delete();
      }
    }
    return parent::delete($change_sid);
  }


  /**
   * If the current user has permission to edit this piece of content
   * @return bool
   */
  protected function editAccess() {
    if (!$this->Acl) {
      $this->Acl = newClass("Acl", $this->Db);
      $this->User = newClass("User_Entity", $this->Db, (!empty($_SESSION["user_id"]) ? $_SESSION["user_id"] : null));
    }
    return $this->Acl->access($this->User, ["contentAdmin", "contentEdit"]);
  }
  
  /**
   * Database schema
   * @return array
   */
  protected function schema() {
    $schema = parent::schema();
    $schema["table"] = "content";
    $schema["fields"]["title"] = [
      "type" => "varchar",
    ];
    $schema["fields"]["data"] = [
      "type" => "blob",
      "serialize" => true,
    ];
    $schema["fields"]["config"] = [
      "type" => "blob",
      "serialize" => true,
    ];
    return $schema;
  }

}