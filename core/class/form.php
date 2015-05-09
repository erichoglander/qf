<?php
/*
	Form class

	Goal is to handle the following:
		ajax
		nested items
		multiple items with an add/delete buttons

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
							"pets" => [
								"type" => "text",
								"label" => "Pets",
								"value" => [
									"Mr. whiskers",
									"Fluffy",
								],
								"multiple" => true,
								"add_button" => "Add pet",
								"delete_button" => "Remove pet",
							],
							"siblings" => [
								"type" => "container",
								"label" => "Siblings",
								"items" => [
									"name" => [
										"type" => "text",
										"label" => "Name",
									],
									"age" => [
										"type" => "text",
										"label" => "Age",
									],
								],
								"value" => [
									[
										"name" => "John",
										"age" => 18,
									],[
										"name" => "Sarah",
										"age" => 16,
									],[
										"name" => "Jennifer",
										"age" => 13,
									],
								],
								"multiple" => true,
								"add_button" => "Add sibling",
								"delete_button" => "Remove sibling",
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
			<div class="form-container form-items form-root-container">
				<div class="form-item form-type-text form-item-name form-item-required">
					<label for="name" class="form-label">
						Name
					</label>
					<div class="form-container form-inputs">
						<div class="form-input">
							<input type="text" name="name" placeholder="Enter your name" class="form-text">
							<div class="form-input-error">Field is required</div>
						</div>
					</div>
					<div class="form-description">
						Your full legal name
					</div>
				</div>
				<div class="form-item form-type-container form-item-family">
					<label class="form-label">Family</label>
					<div class="form-container form-items">
						<div class="form-item form-type-text form-item-pets">
							<label class="form-label" for="pets">
								Pets
							</label>
							<div class="form-container form-inputs">
								<div class="form-input">
									<input type="text" name="pets[0]" class="form-text" value="Mr. whiskers">
									<input type="button" value="Delete item" class="form-button form-delete-button" onclick="formDeleteItem(this)">
								</div>
								<div class="form-input">
									<input type="text" name="pets[1]" class="form-text" value="Fluffy">
									<input type="button" value="Delete item" class="form-button form-delete-button" onclick="formDeleteItem(this)">
								</div>
							</div>
							<input type="button" value="Add item" class="form-button form-add-button" onclick="formAddItem(this)">
						</div>
						<div class="form-item form-type-text form-item-siblings form-item-multiple">
							<label class="form-label" for="family[siblings][0][name]">
								Siblings
							</label>
							<div class="form-container form-items">
								<div class="form-item form-type-text form-item-name">
									<label class="form-label" for="family[siblings][0][name]">
									<div class="form-container form-inputs">
										<div class="form-input">
											<input type="text" name="family[siblings][0][name]" value="John" class="form-text">
										</div>
									</div>
								</div>
								<div class="form-item form-type-text form-item-age">
									<label class="form-label" for="family[siblings][0][age]">
									<div class="form-container form-inputs">
										<div class="form-input">
											<input type="text" name="family[siblings][0][age]" value="18" class="form-text">
										</div>
									</div>
								</div>
								<input type="button" value="Delete item" class="form-button form-delete-button" onclick="formDeleteItem(this)">
							</div>
							<div class="form-container form-items">
								<div class="form-item form-type-text form-item-name">
									<label class="form-label" for="family[siblings][1][name]">
									<div class="form-container form-inputs">
										<div class="form-input">
											<input type="text" name="family[siblings][1][name]" value="Sarah" class="form-text">
										</div>
									</div>
								</div>
								<div class="form-item form-type-text form-item-age">
									<label class="form-label" for="family[siblings][1][age]">
									<div class="form-container form-inputs">
										<div class="form-input">
											<input type="text" name="family[siblings][1][age]" value="16" class="form-text">
										</div>
									</div>
								</div>
								<input type="button" value="Delete item" class="form-button form-delete-button" onclick="formDeleteItem(this)">
							</div>
							<div class="form-container form-items">
								<div class="form-item form-type-text form-item-name">
									<label class="form-label" for="family[siblings][2][name]">
									<div class="form-container form-inputs">
										<div class="form-input">
											<input type="text" name="family[siblings][2][name]" value="Jennifer" class="form-text">
										</div>
									</div>
								</div>
								<div class="form-item form-type-text form-item-age">
									<label class="form-label" for="family[siblings][2][age]">
									<div class="form-container form-inputs">
										<div class="form-input">
											<input type="text" name="family[siblings][2][age]" value="13" class="form-text">
										</div>
									</div>
								</div>
								<input type="button" value="Delete item" class="form-button form-delete-button" onclick="formDeleteItem(this)">
							</div>
							<input type="button" value="Add item" class="form-button form-add-button" onclick="formAddItem(this)">
						</div>
					</div>
				</div>
				<div class="form-item form-type-actions">
					<div class="form-container form-items">
						<div class="form-item form-type-submit">
							<div class="form-container form-inputs">
								<div class="form-input">
									<input type="submit" value="Send">
								</div>
							</div>
						</div>
						<div class="form-item form-type-button">
							<div class="form-container form-inputs">
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

	public function render() {
		$path = $this->templatePath();
		$items = $this->renderItems();
		$errors = $this->getErrors();
		$attributes = $this->attributes();
		include $path;
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

	protected	function getAttributes() {
		$attr = [];
		foreach ($this->attributes as $key => $val)
			$attr[$key] = $val;
		$class = cssClass("form-".$this->name);
		if (empty($attr['class']))
			$attr['class'] = $class;
		else
			$attr['class'].= " ".$class;
		return $attr;
	}
	protected function attributes($attributes = null) {
		if (!$attributes)
			$attributes = $this->getAttributes();
		$attr = "";
		foreach ($attributes as $key => $val)
			$attr.= $key.'="'.$val.'" ';
		$attr = substr($attr, 0, -1);
		return $attr;
	}

	protected function templatePath() {
		$epath = DOC_ROOT."/extend/template/form/form.php";
		$cpath = DOC_ROOT."/core/template/form/form.php";
		if (file_exists($epath))
			return $path;
		if (file_exists($cpath))
			return $path;
		return null;
	}

	protected function renderItems() {
		$items = [];
		foreach ($this->items as $name => $item)
			$items[] = $item->render($name);
		return $items;
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