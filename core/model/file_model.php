<?php
class File_Model_Core extends Model {
	
	public function uploadFile($structure) {
		$FormItem = newClass("FormItem", $this->Io, $structure);
		return JsonToHtml\htmlToJson($FormItem->render());
	}

}