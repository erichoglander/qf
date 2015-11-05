<?php
class Address_FormItem_Core extends FormItem {
	
	public $address_fields = ["line", "postal_code", "locality", "country"];
	public $address_country = "SE";
	public $address_countries;
	public $label_placeholder = false;
	
	
	public function countries() {
		return countryList();
	}

	public function hasValue() {
		if ($this->multiple) {
			foreach ($this->items as $item) 
				if ($item->hasValue())
					return true;
		}
		else {
			foreach ($this->items as $item) 
				if ($item->hasValue() && $item->name != "country")
					return true;
		}
		return false;
	}
	
	
	protected function loadStructure($structure) {
		if (isset($structure["address_fields"]))
			$this->address_fields = $structure["address_fields"];
		if (isset($structure["address_countries"]))
			$this->address_countries = $structure["address_countries"];
		else
			$this->address_countries = $this->countries();
		if (isset($structure["label_placeholder"]))
			$this->label_placeholder = $structure["label_placeholder"];
		
		$structure["items"] = [];
		if (in_array("id", $this->address_fields)) {
			$structure["items"]["id"] = [
				"type" => "value",
			];
		}
		if (in_array("line", $this->address_fields)) {
			$structure["items"]["line"] = [
				"type" => "text",
				"label" => t("Address"),
				"filter" => ["strip_tags", "trim"],
				"required" => true,
			];
		}
		if (in_array("line2", $this->address_fields)) {
			$structure["items"]["line2"] = [
				"type" => "text",
				"label" => t("Address 2"),
				"filter" => ["strip_tags", "trim"],
			];
		}
		if (in_array("postal_code", $this->address_fields)) {
			$structure["items"]["postal_code"] = [
				"type" => "text",
				"label" => t("Postal code"),
				"filter" => ["strip_tags", "trim"],
				"required" => true,
			];
		}
		if (in_array("locality", $this->address_fields)) {
			$structure["items"]["locality"] = [
				"type" => "text",
				"label" => t("Locality"),
				"filter" => ["strip_tags", "trim"],
				"required" => true,
			];
		}
		if (in_array("country", $this->address_fields)) {
			$structure["items"]["country"] = [
				"type" => "select",
				"label" => t("Country"),
				"options" => $this->address_countries,
				"value" => $this->address_country,
				"required" => true,
			];
		}
		if ($this->label_placeholder) {
			foreach ($structure["items"] as $key => $item) {
				if ($item["type"] == "select") {
					$structure["items"][$key]["empty_option"] = "- ".$item["label"]." -";
				}
				else {
					$structure["items"][$key]["attributes"]["placeholder"] = $item["label"];
				}
				unset($structure["items"][$key]["label"]);
			}
		}
		parent::loadStructure($structure);
	}
	
}