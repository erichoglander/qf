<?php
/*
	class UserLogin_Form extends Form {

		public function structure() {
			
			$structure = [
				"items" => [
					"name" => [
						"type" => "text",
						"label" => "Name",
						"attributes" => [
							"placeholder" => "Enter your name",
						],
						"description" => "Your full legal name",
						"required" => true,
					],
					"family" => [
						"type" => "container",
						"label" => "Family",
						"items" => [
							"siblings" => [
								"type" => "text",
								"label" => "Siblings",
								"multiple" => true,
								"add_button" => "Add item",
								"delete_button" => "Delete item",
							],
						],
					],
					"actions" => [
						"type" => "actions",
						"items" => [
							"submit" => [
								"type" => "submit",
								"value" => "Send",
							],
							"cancel" => [
								"type" => "button",
								"value" => "Cancel",
								"attributes" => [
									"onclick" => "window.history.go(-1)",
								],
							],
						],
					],
				],
			];

			return $structure;
		}

	};

	<div class="form-wrapper">
		<form action="" method="POST" class="form">
			<div class="form-items">
				<div class="form-item form-type-text form-item-required">
					<label for="name" class="form-label">
						Name
					</label>
					<div class="form-inputs">
						<div class="form-input">
							<input type="text" name="name" placeholder="Enter your name" class="form-text">
							<div class="form-input-error">Field is required</div>
						</div>
					</div>
					<div class="form-description">
						Your full legal name
					</div>
				</div>
				<div class="form-item form-type-container">
					<label class="form-label">Family</label>
					<div class="form-items">
						<div class="form-item form-type-text">
							<label class="form-label" for="siblings[]">
								Siblings
							</label>
							<div class="form-inputs">
								<div class="form-input">
									<input type="text" name="siblings[]" class="form-text">
								</div>
								<div class="form-input">
									<input type="text" name="siblings[]" class="form-text">
									<input type="button" value="Delete item" class="form-button form-delete-button" onclick="formDeleteItem(this)">
								</div>
							</div>
							<input type="button" value="Add item" class="form-button form-add-button" onclick="formAddItem(this)">
						</div>
					</div>
				</div>
				<div class="form-item form-type-actions">
					<div class="form-items">
						<div class="form-item form-type-submit">
							<div class="form-inputs">
								<div class="form-input">
									<input type="submit" value="Send">
								</div>
							</div>
						</div>
						<div class="form-item form-type-button">
							<div class="form-inputs">
								<div class="form-input">
									<input type="button" value="Cancel" onclick="window.history.go(-1)">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</form>
	</div>
*/
class Form_Core {
	
	protected $attributes = [
		"name" => "default_form",
		"method" => "POST",
		"action" => "",
		"class" => "form"
	];
	protected $items;
	protected $errors = [];

	public function __construct() {
		$this->loadStructure();
	}

	public function setError($msg) {
		$this->errors[] = $msg;
	}
	public function getErrors() {
		return $this->errors;
	}

	public function values() {
		$values = [];
		foreach ($this->items as $name => $item)
			$values[$name] = $item->value();
		return $values;
	}


	protected function structure() {
		return [
			"name" => "default_form",
			"attributes" => [
				"method" => "POST",
				"action" => "",
				"class" => "form",
			],
		];
	}

	protected function loadStructure() {
		$structure = $this->structure();
		if (!empty($structure['attributes'])) {
			foreach ($structure['attributes'] as $key => $val)
				$this->attributes[$key] = $val;
		}
		foreach ($structure['items'] as $name => $item) 
			$this->loadItem($name, $item);
	}

	protected function loadItem($name, $item) {
		if (empty($item['type']))
			throw new Exception("No type given for form item ".$name);
		$a = explode("_", $item['type']);
		$class = "";
		foreach ($a as $b)
			$class.= ucwords($b)."_FormItem";
		if (!class_exists($class))
			throw new Exception("Class not found for form type ".$item['type']);
		$item['name'] = $name;
		$item['full_name'] = $this->inputName().$name;
		$this->items[$name] = new $class($item);
	}

	protected function validate() {
		if (!empty($this->errors))
			return false;
		foreach ($this->items as $item) {
			if (!$this->validate())
				return false;
		}
		return true;
	}

};