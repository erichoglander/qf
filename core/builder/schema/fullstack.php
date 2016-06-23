<?php
require_once("stack.php");

class Fullstack extends Stack {
  
  public static function input(&$args) {
    $input = parent::input($args);
    if ($input)
      return $input;
    
    global $Db;
    
    // Standard input
    $inputs = [
      "update_file" => [
        "prompt" => "Create update file",
        "type" => "bool",
      ],
      "l10n" => [
        "prompt" => "Multilingual",
        "default" => "n",
        "type" => "bool",
      ],
      "type_default" => [
        "prompt" => "Add default entity fields",
        "default" => "y",
        "type" => "bool",
      ],
    ];
    foreach ($inputs as $key => $input) {
      if (empty($args[$key])) {
        $input["key"] = $key;
        return $input;
      }
    }
    
    // Table name
    if (empty($args["table"]))
      return static::arg("table", "Table name", Builder::camelToSnake($args["name"]));
    
    // Check table
    if (empty($args["table_check"]) && $Db->numRows("SHOW TABLES LIKE :table", [":table" => $args["table"]])) {
      print "Table already exists\n";
      return $inputs["table"];
    }
    $args["table_check"] = true;
    
    // Parse fields
    if (!isset($args["fields"])) {
      print "Enter the fields for the new entity type.\n";
      print "Type :undo to start over with current field.\n";
      $args["fields"] = [];
      $args["fields_n"] = 0;
      $field = [];
    }
    else {
      $n = $args["fields_n"];
      if (!isset($args["fields"][$n]))
        $args["fields"][$n] = [];
      $field = $args["fields"][$n];
    }
    foreach ($args as $key => $val) {
      if (!is_array($val) && strpos($key, "field_") === 0) {
        $f = substr($key, 6);
        unset($args[$key]);
        if ($val == ":undo") {
          unset($args["fields"][$n]);
        }
        else {
          if ($val == ":list") {
            print "Field types: ".implode(", ", static::fieldTypes())."\n";
          }
          else {
            $args["fields"][$n][$f] = $val;
          }
          $field = $args["fields"][$n];
        }
        break;
      }
    }
    
    // Next field input
    // Enter field name
    if (!array_key_exists("name", $field)) {
      return static::farg("name", "New field name", "");
    }
    // Field name is empty, so we're done
    else if (empty($field["name"])) {
      unset($args["fields"][$n]);
      return null;
    }
    // Get field type
    else if (empty($field["type"]) || !in_array($field["type"], static::fieldTypes())) {
      return static::farg("type", "Field type (:list)");
    }
    
    // Null
    if (empty($field["null"])) {
      return static::farg("null", "Null", "y", "bool");
    }
    // Index
    if (empty($field["index"])) {
      if ($field["type"] == "file")
        $args["fields"][$n]["index"] = "y";
      else
        return static::farg("index", "Index", "n", "bool");
    }
    // Unsigned
    if ($field["type"] == "file" && empty($field["unsigned"]))
      $field["unsigned"] = "y";
    if (in_array($field["type"], ["int", "tinyint", "double", "float", "decimal"]) && empty($field["unsigned"])) {
      return static::farg("unsigned", "Unsigned", null, "bool");
    }
    // Length
    if (in_array($field["type"], ["varchar", "int", "tinyint", "decimal"]) && empty($field["length"])) {
      return static::farg("length", "Length");
    }
    // Enum values
    if ($field["type"] == "enum" && empty($field["values"])) {
      return static::farg("values", "Values");
    }
    if (!empty($field["values"]) && !is_array($field["values"])) {
      $field["values"] = explode(",", $field["values"]);
      foreach ($field["values"] as $i => $val)
        $field["values"][$i] = trim($val);
      $args["fields"][$n]["values"] = $field["values"];
    }
    // Default value
    if (!array_key_exists("default", $field) ||
       !empty($field["default"]) && $field["type"] == "enum" && !in_array($field["default"], $field["values"])) {
      return static::farg("default", "Default", "");
    }
    // File method name
    if ($field["type"] == "file" && !array_key_exists("file_getter", $field)) {
      return static::farg("file_getter", "Getter method", str_replace("_id", "", $field["name"]));
    }
    if (in_array($field["type"], ["blob", "longblob"]) && empty($field["serialize"])) {
      return static::farg("serialize", "Serialize", "y", "bool");
    }
    
    $args["fields_n"]++;
    return static::input($args);
  }
  
