<?php
class Form_Model_Core extends Model {
	
	public function fileItem($structure) {
		$FormItem = newClass($structure["form_item_class"], $this->Db, $this->Io, $structure);
		return JsonToHtml\htmlToJson($FormItem->render());
	}
	
	public function uploadFile($structure) {
		$structure["submitted"] = true;
		$FormItem = newClass($structure["form_item_class"], $this->Db, $this->Io, $structure);
		return JsonToHtml\htmlToJson($FormItem->render());
	}
	
	public function removeFile($structure, $File) {
		$structure["value"] = 0;
		if ($File->get("status") == 0)
			$File->delete();
		$FormItem = newClass($structure["form_item_class"], $this->Db, $this->Io, $structure);
		return JsonToHtml\htmlToJson($FormItem->render());
	}

}