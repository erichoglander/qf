<?php
namespace JsonToHtml {
	
	function htmlToJson($html) {
		$json = [];
		$stack = [];
		$i = -1;
		$singleTags = ["input", "img", "br", "hr", "link", "meta", "area", "base", "col", "frame", "param", "isindex", "basefont"];
		while (true) {
			$x = strpos($html, "<");
			// Element
			if ($x === 0) {
				$x = strpos($html, ">");
				if ($x === false)
					return null;
				$tag = substr($html, 0, $x+1);
				if ($tag[1] == "/") {
					$el = array_pop($stack);
					$i--;
					if ($i == -1) {
						$json[] = $el;
					}
					else {
						$stack[$i]["children"][] = $el;
					}
				}
				else {
					$stack[++$i] = [
						"tagName" => preg_replace("/^\<\s*([a-z0-9]+).*$/si", "$1", $tag),
					];
					preg_match_all("/([a-z0-9]+)\=\"([^\"]*)\"/i", $tag, $matches); // attr="value"
					preg_match_all("/([a-z0-9]+)\=\'([^\']*)\'/i", $tag, $matches2); // attr='value'
					preg_match_all("/\ ([a-z0-9+]+)[\ |\>]/i", $tag, $matches3); // attr
					$matches[1]+= $matches2[1];
					$matches[2]+= $matches2[2];
					if (!empty($matches[1])) {
						$stack[$i]["attributes"] = [];
						foreach ($matches[1] as $j => $key) {
							$key = strtolower($key);
							$value = (array_key_exists($j, $matches[2]) ? $matches[2][$j] : null);
							if ($key == "style") {
								preg_match_all("/([a-z0-9\-\_]+)\s*\:\s*([a-z0-9\-\_\#\(\)\,\.\ ]+)\s*/is", $value, $styles);
								if (!empty($styles)) {
									$stack[$i]["style"] = [];
									foreach ($styles[1] as $k => $style) 
										$stack[$i]["style"][$k] = $styles[2][$k];
								}
							}
							else {
								$stack[$i]["attributes"][$key] = $value;
							}
						}
					}
					if (in_array($stack[$i]["tagName"], $singleTags)) {
						$el = array_pop($stack);
						$i--;
						if ($i == -1) {
							$json[] = $el;
						}
						else {
							$stack[$i]["children"][] = $el;
						}
					}
				}
				$html = substr($html, $x+1);
			}
			else if ($x === false) {
				$value = str_replace('"', '&quot;', $html);
				if ($i == -1)
					$json[] = $value;
				else
					$stack[$i]["children"][] = $value;
				break;
			}
			else {
				$value = str_replace('"', '&quot;', substr($html, 0, $x));
				if ($i == -1)
					$json[] = $value;
				else
					$stack[$i]["children"][] = $value;
				$html = substr($html, $x);
			}
		}
		return $json;
	}

}