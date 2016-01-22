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
		$Content->set("title", $values["title"]);
		if (array_key_exists("fields", $values)) {
			$config = $Content->get("config");
			$config["fields"] = $values["fields"];
			$Content->set("config", $config);
		}
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
			$data = [];
			foreach ($Content->get("config")["fields"] as $i => $field) {
				if (array_key_exists("field_".$i, $values))
					$data[$i] = $values["field_".$i];
			}
			$Content->set("data", $data);
		}
		return $Content->save();
	}

	/**
	 * Delete content
	 * @return bool
	 */
	public function deleteContent($Content) {
		return $Content->delete();
	}

	/**
	 * Get all content entities
	 * @return array
	 */
	public function getContents() {
		$rows = $this->Db->getRows("
				SELECT id FROM `content`
				ORDER BY title ASC");
		$arr = [];
		foreach ($rows as $row)
			$arr[] = $this->getEntity("Content", $row->id);
		return $arr;
	}

}