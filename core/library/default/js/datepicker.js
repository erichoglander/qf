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
	this.active = {y: this.value.y, m: this.value.m};
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
		var self = this;
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
		this.tags.month.textContent = this.months[this.active.m-1];
		this.tags.month.addEventListener("click", function(){ self.selectToggle("month"); }, false);
		var month_options = document.createElement("div");
		month_options.className = "datepicker-options";
		this.tags.months = {};
		for (var i=1; i<=12; i++) {
			this.tags.months[i] = document.createElement("div");
			this.tags.months[i].className = "datepicker-option";
			this.tags.months[i].textContent = this.months[i-1];
			month_options.appendChild(this.tags.months[i]);
			(function(n) {
				self.tags.months[n].addEventListener("click", function(){ self.chooseMonth(n); }, false);
			}(i));
		}
		var years = document.createElement("div");
		years.className = "datepicker-select datepicker-select-years";
		this.tags.year = document.createElement("div");
		this.tags.year.className = "datepicker-selected";
		this.tags.year.textContent = this.active.y;
		this.tags.year.addEventListener("click", function(){ self.selectToggle("year"); }, false);
		var year_options = document.createElement("div");
		year_options.className = "datepicker-options";
		this.tags.years = {};
		var stop = this.now.getFullYear()+20;
		for (var i=stop; i>=1900; i--) {
			this.tags.years[i] = document.createElement("div");
			this.tags.years[i].className = "datepicker-option";
			this.tags.years[i].textContent = i;
			year_options.appendChild(this.tags.years[i]);
			(function(n) {
				self.tags.years[n].addEventListener("click", function(){ self.chooseYear(n); }, false);
			}(i));
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
	
	this.renderCal = function() {
		
	}
	
	this.selectToggle = function(key) {
		if (this.tags[key].parentNode.className.match("active"))
			this.selectClose(key);
		else
			this.selectOpen(key);
	}
	this.selectOpen = function(key) {
		this.tags[key].parentNode.addClass("active");
	}
	this.selectClose = function(key) {
		this.tags[key].parentNode.removeClass("active");
	}
	
	this.chooseMonth = function(m) {
		this.active.m = m;
		this.selectClose("month");
		this.tags.month.textContent = this.months[m-1];
		this.renderCal();
	}
	this.chooseYear = function(y) {
		this.active.y = y;
		this.selectClose("year");
		this.tags.year.textContent = y;
		this.renderCal();
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