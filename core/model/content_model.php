<?php
class Content_Model_Core extends Model {
	
	public function addContent($values) {
		$Content = $this->getEntity("Content");
		$Content->set("config", ["fields" => null]);
		if ($this->editContent($Content, $values))
			return $Content;
		return null;
	}

	public function editContent($Content, $values) {
		$Content->set("title", $values["title"]);
		if ($Content->get("config")) {
			$data = [];
			foreach ($Content->get("config")["fields"] as $i => $field) {
				if (array_key_exists("field_".$i, $values))
					$data[$i] = $values["field_".$i];
			}
			$Content->set("data", $data);
		}
		if (array_key_exists("fields", $values)) {
			$config = $Content->get("config");
			$config["fields"] = $values["fields"];
			$Content->set("config", $config);
		}
		return $Content->save();
	}

	public function deleteContent($Content) {
		return $Content->delete();
	}

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