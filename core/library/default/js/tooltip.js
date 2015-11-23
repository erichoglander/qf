var _tooltips = [];
function tooltipInit() {
	var els = document.getElementsByClassName("tooltip");
	for (var i=0; i<els.length; i++)
		_tooltips.push(new tooltip(els[i]));
	if (typeof(MutationObserver) == "function") {
		var observer = new MutationObserver(function(mutations) {
			mutations.forEach(function(mutation) {
				tooltipInit2(mutation.target);
			});
		});
		var config = { childList: true, subtree: true };
		observer.observe(document.body, config);
	}
	else {
		setInterval(function() {
			tooltipInit2(document.body);
		}, 1000);
	}
}
function tooltipInit2(el) {
	var els = el.getElementsByClassName("tooltip");
	for (var i=0; i<els.length; i++) {
		if (!els[i].className.match("tooltip-init"))
			_tooltips.push(new tooltip(els[i]));
	}
}

function tooltip(el) {

	this.tags = {
		parent: el
	};

	this.init = function() {
		var self = this;
		this.text = el.getAttribute("tooltip");
		this.create();
	}

	this.create = function() {
		this.tags.tip = document.createElement("div");
		this.tags.tip.className = "tooltip-tip";
		var arrow = document.createElement("div");
		arrow.className = "tooltip-arrow";
		var content = document.createElement("div");
		content.className = "tooltip-content";
		var inner = document.createElement("div");
		inner.className = "tooltip-inner";
		inner.innerHTML = this.text;
		content.appendChild(inner);
		this.tags.tip.appendChild(content);
		this.tags.tip.appendChild(arrow);
		this.tags.parent.appendChild(this.tags.tip);
		this.tags.parent.addClass("tooltip-init");
	}

	this.init();

}

window.addEventListener("load", tooltipInit, false);
