<?php
class File_FormItem extends FormItem {
	
	protected $upload_button, $remove_button;
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
		$data = $this->postValue();
		if (!empty($data["id"]))
			return (int) $data["id"];
		pr($_FILES);
		pr($data);
		exit;
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
		$File = newClass("File", $file_id);
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