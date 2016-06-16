<?php
class File_FormItem_Core extends FormItem {
  
  public $remove = false;
  public $uploaded = null;
  public $upload_button, $remove_button;
  public $file_folder, $file_extensions, $file_dir, $file_max_size;
  public $file_drop, $drop_markup;
  public $file_blacklist = ["php", "phtml", ".htaccess"];
  public $upload_callback, $remove_callback;
  public $preview_template;
  public $filter = "uint";
  public $multiple_new = true;


  public function hasFileItem() {
    return true;
  }


  protected function loadDefault() {
    $this->upload_button = t("Browse...");
    $this->remove_button = t("Remove file");
    $this->drop_markup = t("Drag and drop file here");
    $this->file_drop = false;
    $this->file_dir = "public";
  }

  protected function isFileUpload() {
    $file = $this->getFileArray();
    return !empty($file["tmp_name"]) && $this->uploaded === null;
  }

  protected function inputType() {
    return "file";
  }

  protected function itemValue() {
    if (!$this->submitted)
      return $this->value;
    if ($this->isFileUpload()) {
      return $this->uploadFile();
    }
    else if ($this->uploaded !== null) {
      return $this->uploaded;
    }
    else {
      $data = $this->postValue();
      if ($this->multiple) 
        return $data;
      else if (!empty($data["id"]))
        return (int) $data["id"];
      else
        return null;
    }
  }
  
  protected function fileIcon($ext) {
    $icons = [
      "file-image-o" => ["jpg", "jpeg", "gif", "png", "tif", "tiff", "bmp"],
      "file-text-o" => ["txt"],
      "file-audio-o" => [
        "3gp", "act", "aiff", "aac", "amr", "au", "awb", "dct", "dss", "dvf", "flac", "gsm", "iklax", "ivs", "m4a", "m4p",
        "mmf", "mp3", "mpc", "msv", "ogg", "oga", "opus", "ra", "rm", "raw", "sln", "tta", "vox", "wav", "wave", "wma", "wv",
      ],
      "file-video-o" => [
        "webm", "mkv", "flv", "vob", "ogv", "drc", "mng", "avi", "mov", "qt", "wmv", "yuv", "rmvb", "asf", "mp4", "m4p", "m4v",
        "mpg", "mp2", "mpeg", "mpe", "mpv", "m2v", "svi", "3g2", "mxf", "roq", "nsv",
      ],
      "file-archive-o" => ["tar", "gz", "zip", "7z", "rar"],
      "file-pdf-o" => ["pdf"],
      "file-code-o" => ["php", "xml", "html", "htm", "xhtml", "jsp", "py", "js", "css", "c", "cpp", "h", "hpp", "cc", "hh"],
      "file-powerpoint-o" => ["ppt", "pot", "pps", "pptx", "pptm", "potx", "potm", "ppam", "ppsx", "ppsm", "sldx", "sldm"],
      "file-excel-o" => ["xls", "xlt", "xlm", "xlsx", "xlsm", "xltx", "xltm", "xlsb", "xla", "xlam", "xll", "xlw"],
      "file-word-o" => ["doc", "dot", "docx", "docm", "dotx", "dotm", "docb"],
    ];
    foreach ($icons as $icon => $exts) {
      if (in_array($ext, $exts))
        return $icon;
    }
    return "file-o";
  }

  protected function validate($values = null) {
    if ($this->isFileUpload()) {
      $file = $this->getFileArray();
      $opt = [
        "whitelist" => $this->file_extensions,
        "blacklist" => $this->file_blacklist,
        "dir" => $this->file_dir,
        "folder" => $this->file_folder,
        "max_size" => $this->file_max_size,
      ];
      try {
        $this->getModel("File")->validateUpload($file, $opt);
      }
      catch (Exception $e) {
        $this->setError($e->getMessage());
        return false;
      }
      if (is_callable([$this, "fileUploadValidate"]) && !$this->fileUploadValidate($file)) {
        if (empty($this->getError()))
          $this->setError(t("Validation failed"));
        return false;
      }
    }
    return true;
  }

