<?php
class File_Model_Core extends Model {
	
	public function getPrivateFileFromUri($uri) {
		$File = $this->getEntity("File");
		$File->loadFromUri($uri, "private");
		if (!$File->id())
			return null;
		return $File;
	}
	
}