  public static function files(&$args) {
    $files = parent::files($args);
    if ($args["update_file"] == "y") {
      $qs = static::tableQueries($args);
      $files["update"] = [
        "path" => "update/update_".Builder::nextUpdate().".php",
        "content" => static::fileUpdate($qs),
      ];
    }
    return $files;
  }
  
  public static function mods($args) {
    global $Db;
    parent::mods($args);
    $qs = static::tableQueries($args);
    print "Running SQL queries... ";
    foreach ($qs as $q) {
      if (!$Db->query($q)) {
        print "Failed\n";
        return;
      }
    }
    print "OK\n";
    print "Updating version number...";
    Builder::setUpdate(Builder::nextUpdate()-1);
    print "OK\n";
  }
  
  public static function fileUpdate($qs) {
    $file = '<?php
class Update_'.Builder::nextUpdate().' extends Update_Core {
  
  public function execute() {
    $qs = [';
    foreach ($qs as $i => $q) {
      $file.= '
      "'.$q.'",';
    }
    $file.= '
    ];
    return $this->dbQueries($qs);
  }
  
}';
    return $file;
  }
  
  public static function fileEntity($args) {
    extract($args);
    $extend = ($args["l10n"] == "y" ? "l10n_Entity" : "Entity");
    $schema_fields = "";
    $t1 = "\r\n      ";
    $t2 = $t1."  ";
    foreach ($args["fields"] as $field) {
      $schema_fields.= $t1.'"'.$field["name"].'" => [';
      $schema_fields.= $t2.'"type" => "'.static::entityType($field).'",';
      if (!empty($field["length"]))
        $schema_fields.= $t2.'"length" => '.$field["length"].',';
      if (!empty($field["values"]))
        $schema_fields.= $t2.'"values" => ["'.implode('", "', $field["values"]).'"],';
      if (!empty($field["serialize"]) && $field["serialize"] == "y")
        $schema_fields.= $t2.'"serialize" => true,';
      if (!empty($field["default"])) {
        $v = $field["default"];
        if (!static::fieldIsNumeric($field["type"]))
          $v = '"'.$v.'"';
        $schema_fields.= $t2.'"default" => '.$v.',';
      }
      $schema_fields.= $t1.'],';
    }
    $schema_fields = trim($schema_fields);
    $file = '<?php
class '.$name.'_Entity extends '.$extend.' {
';

    
    foreach ($args["fields"] as $field) {
      if ($field["type"] == "file" && $field["file_getter"]) {
        $file.= '
  public function '.$field["file_getter"].'() {
    return $this->getStoredEntity("File", $this->get("'.$field["name"].'"));
  }
';
      }
    }

    $file.= '
  protected function schema() {
    $schema = parent::schema();
    $schema["table"] = "'.$table.'";
    $schema["fields"]'.($type_default == "y" ? "+" : " ").'= [
      '.$schema_fields.'
    ];
    return $schema;
  }
  
}
';
    
    return $file;
  }
  
  public static function fieldTypes() {
    return [
      "file", "int", "tinyint", "float", "double", "decimal",
      "varchar", "text", "longtext", "enum",
      "blob", "longblob",
    ];
  }
  
  public static function fieldIsNumeric($field) {
    return in_array($field, ["file", "int", "tinyint", "float", "double", "decimal"]);
  }
  
  public static function fieldIsText($field) {
    return in_array($field, ["varchar", "text", "longtext"]);
  }
  
