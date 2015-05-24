<?php
class RedirectEdit_Form_Core extends Form {
	
	public function validate($values) {
		$Redirect = $this->get("Redirect");
		$row = $this->Db->getRow("SELECT * FROM `redirect` WHERE source = :source", [":source" => $values["source"]]);
		if ($row && (!$Redirect || $row->id != $Redirect->id())) {
			$this->setError(t("Redirect source already exists"), "source");
			return false;
		}
		return true;
	}

	public function structure() {
		$Redirect = $this->get("Redirect");
		return [
			"name" => "redirect_edit",
			"items" => [
				"source" => [
					"type" => "text",
					"label" => t("Source"),
					"required" => true,
					"value" => ($Redirect ? $Redirect->get("source") : null),
					"focus" => true,
				],
				"target" => [
					"type" => "text",
					"label" => t("Target"),
					"description" => xss(t("Enter <front> for the front page")),
					"required" => true,
					"value" => ($Redirect ? $Redirect->get("target") : null),
				],
				"code" => [
					"type" => "select",
					"label" => t("Code"),
					"options" => [
						"301" => t("301 - Moved Permanently"),
						"302" => t("302 - Found"),
						"303" => t("303 - See Other"),
						"307" => t("307 - Temporary Redirect"),
					],
					"required" => true,
					"value" => ($Redirect ? $Redirect->get("code") : "301"),
				],
				"status" => [
					"type" => "checkbox",
					"label" => t("Active"),
					"value" => ($Redirect ? $Redirect->get("status") : true),
				],
				"actions" => $this->defaultActions(),
			]
		];
	}

}