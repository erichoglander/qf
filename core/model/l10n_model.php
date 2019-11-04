<?php
/**
 * Contains the l10n model
 */
/**
 * l10n model
 * @author Eric Höglander
 */
class l10n_Model_Core extends Model {

  /**
   * Get all active languages
   * @return array
   */
  public function getActiveLanguages() {
    $rows = $this->Db->getRows("SELECT * FROM `language` WHERE status = 1");
    $languages = [];
    foreach ($rows as $row)
      $languages[$row->lang] = $row;
    return $languages;
  }

  public function import($values) {
    if (!empty($values["file"])) {
      $File = $this->getEntity("File", $values["file"]);
      $json = @json_decode(file_get_contents($File->path()));
      if (empty($json))
        throw new Exception(t("File contains invalid JSON"));
      return $this->importJson($json);
    }
    else if (!empty($values["paste_data"])) {
      return $this->importPasted($values["paste_data"]);
    }
    throw new Exception(t("Invalid data source"));
  }

  public function importPasted($raw) {
    $n = 0;
    $rows = $this->split($raw, "\n");
    $header = array_shift($rows);
    $langs = $this->split($header, "\t");
    foreach ($langs as $i => $lang) {
      $lang = trim($lang);
      $langs[$i] = $lang;
      $row = $this->Db->getRow("SELECT * FROM `language` WHERE lang = :lang", [":lang" => $lang]);
      if (!$row)
        throw new Exception(t("Unknown language code :lang", "en", [":lang" => $lang]));
    }
    foreach ($rows as $row) {
      $strings = $this->split($row, "\t", null);
      $Source = null;
      foreach ($langs as $i => $lang) {
        $str = trim($strings[$i]);
        $row = $this->Db->getRow("
            SELECT * FROM `l10n_string`
            WHERE
              sid IS NULL &&
              lang = :lang &&
              string = :string",
            [ ":lang" => $lang,
              ":string" => $str]);
        if ($row) {
          $Source = $this->getEntity("l10nString");
          $Source->loadRow($row);
          break;
        }
      }

      if (!$Source) {
        foreach ($langs as $i => $lang) {
          if (!empty($strings[$i])) {
            $str = trim($strings[$i]);
            $Source = $this->getEntity("l10nString");
            $Source->set("string", $str);
            $Source->set("input_type", "import");
            $Source->set("lang", $lang);
            $Source->save();
            break;
          }
        }
      }
      if (!$Source)
        continue;

      foreach ($langs as $i => $lang) {
        if (!empty($strings[$i])) {
          $str = trim($strings[$i]);
          if ($Source->get("lang") != $lang) {
            $String = $Source->translation($lang, true);
            if ($String->get("string") != $str) {
              $String->set("string", $str);
              $String->set("input_type", "import");
              $String->save();
              $n++;
            }
          }
        }
      }
    }
    return $n;
  }

  public function split($input, $delimiter, $enclosure = '"') {
    $arr = [];
    $len = strlen($input);
    $in_enclosure = false;
    $str = "";
    for ($i=0; $i<$len; $i++) {
      $c = $input[$i];
      if ($c == $enclosure) {
        $in_enclosure = !$in_enclosure;
      }
      else if ($c == $delimiter && !$in_enclosure) {
        $arr[] = $str;
        $str = "";
      }
      else {
        $str.= $c;
      }
    }
    $arr[] = $str;
    return $arr;
  }

  /**
   * Import string translations
   *
   * Data given in an array of objects, containing
   * lang, string, and translations.
   * Those translations themselves also containing lang and string.
   *
   * @see    export
   * @param  array $l10n_strings
   * @return int   The number of imported strings
   */
  public function importJson($l10n_strings = []) {
    $n = 0;
    foreach ($l10n_strings as $l10n_string) {
      if (empty($l10n_string->string) || empty($l10n_string->lang))
        continue;
      $source = $this->Db->getRow("
          SELECT id FROM `l10n_string`
          WHERE
            lang = :lang &&
            string = :string &&
            sid IS NULL",
          [  ":lang" => $l10n_string->lang,
            ":string" => $l10n_string->string]);
      if ($source) {
        $l10nString = $this->getEntity("l10nString", $source->id);
      }
      else {
        $l10nString = $this->getEntity("l10nString");
        $l10nString->set("lang", $l10n_string->lang);
        $l10nString->set("input_type", "import");
        $l10nString->set("string", $l10n_string->string);
        $l10nString->save();
      }
      foreach ($l10n_string->translations as $lang => $translation) {
        if (empty($translation->string))
          continue;
        if ($source && $l10nString->translation($lang)) {
          if ($l10nString->translation($lang)->get("string") == $translation->string)
            continue;
          if ($l10nString->translation($lang)->get("input_type") == "manual")
            continue;
          $l10nString->translation($lang)->set("string", $translation->string);
          $l10nString->translation($lang)->set("input_type", "import");
          $l10nString->translation($lang)->save();
          $n++;
        }
        else {
          $l10nString->newTranslation($lang);
          $l10nString->translation($lang)->set("lang", $lang);
          $l10nString->translation($lang)->set("string", $translation->string);
          $l10nString->translation($lang)->set("sid", $l10nString->id());
          $l10nString->translation($lang)->set("input_type", "import");
          $l10nString->translation($lang)->save();
          $n++;
        }
      }
    }
    return $n;
  }

