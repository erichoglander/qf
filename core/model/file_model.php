<?php
class File_Model_Core extends Model {
	
	public function uploadFile($structure) {
		$FormItem = newClass("FormItem", $this->Db, $structure);
		return $FormItem->render();
	}

}