  protected function getFileArray() {
    $keys = explode("[", str_replace("]", "", $this->inputName()));
    $keys[] = "file";
    $file = $_FILES[$keys[0]];
    if (empty($file))
      return null;
    array_shift($keys);
    foreach (array_keys($file) as $field)
      $file[$field] = $this->nestedValue($file[$field], $keys);
    return $file;
  }

  protected function uploadFile() {
    if (!$this->validate())
      return $this->value;
    $file = $this->getFileArray();
    $opt = [
      "dir" => $this->file_dir,
      "folder" => $this->file_folder,
    ];
    try {
      $File = $this->getModel("File")->upload($file, $opt);
    }
    catch (Exception $e) {
      $this->setError($e->getMessage());
      return $this->value;
    }
    $this->uploaded = $File->id();
    return $File->id();
  }

  protected function nestedValue($arr, $keys) {
    while (!empty($keys)) {
      $arr = $arr[$keys[0]];
      array_shift($keys);
    }
    return $arr;
  }

  protected function templatePreviewPath() {
    $prefix = "form_file_preview";
    $d = "__";
    $names = [];
    if ($this->preview_template)
      $names[] = $prefix."__".$this->preview_template;
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

  protected function preview() {
    $file_id = $this->value();
    if (!$file_id)
      return null;
    $File = newClass("File_Entity", $this->Db, $file_id);
    if (!file_exists($File->path()))
      return null;
    $path = $this->templatePreviewPath();
    $vars = [
      "size" => filesize($File->path()),
      "name" => $File->get("name"),
      "url" => $File->url(),
      "path" => $File->path(),
      "filename" => $File->filename(),
      "icon" => $this->fileIcon($File->get("extension")),
      "extension" => $File->get("extension"),
    ];
    if (is_callable([$this, "preRenderPreview"]))
      $this->preRenderPreview($vars);
    return renderTemplate($path, $vars);
  }

  protected function uploadCallback() {
    return ($this->upload_callback ? $this->upload_callback : "null");
  }
  protected function removeCallback() {
    return ($this->remove_callback ? $this->remove_callback : "null");
  }
  protected function parentMultiple() {
    return ($this->parent_multiple ? $this->parent_multiple : "null");
  }

  protected function fileToken() {
    return $this->form_name."--".str_replace("[", "-", str_replace("]", "", $this->inputName()));
  }

  protected function fileSession() {
    if (empty($_SESSION["file_upload"]))
      $_SESSION["file_upload"] = [];
    $token = $this->fileToken();
    $info = $this->structure;
    $info["form_item_class"] = get_class($this);
    $_SESSION["file_upload"][$token] = $info;
    return $token;
  }

  protected function preRenderInput(&$vars) {
    $vars["upload_button"] = $this->upload_button;
    $vars["remove_button"] = $this->remove_button;
    $vars["remove_callback"] = $this->removeCallback();
    $vars["file_extensions"] = $this->file_extensions;
    $vars["file_max_size"] = $this->file_max_size;
    $vars["preview"] = $this->preview();
    $vars["token"] = $this->fileSession();
    $vars["file_remove"] = "formFileRemove(this, '".$this->inputName()."', ".$this->parentMultiple().", ".$this->removeCallback().")";
    $vars["file_drop"] = ($this->file_drop ? "formFileDrop(this, event, ".$this->parentMultiple().", ".$this->uploadCallback().")" : null);
    $vars["drop_markup"] = $this->drop_markup;
  }

  protected function getAttributes() {
    $attr = parent::getAttributes();
    $attr["name"].= "[file]";
    $attr["onchange"] = "formFileUpload(this, ".$this->parentMultiple().", ".$this->uploadCallback().")";
    return $attr;
  }

  protected function itemClass() {
    $class = parent::itemClass();
    if (strpos($class, "form-type-file") === false)
      $class.= " form-type-file";
    if ($this->value())
      $class.= " has-value";
    return $class;
  }

}