  /**
   * Export all string translations
   * @see    import
   * @param  array $values Search parameters
   * @return string
   */
  public function export($values = []) {
    $values+= [
      "format" => "json_min",
    ];
    $l10n_strings = [];
    $sql = "SELECT id, lang, string FROM `l10n_string` WHERE sid IS NULL";
    $rows = $this->Db->getRows($sql);
    if (!empty($rows)) {
      $sql = "SELECT lang, string, updated FROM `l10n_string` WHERE sid = :id";
      if (!empty($values["input_type"]))
        $sql.= " && input_type IN ('".implode("','", $values["input_type"])."')";
      if (!empty($values["language"]))
        $sql.= " && lang IN ('".implode("','", $values["language"])."')";
      foreach ($rows as $row) {
        $row->translations = [];
        $translations = $this->Db->getRows($sql, [":id" => $row->id]);
        foreach ($translations as $translation)
          $row->translations[$translation->lang] = $translation;
        unset($row->id);
        $l10n_strings[] = $row;
      }
    }
    if ($values["format"] == "json_pretty") {
      return json_encode($l10n_strings, JSON_PRETTY_PRINT);
    }
    else if ($values["format"] == "json_min") {
      return json_encode($l10n_strings);
    }
    else if ($values["format"] == "xml") {

      $rows = [];

      // Get languages
      $langs = [];
      if (!empty($values["language"])) {
        $langs = $values["language"];
      }
      else  {
        $languages = $this->Db->getRows("SELECT lang FROM `language` WHERE status = 1");
        foreach ($languages as $lang)
          $langs[] = $lang->lang;
      }
      foreach ($langs as $lang)
        $rows[0][$lang] = $lang;

      // Transform string data into rows
      foreach ($l10n_strings as $string) {
        $row = [];
        $row[$string->lang] = $string->string;
        foreach ($string->translations as $str)
          $row[$str->lang] = $str->string;
        $rows[] = $row;
      }

      // Compile data to xml
      $data = "";
      foreach ($rows as $row) {
        $data.= '
            <Row>';
        foreach ($langs as $lang) {
          if (!empty($row[$lang])) {
            $str = xss($row[$lang]);
            $str = preg_replace("/\r?\n/", "&#10;", $str);
          }
          else {
            $str = null;
          }
          $data.= '
              <Cell ss:StyleID="s62"><Data ss:Type="String">'.$str.'</Data></Cell>';
        }
        $data.= '
            </Row>';
      }
      
      // Column widths
      $column_widths = '';
      foreach ($langs as $lang)
        $column_widths.= '<Column ss:AutoFitWidth="0" ss:Width="250"/>';
      
      $xml = ' <?xml version="1.0"?>
        <?mso-application progid="Excel.Sheet"?>
        <Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
         xmlns:o="urn:schemas-microsoft-com:office:office"
         xmlns:x="urn:schemas-microsoft-com:office:excel"
         xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
         xmlns:html="http://www.w3.org/TR/REC-html40">
         <Styles>
          <Style ss:ID="s62">
           <Alignment ss:Vertical="Bottom" ss:WrapText="1"/>
          </Style>
         </Styles>
         <Worksheet ss:Name="Default">
          <Table>
            '.$column_widths.'
            '.$data.'
          </Table>
         </Worksheet>
        </Workbook>';
      return $xml;
    }
    return null;
  }

  /**
   * Save a localized string
   * @param  \l10nString_Entity_Core $l10nString
   * @param  array                   $values     Associative array of $lang => $string
   * @return bool
   */
  public function editString($l10nString, $values) {
    if (!$l10nString->id())
      return false;
    foreach ($values as $lang => $string) {
      if (empty($string))
        continue;
      if (!$l10nString->translation($lang)) {
        $l10nString->newTranslation($lang);
        $l10nString->translation($lang)->set("lang", $lang);
        $l10nString->translation($lang)->set("input_type", "manual");
        $l10nString->translation($lang)->set("string", $string);
        $l10nString->translation($lang)->set("sid", $l10nString->id());
        if (!$l10nString->translation($lang)->save())
          return false;
      }
      else {
        if ($l10nString->translation($lang)->get("string") != $string) {
          $l10nString->translation($lang)->set("input_type", "manual");
          $l10nString->translation($lang)->set("string", $string);
          if (!$l10nString->translation($lang)->save())
            return false;
        }
      }
    }
    return true;
  }

