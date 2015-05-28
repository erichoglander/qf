<?php
class Image_FormItem extends File_FormItem {
	
	protected $file_extensions = ["jpeg", "jpg", "png", "gif"];
	protected $image_exact_size, $image_min_size, $image_max_size;
	protected $template = "file";
	protected $preview_template = "image";


	protected function loadDefault() {
		parent::loadDefault();
		$this->remove_button = t("Remove image");
	}

	protected function fileUploadValidate($file) {
		list($width, $height) = getimagesize($file["tmp_name"]);
		$e = null;
		if ($this->image_exact_size !== null && 
					($width > $this->image_exact_size[0] || $height > $this->image_exact_size[1]) ||
				$this->image_max_size !== null && 
					($width > $this->image_max_size[0] || $height > $this->image_max_size[1])) {
			$this->setError(t("Image too large"));
			return false;
		}
		if ($this->image_exact_size !== null && 
					($width < $this->image_exact_size[0] || $height < $this->image_exact_size[1]) ||
				$this->image_min_size !== null && 
					($width < $this->image_min_size[0] || $height < $this->image_min_size[1])) {
			$this->setError(t("Image too small"));
			return false;
		}
		return true;
	}

	protected function preRenderInput(&$vars) {
		parent::preRenderInput($vars);
		$vars["file_extra_text"] = $this->imageSizeText();
	}

	protected function imageSizeText() {
		if ($this->image_exact_size !== null) {
			$size = $this->image_exact_size;
			return t("Image must be :wx:h", "en", [":w" => $size[0], ":h" => $size[1]]);
		}
		else if ($this->image_min_size !== null && $this->image_max_size !== null) {
			return t("Image must be between :minwx:minh and :maxwx:maxh", "en",
					[	":minw" => $this->image_min_size[0],
						":minh" => $this->image_min_size[1],
						":maxw" => $this->image_max_size[0],
						":maxh" => $this->image_max_size[1]]);
		}
		else if ($this->image_min_size !== null) {
			return t("Image must be larger than :wx:h", "en",
					[	":w" => $this->image_min_size[0],
						":h" => $this->image_min_size[1]]);
		}
		else if ($this->image_max_size !== null) {
			return t("Image must be smaller than :wx:h", "en",
					[	":w" => $this->image_max_size[0],
						":h" => $this->image_max_size[1]]);
		}
	}

}