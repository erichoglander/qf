<?php
/**
 * Contains the content model
 */
/**
 * Content model
 * @author Eric Höglander
 */
class Content_Model_Core extends Model {
	
	/**
	 * Add content, which means saving config
	 * @see    configContent
	 * @param  array values
	 * @return \Content_Entity_Core
	 */
	public function addContent($values) {
		$Content = $this->getEntity("Content");
		$Content->set("config", ["fields" => null]);
		if ($this->configContent($Content, $values))
			return $Content;
		return null;
	}

	/**
	 * Save content config
	 * @param  \Content_Entity_Core $Content
	 * @param  array                $values
	 * @return bool
	 */
	public function configContent($Content, $values) {
		$config = $Content->get("config");
		$Content->set("title", $values["title"]);
		if (array_key_exists("l10n", $values)) 
			$config["l10n"] = $values["l10n"];
		if (array_key_exists("fields", $values)) 
			$config["fields"] = $values["fields"];
		$Content->set("config", $config);
		return $Content->save();
	}

	/**
	 * Save content data
	 * @param  \Content_Entity_Core $Content
	 * @param  array                $values
	 * @return bool
	 */
	public function editContent($Content, $values) {
		if ($Content->get("config")) {
			if ($Content->l10n()) {
				foreach ($values as $lang => $vals) {
					$data = [];
					$C = $Content->translation($lang, true);
					foreach ($Content->get("config")["fields"] as $i => $field) {
						if (array_key_exists("field_".$i, $vals))
							$data[$i] = $vals["field_".$i];
					}
					$C->set("data", $data);
					$C->save();
				}
			}
			else {
				$data = [];
				foreach ($Content->get("config")["fields"] as $i => $field) {
					if (array_key_exists("field_".$i, $values))
						$data[$i] = $values["field_".$i];
				}
				$Content->set("data", $data);
				$Content->save();
			}
		}
		return true;
	}

	/**
	 * Delete content
	 * @return bool
	 */
	public function deleteContent($Content) {
		return $Content->delete();
	}

	/**
	 * Get all  source content entities
	 * @return array
	 */
	public function getContents() {
		$rows = $this->Db->getRows("
				SELECT id FROM `content`
				WHERE sid IS NULL
				ORDER BY title ASC");
		$arr = [];
		foreach ($rows as $row)
			$arr[] = $this->getEntity("Content", $row->id);
		return $arr;
	}

}