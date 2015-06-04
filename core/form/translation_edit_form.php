<?php
class TranslationEdit_Form_Core extends Form {
	
	protected function structure() {
		$languages = $this->get("languages");
		$translation = $this->get("translation");
		$structure = [
			"name" => "translation_edit",
			"items" => [
				"source" => [
					"type" => "textarea",
					"label" => $languages[$translation->lang]->title,
					"value" => xss($translation->text),
					"attributes" => [
						"disabled" => true,
					],
				],
			],
		];
		$i = 0;
		foreach ($languages as $language) {
			if ($language->lang != $translation->lang) {
				$structure["items"][$language->lang] = [
					"type" => "textarea",
					"label" => $language->title,
					"value" => (isset($translation->translations[$language->lang]) ? $translation->translations[$language->lang]->text : null),
					"focus" => ($i == 0),
					"filter" => "trim",
				];
				$i++;
			}
		}
		$structure["items"]["actions"] = $this->defaultActions();
		return $structure;
	}

}