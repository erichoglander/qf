var _datepickers = [];
function datepickerInit() {
	var els = document.getElementsByClassName("form-date");
	for (var i=0; i<els.length; i++)
		_datepickers.push(new datepicker(els[i]));
	var observer = new MutationObserver(function(mutations) {
		mutations.forEach(function(mutation) {
			timepickerObserve(mutation.target);
		});    
	});
	var config = { childList: true, subtree: true };
	observer.observe(document.body, config);
}
function datepickerObserve(el) {
	var els = el.getElementsByClassName("form-date");
	for (var i=0; i<els.length; i++) {
		if (!els[i].className.match("datepicker-init"))
			_datepickers.push(new datepicker(els[i]));
	}
}

function datepicker(el) {
	
	this.tags = {
		wrap: el.parentNode,
		input: el,
	};
	this.now = new Date();
	this.value = {y: this.now.getFullYear(), m: this.now.getMonth()+1, d: 0};
	this.months = [
		"January", "February", "March", 
		"April", "May", "June", 
		"July", "August", "September", 
		"October", "November", "December",
	];

	this.init = function() {
		var self = this;
		this.tags.wrap.addClass("datepicker-init");
		this.tags.input.addEventListener("click", function(){ self.toggle(); }, false);
		window.addEventListener("click", function(e){ self.windowClick(e); }, false);
		if (this.tags.input.value.length) {
			var arr = this.tags.input.value.split("-");
			this.value.y = arr[0];
			this.value.m = arr[1];
			this.value.d = arr[2];
		}
		this.render();
	}

	this.render = function() {
		this.tags.picker = document.createElement("div");
		this.tags.picker.className = "datepicker";
		var arrow = document.createElement("div");
		arrow.className = "arrow";
		var inner = document.createElement("div");
		inner.className = "datepicker-inner";
		this.tags.prev = document.createElement("div");
		this.tags.prev.className = "datepicker-pager datepicker-prev";
		this.tags.prev.appendChild(FontAwesome.icon("angle-left"));
		this.tags.next = document.createElement("div");
		this.tags.next.className = "datepicker-pager datepicker-next";
		this.tags.next.appendChild(FontAwesome.icon("angle-right"));
		var selects = document.createElement("div");
		selects.className = "datepicker-selects";
		var months = document.createElement("div");
		months.className = "datepicker-select datepicker-select-months";
		this.tags.month = document.createElement("div");
		this.tags.month.className = "datepicker-selected";
		var month_options = document.createElement("div");
		month_options.className = "datepicker-options";
		if (this.value.m)
			this.tags.month.textContent = this.months[this.value.m-1];
		this.tags.months = {};
		for (var i=1; i<=12; i++) {
			this.tags.months[i] = document.createElement("div");
			this.tags.months[i].className = "datepicker-option";
			this.tags.months[i].textContent = this.months[i-1];
			month_options.appendChild(this.tags.months[i]);
		}
		var years = document.createElement("div");
		years.className = "datepicker-select datepicker-select-years";
		this.tags.year = document.createElement("div");
		this.tags.year.className = "datepicker-selected";
		if (this.value.y)
			this.tags.year.textContent = this.value.y;
		var year_options = document.createElement("div");
		year_options.className = "datepicker-options";
		this.tags.years = {};
		var stop = this.now.getFullYear()+20;
		for (var i=1900; i<=stop; i++) {
			this.tags.years[i] = document.createElement("div");
			this.tags.years[i].className = "datepicker-option";
			this.tags.years[i].textContent = i;
			year_options.appendChild(this.tags.years[i]);
		}
		months.appendChild(month_options);
		months.appendChild(this.tags.month);
		years.appendChild(year_options);
		years.appendChild(this.tags.year);
		selects.appendChild(months);
		selects.appendChild(years);
		inner.appendChild(selects);
		inner.appendChild(this.tags.prev);
		inner.appendChild(this.tags.next);
		this.tags.picker.appendChild(arrow);
		this.tags.picker.appendChild(inner);
		this.tags.wrap.appendChild(this.tags.picker);
	}

	this.toggle = function() {
		if (this.isOpen())
			this.close();
		else
			this.open();
	}
	this.isOpen = function() {
		return (this.tags.wrap.className.match("datepicker-open") ? true : false);
	}
	this.open = function() {
		this.tags.wrap.addClass("datepicker-open");
	}
	this.close = function() {
		this.tags.wrap.removeClass("datepicker-open");
	}

	this.windowClick = function(e) {
		var el = e.target;
		for (var i=0; el  && i<8; el = el.parentNode, i++) {
			if (el == this.tags.wrap)
				return;
		}
		this.close();
	}

	this.init();

}

window.addEventListener("load", datepickerInit, false);