<?php
class File_Model_Core extends Model {
	
	public function uploadFile($structure) {
		$structure["submitted"] = true;
		$FormItem = newClass("File_FormItem", $this->Db, $this->Io, $structure);
		return JsonToHtml\htmlToJson($FormItem->render());
	}
	
	public function removeFile($structure, $File) {
		$structure["value"] = 0;
		$File->delete();
		$FormItem = newClass("File_FormItem", $this->Db, $this->Io, $structure);
		return JsonToHtml\htmlToJson($FormItem->render());
	}

}