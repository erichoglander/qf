<?php
class File_FormItem extends FormItem {
	
	protected $upload = false;
	protected $uploaded = null;
	protected $upload_button, $remove_button;
	protected $upload_folder;
	protected $file_extensions, $file_dir, $file_max_size;
	protected $upload_callback, $remove_callback;
	protected $preview_template;


	public function loadDefault() {
		$this->upload_button = t("Upload file");
		$this->remove_button = t("Remove file");
		$this->file_dir = "public";
	}

	public function hasFileItem() {
		return true;
	}

	public function value() {
		if (!$this->submitted)
			return $this->value;
		if ($this->upload) {
			$this->upload = false;
			return $this->uploadFile();
		}
		else if ($this->uploaded !== null) {
			return $this->uploaded;
		}
		else {
			$data = $this->postValue();
			if (!empty($data["id"]))
				return (int) $data["id"];
			else
				return null;
		}
	}


	protected function uploadFile() {
		$keys = explode("[", str_replace("]", "", $this->inputName()));
		$file = $_FILES[$keys[0]];
		if (count($keys) > 1) {
			array_shift($keys);
			$keys[] = "file";
			foreach (array_keys($file) as $field)
				$file[$field] = $this->nestedValue($file[$field], $keys);
		}
		if (!empty($file["error"])) {
			$errors = [
				UPLOAD_ERR_INI_SIZE => t("File is too big (server file limit)"),
				UPLOAD_ERR_FORM_SIZE => t("File is too big (server form limit)"),
				UPLOAD_ERR_PARTIAL => t("The file was only partially upload, please try again"),
				UPLOAD_ERR_NO_FILE => t("No file was uploaded"),
				UPLOAD_ERR_NO_TMP_DIR => t("Missing temp folder, contact administrator"),
				UPLOAD_ERR_CANT_WRITE => t("Can't write file to disk, contact administrator"),
				UPLOAD_ERR_EXTENSION => t("Upload stopped by a php extension"),
			];
			if (isset($errors[$file["error"]]))
				$this->setError($errors[$file["error"]]);
			else
				$this->setError(t("An error occurred while uploading file"));
			return $this->value;
		}
		$info = pathinfo($file["name"]);
		if (!empty($this->file_extensions) && !in_array($info["extension"], $this->file_extensions)) {
			$this->setError(
					t("Unallowed file extension. Only :ext", 
						"en", 
						[":ext" => implode(", ", $this->file_extensions)]));
			return $this->value;
		}
		$path = ($this->upload_dir == "private" ? PRIVATE_PATH : PUBLIC_PATH)."/";
		$uri = $this->upload_folder."/";
		$name = $this->Io->filter($info["filename"], "filename");
		$basename = $this->Io->filter($info["basename"], "filename");
		for ($fname = $basename, $i = 0; file_exists($path.$uri.$fname); $fname = $basename."-".$i, $i++);
		move_uploaded_file($file["tmp_name"], $path.$uri.$fname);
		$File = newClass("File_Entity", $this->Db);
		$File->set("dir", $this->upload_dir);
		$File->set("name", $name);
		$File->set("uri", $uri.$fname);
		$File->set("extension", $info["extension"]);
		$File->set("status", 0);
		if (!$File->save()) {
			$this->setError(t("An error occurred while saving file"));
			return false;
		}
		$this->uploaded = $File->id();
		return $File->id();
	}

	protected function nestedValue($arr, $keys) {
		while (!empty($keys)) {
			$arr = $arr[$keys[0]];
			array_shift($keys);
		}
		return $arr;
	}

	protected function templatePreviewPath() {
		$prefix = "form_file_preview";
		$d = "__";
		$names = [];
		if ($this->preview_template)
			$names[] = $prefix."__".$this->preview_template;
		$names[] = $prefix;
		foreach ($names as $name) {
			$path = DOC_ROOT."/extend/template/form/".$name.".php";
			if (file_exists($path))
				return $path;
		}
		foreach ($names as $name) {
			$path = DOC_ROOT."/core/template/form/".$name.".php";
			if (file_exists($path))
				return $path;
		}
		return null;
	}

	protected function preview() {
		$file_id = $this->value();
		if (!$file_id)
			return null;
		$File = newClass("File_Entity", $file_id);
		$path = $this->templatePreviewPath();
		$vars = [
			"size" => filesize($File->path()),
			"name" => $File->get("name"),
			"url" => $File->get("url"),
			"path" => $File->path(),
			"extension" => $File->get("extension"),
		];
		if (is_callable([$this, "preRenderPreview"]))
			$this->preRenderPreview($vars);
		return renderTemplate($path, $vars);
	}

	protected function uploadCallback() {
		return ($this->upload_callback ? $this->upload_callback : "null");
	}
	protected function removeCallback() {
		return ($this->remove_callback ? $this->remove_callback : "null");
	}

	protected function fileToken() {
		return $this->form_name."--".str_replace("[", "-", str_replace("]", "", $this->inputName()));
	}

	protected function fileSession() {
		if (empty($_SESSION["file_upload"]))
			$_SESSION["file_upload"] = [];
		$token = $this->fileToken();
		$info = $this->structure;
		$info["submitted"] = true;
		$info["upload"] = true;
		$_SESSION["file_upload"][$token] = $info;
		return $token;
	}

	protected function preRenderInput(&$vars) {
		$vars["upload_button"] = $this->upload_button;
		$vars["remove_button"] = $this->remove_button;
		$vars["remove_callback"] = $this->removeCallback();
		$vars["file_extensions"] = $this->file_extensions;
		$vars["file_max_size"] = $this->file_max_size;
		$vars["preview"] = $this->preview();
		$vars["token"] = $this->fileSession();
	}

	protected function getAttributes() {
		$attr = parent::getAttributes();
		$attr["name"].= "[file]";
		$attr["onchange"] = "formFileUpload(this, ".$this->uploadCallback().")";
		return $attr;
	}

	protected function itemClass() {
		$class = parent::itemClass();
		if ($this->value())
			$class.= " has-value";
		return $class;
	}

}