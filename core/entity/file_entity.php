<?php
/**
 * Contains the file entity
 */
/**
 * File entity
 *
 * Provides the connection between a file on the file system
 * and the data stored in the database.
 * Data is usually refered to as 'entity'
 * File on disk is usually refered to as 'file'
 * Together they a refered to as 'managed file'
 */
class File_Entity_Core extends Entity {
	
	/**
	 * Imagestyle object
	 * @var \Imagestyle_Core
	 */
	protected $Imagestyle;
	
	
	/**
	 * Whether or not the managed file is an image
	 * @return bool
	 */
	public function isImage() {
		return in_array($this->get("extension"), ["jpg", "jpeg", "png", "gif"]);
	}
	
	/**
	 * Create an image of given style if the managed file is an image
	 * @see    \Imagestyle_Core::style()
	 * @param  string $style
	 * @return string
	 */
	public function imageStyle($style) {
		if (!$this->isImage())
			return null;
		if (!$this->Imagestyle)
			$this->Imagestyle = newClass("Imagestyle");
		$this->Imagestyle->src = $this->path();
		return $this->Imagestyle->style($style);
	}

	/**
	 * The url of the file
	 * @param  bool $abs If true, an absolute url is returned
	 * @return string
	 */
	public function url($abs = false) {
		if ($this->get("dir") == "private")
			$url = PRIVATE_URI.BASE_PATH.$this->get("uri");
		else
			$url = PUBLIC_URI."/".$this->get("uri");
		if ($abs)
			$url = SITE_URL.$url;
		return $url;
	}

	/**
	 * The system path of the file
	 * @return string
	 */
	public function path() {
		if ($this->get("dir") == "private")
			return PRIVATE_PATH."/".$this->get("uri");
		else
			return PUBLIC_PATH."/".$this->get("uri");
	}
	
	/**
	 * The intended filename based on entity
	 *
	 * With extension
	 * Without path
	 * Without any prefix/suffix added while saving
	 * @return string
	 */
	public function filename() {
		$filename = $this->get("name");
		if ($this->get("extension"))
			$filename.= ".".$this->get("extension");
		return $filename;
	}
	
	/**
	 * Prompt file for download
	 */
	public function prompt() {
		header('Content-Disposition: attachment;filename="'.$this->filename().'"');
		promptFile($this->path());
	}
	
	/**
	 * Copy entity and file
	 * @return \File_Entity_Core
	 */
	public function copy() {
		$Copy = $this->getEntity("File");
		foreach ($this->schema["fields"] as $key => $field)
			$Copy->set($key, $this->get($key));
		$info = pathinfo($Copy->get("uri"));
		$path = ($Copy->get("dir") == "private" ? PRIVATE_PATH : PUBLIC_PATH).BASE_PATH;
		$uri = $info["dirname"]."/";
		$name = $Copy->get("name");
		$ext = $Copy->get("extension");
		for ($fname = $name."-0.".$ext, $i = 1; file_exists($path.$uri.$fname); $fname = $name."-".$i.".".$ext, $i++);
		if (!copy($this->path(), $path.$uri.$fname)) {
			setmsg("Failed to copy file: ".$this->path()." to ".$path.$uri.$fname, "error");
			return false;
		}
		$Copy->set("uri", $uri.$fname);
		if (!$Copy->save())
			return false;
		return $Copy;
	}
	
	/**
	 * Attempt to load managed file from a uri
	 * @param  string $uri
	 * @param  string $dir
	 * @return bool
	 */
	public function loadFromUri($uri, $dir = "public") {
		$row = $this->Db->getRow("
				SELECT * FROM `file`
				WHERE 
					uri = :uri && 
					dir = :dir",
				[	":uri" => $uri,
					":dir" => $dir]);
		if ($row) {
			$this->load($row->id);
			return true;
		}
		else {
			$this->set("uri", $uri);
			$this->set("dir", $dir);
			$info = pathinfo($this->path());
			$this->set("name", $info["filename"]);
			$this->set("extension", $info["extension"]);
			return false;
		}
	}

	/**
	 * Delete file along with entity
	 * @return bool
	 */
	public function delete() {
		@unlink($this->path());
		return parent::delete();
	}

	
	/**
	 * Database schema
	 * @return array
	 */
	protected function schema() {
		$schema = parent::schema();
		$schema["table"] = "file";
		$schema["fields"]["name"] = [
			"type" => "varchar",
		];
		$schema["fields"]["uri"] = [
			"type" => "varchar",
		];
		$schema["fields"]["extension"] = [
			"type" => "varchar",
		];
		$schema["fields"]["dir"] = [
			"type" => "enum",
			"values" => ["public", "private"],
			"default" => "public",
		];
		return $schema;
	}

}