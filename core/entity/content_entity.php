<?php
class Content_Entity_Core extends l10n_Entity {

	protected $Acl, $User;


	public function render() {
		$data = $this->get("data");
		$html = '<div class="content-entity content-entity-'.$this->id().'">';
		if ($this->editAccess())
			$html.= $this->editButton();
		$html.= '<div class="inner">';
		foreach ($this->get("config")["fields"] as $i => $field) {
			$html.= '<div class="field field-'.$field["type"].' field-'.($i+1).'">';
			$html.= $this->renderField($field["type"], $data[$i]);
			$html.= '</div>';
		}
		$html.= '</div></div>';
		return $html;
	}

	public function editButton() {
		return 
			'<a class="edit-btn" href="/content/edit/'.$this->id().'?redir='.REQUEST_ALIAS.'">'.
				FontAwesome\Icon("pencil").
			'</a>';
	}

	public function fields() {
		$arr = [];
		foreach ($this->get("config")["fields"] as $field) {
			$arr[] = [
				"type" => $field["type"],
				"title" => $field["title"],
				"description" => $field["description"],
			];
		}
		return $arr;
	}

	public function renderField($field, $value) {
		if ($field == "text") {
			return xss($value);
		}
		if ($field == "textarea") {
			return nl2br(xss($value));
		}
		if ($field == "image") {
			$File = $this->getEntity("File", $value);
			if (!$File->id())
				return null;
			return '<img src="'.$File->url().'" alt="">';
		}
		if ($field == "editor") {
			return $value;
		}
	}


	protected function editAccess() {
		if (!$this->Acl) {
			$this->Acl = newClass("Acl", $this->Db);
			$this->User = newClass("User_Entity", $this->Db, (!empty($_SESSION["user_id"]) ? $_SESSION["user_id"] : null));
		}
		return $this->Acl->access($this->User, ["contentAdmin", "contentEdit"]);
	}
	
	protected function schema() {
		$schema = parent::schema();
		$schema["table"] = "content";
		$schema["fields"]["title"] = [
			"type" => "varchar",
		];
		$schema["fields"]["data"] = [
			"type" => "blob",
			"serialize" => true,
		];
		$schema["fields"]["config"] = [
			"type" => "blob",
			"serialize" => true,
		];
		return $schema;
	}

}