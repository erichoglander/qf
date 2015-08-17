<?php
class Editor_FormItem_Core extends FormItem {

	protected function filter($value, $filter) {
		return preg_replace("/<\s*script[^>]+>[^<]+<\s*\/script\s*>/i", "", $value);
	}

}