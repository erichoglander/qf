<?php
class File_Entity_Core extends Entity {
	
	protected $Imagestyle;
	
	
	public function isImage() {
		return in_array($this->get("extension"), ["jpg", "jpeg", "png", "gif"]);
	}
	
	public function imageStyle($style) {
		if (!$this->isImage())
			return null;
		if (!$this->Imagestyle)
			$this->Imagestyle = newClass("Imagestyle");
		$Imagestyle->src = $this->path();
		return $Imagestyle->style($style);
	}

	public function url() {
		if ($this->get("dir") == "private")
			return PRIVATE_URI."/".$this->get("uri");
		else
			return PUBLIC_URI."/".$this->get("uri");
	}

	public function path() {
		if ($this->get("dir") == "private")
			return PRIVATE_PATH."/".$this->get("uri");
		else
			return PUBLIC_PATH."/".$this->get("uri");
	}
	
	public function filename() {
		$filename = $this->get("name");
		if ($this->get("extension"))
			$filename.= ".".$this->get("extension");
		return $filename;
	}

	public function delete() {
		@unlink($this->path());
		return parent::delete();
	}

	
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