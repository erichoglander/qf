<?php
/**
 * Contains the updater model
 */
/**
 * Updater model
 * @author Eric Höglander
 */
class Updater_Model_Core extends Model {

  /**
   * Run a specific update
   * @param  \Update_Core $Update
   * @return bool
   */
  public function runUpdate($Update) {
    if (!$Update->execute())
      return false;
    $last = $this->Variable->get($Update->part()."_update", 0);
    $this->Variable->set($Update->part()."_update", max($Update->nr(), $last));
    return true;
  }
  
  /**
   * Get all pending updates
   * @return array
   */
  public function getUpdates() {
    $updates = [];
    $parts = ["core", "extend"];
    foreach ($parts as $part) {
      $last = $this->Variable->get($part."_update", 0);
      $files = glob(DOC_ROOT."/".$part."/update/update_*.php");
      foreach ($files as $file) {
        $info = pathinfo($file);
        $nr = (int) substr($info["filename"], 7);
        if ($nr > $last) {
          require_once($file);
          $cname = "Update_".$nr;
          if ($part == "core")
            $cname.= "_Core";
          if (!class_exists($cname))
            throw new Exception("Class not found for update ".$nr." (".$part.")");
          $updates[] = new $cname($this->Db);
        }
      }
      usort($updates, [$this, "compareUpdates"]);
    }
    return $updates;
  }

  /**
   * Comparison function for updates in order of execution
   * @param  \Update_Core $a
   * @param  \Update_Core $b
   * @return int
   */
  public function compareUpdates($a, $b) {
    if ($a->part() == $b->part()) {
      if ($a->nr() < $b->nr())
        return -1;
      else
        return 1;
    }
    else if ($a->part() == "core")
      return -1;
    else
      return 1;
  }

  /**
   * Update any missing translations from file
   * @return int Number of translations added.
   */
  public function updateTranslations() {
    $parts = ["core", "extend"];
    $n = 0;
    foreach ($parts as $part) {
      $path = DOC_ROOT."/".$part."/update/l10n_strings.json";
      if (!file_exists($path))
        continue;
      $json = @json_decode(file_get_contents($path));
      if (!$json)
        continue;
      $l10nModel = $this->getModel("l10n");
      $n+= $l10nModel->importJson($json);
    }
    return $n;
  }

}