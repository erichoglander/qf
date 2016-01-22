<?php
/**
 * Contains the form model
 */
/**
 * Form model
 * @author Eric Höglander
 */
class Form_Model_Core extends Model {
	
	/**
	 * Get json formatted form item based on given structure
	 * @param  array $structure
	 * @return string
	 */
	public function fileItem($structure) {
		$FormItem = newClass($structure["form_item_class"], $this->Db, $this->Io, $structure);
		return JsonToHtml\htmlToJson($FormItem->render());
	}
	
	/**
	 * Get json formatted file form item and attempt to upload file
	 * @see    \File_FormItem_Core
	 * @param  array $structure
	 * @return string
	 */
	public function uploadFile($structure) {
		$structure["submitted"] = true;
		$FormItem = newClass($structure["form_item_class"], $this->Db, $this->Io, $structure);
		return JsonToHtml\htmlToJson($FormItem->render());
	}
	
	/**
	 * Get json formatted file form item and remove file
	 * @see    \File_FormItem_Core
	 * @param  array $structure
	 * @return string
	 */
	public function removeFile($structure, $File) {
		$structure["value"] = 0;
		if ($File->get("status") == 0)
			$File->delete();
		$FormItem = newClass($structure["form_item_class"], $this->Db, $this->Io, $structure);
		return JsonToHtml\htmlToJson($FormItem->render());
	}

}