<?php
/**
 * Contains the natural form class
 */

/**
 * Natural form class
 * @author NaturalForm
 */
class NaturalForm extends Form {
	
	/**
	 * HTML content of the form button
	 * @var string
	 */
	protected $nf_button;

	/**
	 * Whether to display a progress bar
	 * @var bool
	 */
	protected $nf_progress = true;
	
	
	/**
	 * Add attributes to the form
	 * @return array
	 */
	protected function getAttributes() {
		$arr = parent::getAttributes();
		$arr["class"].= " natural-form";
		return $arr;
	}
	
	/**
	 * Manipulates the form structure
	 */
	protected function loadStructure() {
		parent::loadStructure();
		
		$e = false;
		$current = 1;
		$n = count($this->items);
		if ($this->isSubmitted(false)) {
			foreach ($this->items as $item) {
				if (!$item->validated()) {
					$e = true;
					$item->item_class.= " active";
				}
				if (!$e)
					$current++;
			}
		}
		if (!$e) {
			$current = 1;
			foreach ($this->items as $item) {
				$item->item_class.= " active";
				break;
			}
		}
		
		$items = [];
		$items["nf_button"] = [
			"type" => "markup",
			"value" => '<div class="nf-button">'.$this->nf_button.'</div>',
		];
		$items["nf_submit"] = [
			"type" => "submit",
			"value" => "",
			"prefix" => '<div style="display: none">',
			"suffix" => '</div>',
		];
		if ($this->nf_progress) {
			$items["nf_progress"] = [
				"type" => "markup",
				"value" => '
					<div class="progress-wrap">
						<div class="progress-bar"><div class="progress"></div></div>
						<div class="progress-current"><span class="current">'.$current.'</span>/'.$n.'</div>
					</div>',
			];
		}
		foreach ($items as $name => $item)
			$this->loadItem($name, $item);
	}
	
}