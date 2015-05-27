function jsonToHtml(parent, json, replace) {
	if (replace)
		parent.innerHTML = "";
	if (typeof(json) == "object") {
		// Array
		if (typeof(json.length) != "undefined") {
			for (var i in json)
				jsonToHtml(parent, json[i]);
		}
		// Object
		else {
			if (json.tagName) {
				var el = document.createElement(json.tagName);
				if (json.style) {
					for (var key in json.style) {
						var k = key;
						if (k[0] == "-")
							k = k.substr(1);
						// snake-case to snakeCase
						k = k.replace(/(\-\w)/g, function(m){return m[1].toUpperCase();});
						el.style[k] = json.style[key];
					}
				}
				if (json.attributes) {
					for (var attr in json.attributes) {
						if (json.attributes[attr] !== null)
							el.setAttribute(attr, json.attributes[attr]);
					}
				}
				if (json.children) {
					for (var i in json.children)
						jsonToHtml(el, json.children[i]);
				}
				parent.appendChild(el);
			}
			else if (json.script) {
				eval(json.script);
			}
		}
	}
	else {
		parent.appendChild(document.createTextNode(json));
	}
}