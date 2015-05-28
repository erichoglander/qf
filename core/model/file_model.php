<?php
class File_Model_Core extends Model {
	
	public function uploadFile($structure) {
		$structure["submitted"] = true;
		$FormItem = newClass($structure["form_item_class"], $this->Db, $this->Io, $structure);
		return JsonToHtml\htmlToJson($FormItem->render());
	}
	
	public function removeFile($structure, $File) {
		$structure["value"] = 0;
		$File->delete();
		$FormItem = newClass($structure["form_item_class"], $this->Db, $this->Io, $structure);
		return JsonToHtml\htmlToJson($FormItem->render());
	}

}