<?php
class Editor_FormItem extends FormItem {

	protected function filter($value, $filter) {
		return preg_replace("/<\s*script[^>]+>[^<]+<\s*\/script\s*>/i", "", $value);
	}

}