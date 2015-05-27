<?php
class File_Model_Core extends Model {
	
	public function uploadFile($structure) {
		$FormItem = newClass("File_FormItem", $this->Db, $this->Io, $structure);
		return JsonToHtml\htmlToJson($FormItem->render());
	}

}