  /**
   * Delete a localized string
   * @param  \l10nString_Entity_Core $l10nString
   * @return bool
   */
  public function deleteString($l10nString) {
    return $l10nString->deleteAll();
  }

  /**
   * Creates database query for a string search
   * @param  string $q Search string
   * @return array  Contains sql-query and vars
   */
  public function searchQuery($q) {
    $vars = [];
    $sql = "
        SELECT source.* FROM `l10n_string` as source
        LEFT JOIN `l10n_string` as trans ON
          trans.sid = source.id
        WHERE source.sid IS NULL";
    if ($q) {
      $sql.= " && (
          source.string LIKE :q ||
          trans.string LIKE :q
      )";
      $vars[":q"] = "%".$q."%";
    }
    $sql.= " GROUP BY source.id";
    return [$sql, $vars];
  }
  /**
   * Number of matches for a search
   * @see    searchQuery
   * @param  string $q
   * @return int
   */
  public function searchNum($q = null) {
    list($sql, $vars) = $this->searchQuery($q);
    return $this->Db->numRows($sql, $vars);
  }
  /**
   * Get localized strings matching a search
   * @see    searchQuery
   * @param  string $q     Search string
   * @param  int    $start
   * @param  int    $stop
   * @return array
   */
  public function search($q = null, $start = 0, $stop = 30) {
    list($sql, $vars) = $this->searchQuery($q);
    $sql.= " ORDER BY created DESC";
    $sql.= " LIMIT ".$start.", ".$stop;
    $sources = $this->Db->getRows($sql, $vars);
    $l10n_strings = [];
    foreach ($sources as $source) {
      $l10nString = $this->getEntity("l10nString", $source->id);
      $l10n_strings[] = $l10nString;
    }
    return $l10n_strings;
  }

  /**
   * Scan code for translation calls and add them to database if needed
   * @see    scan
   * @param  array $parts Which parts of the codebase to scan
   * @return int   $n     Number of strings added to database
   */
  public function scanAdd($parts) {
    $arr = $this->scan($parts);
    $n = 0;
    foreach ($arr as $l10n_string) {
      $l10nString = $this->getEntity("l10nString");
      if (!$l10nString->loadFromString($l10n_string->string, $l10n_string->lang)) {
        $l10nString->set("lang", $l10n_string->lang);
        $l10nString->set("string", $l10n_string->string);
        $l10nString->set("input_type", "code");
        $l10nString->save();
        $n++;
      }
    }
    return $n;
  }

  /**
   * Scan code for translation calls and return information about the scan
   * @see    scan
   * @param  array $parts Which parts of the codebase to scan
   * @return array
   */
  public function scanInfo($parts) {
    $arr = $this->scan($parts);
    $info = ["total" => count($arr), "new" => 0];
    $l10nString = $this->getEntity("l10nString");
    foreach ($arr as $l10n_string) {
      if (!$l10nString->loadFromString($l10n_string->string, $l10n_string->lang))
        $info["new"]++;
    }
    return $info;
  }

  /**
   * Scan code for translation calls
   * @see    scanFiles
   * @param  array $parts Which parts of the codebase to scan
   * @return array
   */
  public function scan($parts) {
    if (in_array("core", $parts))
      $arr[] = DOC_ROOT."/core";
    if (in_array("extend", $parts))
      $arr[] = DOC_ROOT."/extend";
    if (empty($arr))
      return null;
    return $this->scanFiles($arr);
  }

  /**
   * Scan folders for translation calls
   * @see    scanString
   * @param  array $arr Folders to scan
   * @return array
   */
  public function scanFiles($arr) {
    $t = [];
    foreach ($arr as $file) {
      if (is_dir($file)) {
        if (strpos($file, "core/builder/schema") !== false)
          continue;
        $files = glob($file."/*");
        $t = array_merge($t, $this->scanFiles($files));
      }
      else if (substr($file, -4) == ".php") {
        $str = @file_get_contents($file);
        if ($str) {
          $sources = $this->scanString($str);
          if (!empty($sources))
            $t = array_merge($t, $sources);
        }
      }
    }
    return array_unique($t, SORT_REGULAR);
  }

  /**
   * Scan a string for translation calls
   * @param  string $str
   * @return array  Data of translation calls with the strings and languages
   */
  public function scanString($str) {
    $arr = [];
    $n = preg_match_all("/[^a-z0-9\_\>\$]t\(([\"|\'])([^\"]+)[\"|\'](\,\s*[\"|\']([a-z]+)[\"|\'])?/i", $str, $matches);
    if (!$n)
      return null;
    foreach ($matches[2] as $i => $string) {
      $lang = (empty($matches[4][$i]) ? "en" : $matches[4][$i]);
      // Fix whitespace characters
      if ($matches[1][$i] == '"')
        $string = str_replace(["\\r", "\\n", "\\t"], ["\r", "\n", "\t"], $string);
      $arr[] = (object) [
        "string" => $string,
        "lang" => $lang,
      ];
    }
    return $arr;
  }

}