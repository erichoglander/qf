<?php
class Confirm_Form extends Form {
	
	public function structure() {
		$structure = [
			"name" => "confirm",
			"items" => [
				"text" => [
					"type" => "markup",
					"value" => '
						<div class="form-item confirm-text">
							'.$this->get("text", t("Are you sure you want to continue?")).'
						</div>',
				],
				"actions" => [
					"type" => "actions",
					"items" => [
						"proceed" => [
							"type" => "submit",
							"value" => $this->get("proceed", t("Yes")),
						],
						"cancel" => [
							"type" => "button",
							"value" => $this->get("cancel", t("No")),
							"attributes" => [
								"onclick" => "window.history.go(-1)",
							],
						],
					]
				]
			]
		];
		return $structure;
	}

};