  public static function fieldSqlAdd($field) {
    $sql = "`".$field["name"]."` ".static::columnType($field);
    if (static::fieldIsText($field))
      $sql.= " COLLATE ".static::collateSql();
    if (!empty($field["unsigned"]) && $field["unsigned"] == "y" || $field["type"] == "file")
      $sql.= " unsigned";
    if ($field["null"] == "y")
      $sql.= " NULL";
    else
      $sql.= " NOT NULL";
    if (!empty($field["default"]))
      $sql.= " DEFAULT '".$field["default"]."'";
    return $sql;
  }
  
  public static function fieldSqlIndex($field) {
    return "KEY `".$field["name"]."` (`".$field["name"]."`)";
  }
  
  public static function fieldSqlKey($field) {
    if ($field["type"] == "file")
      return "ADD FOREIGN KEY (`".$field["name"]."`) REFERENCES `file`(`id`) ON DELETE SET NULL ON UPDATE SET NULL";
    return null;
  }
  
  public static function columnType($field) {
    $type = $field["type"];
    if ($type == "file")
      $type = "int";
    $type = strtoupper($type);
    if (!empty($field["length"]))
      $type.= "(".$field["length"].")";
    if (!empty($field["values"]))
      $type.= "('".implode("','", $field["values"])."')";
    return $type;
  }
  
  public static function entityType($field) {
    $type = $field["type"];
    if ($type == "tinyint")
      $type = "int";
    else if ($type == "longtext")
      $type = "text";
    else if ($type == "longblob")
      $type = "blob";
    if (!empty($field["unsigned"]))
      $type = "u".$type;
    return $type;
  }
  
  public static function collateSql() {
    return "utf8_swedish_ci";
  }
  
  public static function engineSql() {
    return "InnoDB";
  }
  
  public static function tableQueries($args) {
    $queries = [];
    
    // Main table structure
    $rows = [];
    // Columns
    $rows[] = "`id` int(10) unsigned NOT NULL AUTO_INCREMENT";
    if ($args["type_default"] == "y" && $args["l10n"] == "y") {
      $rows[] = "`sid` int(11) DEFAULT NULL";
      $rows[] = "`lang` varchar(2) COLLATE ".static::collateSql()." DEFAULT NULL";
    }
    foreach ($args["fields"] as $i => $field) {
      $rows[] = static::fieldSqlAdd($field);
    }
    if ($args["type_default"] == "y") {
      $rows[] = "`status` tinyint(1) NOT NULL DEFAULT '1'";
      $rows[] = "`created` int(10) unsigned NOT NULL DEFAULT '0'";
      $rows[] = "`updated` int(10) unsigned NOT NULL DEFAULT '0'";
    }
    $rows[] = "PRIMARY KEY (`id`)";
    if ($args["type_default"] == "y") {
      if ($args["l10n"] == "y") {
        $rows[] = "KEY `lang` (`lang`)";
        $rows[] = "KEY `sid` (`sid`)";
      }
      $rows[] = "KEY `status` (`status`)";
    }
    // Indexes
    foreach ($args["fields"] as $i => $field) {
      if ($field["index"] == "y")
        $rows[] = static::fieldSqlIndex($field);
    }
    // Compile query
    $sql = "CREATE TABLE IF NOT EXISTS `".$args["table"]."` (";
    foreach ($rows as $i => $row) {
      if ($i != 0)
        $sql.= ",";
      $sql.= "
        ".$row;
    }
    $sql.= "
      ) ENGINE=".static::engineSql()." DEFAULT CHARSET=utf8 COLLATE=".static::collateSql().";";
      
    $queries[] = $sql;
      
    $keys = [];
    foreach ($args["fields"] as $i => $field) {
      $key = static::fieldSqlKey($field);
      if ($key)
        $keys[] = $key;
    }
    if (!empty($keys)) {
      $sql = "ALTER TABLE `".$args["table"]."`";
      foreach ($keys as $i => $key) {
        if ($i != 0)
          $sql.= ",";
        $sql.= "
        ".$key;
      }
      $sql.= ";";
      $queries[] = $sql;
    }
    
    return $queries;
  }
  
  
  protected function farg($name, $prompt, $default = null, $type = null) {
    return static::arg("field_".$name, $prompt, $default, $type);
  }
  
}