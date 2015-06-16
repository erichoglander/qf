<?php
class Address_FormItem_Core extends FormItem {
	
	protected $address_fields = ["line", "postal_code", "locality", "country"];
	protected $address_countries;
	
	
	public function countries() {
		if (empty($this->address_countries))
			return countryList();
		return $this->address_countries;
	}
	
	
	protected function loadStructure($structure) {
		$structure["items"] = [];
		if (in_array("line", $this->address_fields)) {
			$structure["items"]["line"] = [
				"type" => "text",
				"label" => t("Address"),
				"required" => true,
			];
		}
		if (in_array("postal_code", $this->address_fields)) {
			$structure["items"]["postal_code"] = [
				"type" => "text",
				"label" => t("Postal code"),
				"required" => true,
			];
		}
		if (in_array("locality", $this->address_fields)) {
			$structure["items"]["locality"] = [
				"type" => "text",
				"label" => t("Locality"),
				"required" => true,
			];
		}
		if (in_array("country", $this->address_fields)) {
			$structure["items"]["country"] = [
				"type" => "select",
				"label" => t("Country"),
				"options" => $this->countries(),
				"required" => true,
			];
		}
		parent::loadStructure($structure);
	}
	
}