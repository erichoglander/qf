var _checkboxes_selects = [];
function checkboxesSelectInit() {
	var els = document.getElementsByClassName("checkboxes-select");
	for (var i=0; i<els.length; i++)
		_checkboxes_selects.push(new checkboxesSelect(els[i]));
	var observer = new MutationObserver(function(mutations) {
		mutations.forEach(function(mutation) {
			checkboxes_selectObserve(mutation.target);
		});    
	});
	var config = { childList: true, subtree: true };
	observer.observe(document.body, config);
}
function checkboxesSelectObserve(el) {
	var els = el.getElementsByClassName("checkboxes-select");
	for (var i=0; i<els.length; i++) {
		if (!els[i].className.match("checkboxes-select-init"))
			_checkboxes_selects.push(new checkboxesSelect(els[i]));
	}
}

function checkboxesSelect(el) {
	
	this.tags = {
		wrap: el
	};
	
	this.init = function() {
		this.tags.title = this.tags.wrap.getElementsByClassName("checkboxes-select-title-inner")[0];
		var checkboxes = this.tags.wrap.getElementsByTagName("input");
		this.tags.checkboxes = {};
		for (var i=0; i<checkboxes.length; i++)
			this.tags.checkboxes[checkboxes[i].value] = checkboxes[i];
		this.tags.wrap.addClass("checkboxes-select-init");
	}
	
	this.init();
	
}

window.addEventListener("load", checkboxesSelectInit, false);