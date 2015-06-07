var _timepickers = [];
function timepickerInit() {
	var els = document.getElementsByClassName("form-time");
	for (var i=0; i<els.length; i++)
		_timepickers.push(new timepicker(els[i]));
	var observer = new MutationObserver(function(mutations) {
		mutations.forEach(function(mutation) {
			timepickerObserve(mutation.target);
		});    
	});
	var config = { childList: true, subtree: true };
	observer.observe(document.body, config);
}
function timepickerObserve(el) {
	var els = el.getElementsByClassName("form-time");
	for (var i=0; i<els.length; i++) {
		if (!els[i].className.match("timepicker-init"))
			_timepickers.push(new timepicker(els[i]));
	}
}

function timepicker(el) {
	
	this.tags = {
		wrap: el.parentNode,
		input: el,
	};
	this.value = {h: 0, m: 0};

	this.init = function() {
		var self = this;
		this.tags.wrap.addClass("timepicker-init");
		this.tags.input.addEventListener("click", function(){ self.toggle(); }, false);
		if (this.tags.input.value.length) {
			var arr = this.tags.input.value.split(":");
			this.value.h = arr[0];
			this.value.m = arr[1];
		}
		this.render();
	}

	this.render = function() {
		this.tags.picker = document.createElement("div");
		this.tags.picker.className = "timepicker";
		var inner = document.createElement("div");
		inner.className = "timepicker-inner";
		var hours = document.createElement("div");
		hours.className = "timepicker-hours";
		var minutes = document.createElement("div");
		minutes.className = "timepicker-minutes";
		this.tags.hours = [];
		this.tags.minutes = [];
		for (var i=1; i<=24; i++) {
			this.tags.hours[i] = document.createElement("div");
			this.tags.hours[i].className = "timepicker-hour timepicker-hour-"+i;
			this.tags.hours[i].textContent = i;
			var x = (i > 12 ? i-12 : i);
			var d = (i > 12 ? 40 : 28);
			var a = 2 * Math.PI * x / 12;
			var x = 50 + d * Math.sin(a);
			var y = 50 + d * Math.cos(a);
			this.tags.hours[i].style.left = x+"%";
			this.tags.hours[i].style.top = (100-y)+"%";
			hours.appendChild(this.tags.hours[i]);
			(function(self, n) {
				self.tags.hours[i].addEventListener("click", function(){ self.hourClick(n); }, false);
			}(this, i));
		}
		for (var i=0; i<60; i++) {
			this.tags.minutes[i] = document.createElement("div");
			this.tags.minutes[i].className = "timepicker-minute timepicker-minute-"+i;
			if (i%5 == 0)
				this.tags.minutes[i].className+= " timepicker-minute-five";
			this.tags.minutes[i].textContent = i;
			var d = 40;
			var a = 2 * Math.PI * i / 60;
			var x = 50 + d * Math.sin(a);
			var y = 50 + d * Math.cos(a);
			this.tags.minutes[i].style.left = x+"%";
			this.tags.minutes[i].style.top = (100-y)+"%";
			minutes.appendChild(this.tags.minutes[i]);
			(function(self, n) {
				self.tags.minutes[i].addEventListener("click", function(){ self.minuteClick(n); }, false);
			}(this, i));
		}
		inner.appendChild(hours);
		inner.appendChild(minutes);
		this.tags.picker.appendChild(inner);
		this.tags.wrap.appendChild(this.tags.picker);
	}

	this.renderValue = function() {
		this.tags.input.value = this.addZero(this.value.h)+":"+this.addZero(this.value.m);
	}

	this.addZero = function(val) {
		if (val < 10)
			return "0"+val;
		return val;
	}

	this.toggle = function() {
		if (this.isOpen())
			this.close();
		else
			this.open();
	}
	this.isOpen = function() {
		return (this.tags.wrap.className.match("timepicker-open") ? true : false);
	}
	this.open = function() {
		this.tags.wrap.addClass("timepicker-open");
		this.hourOpen();
	}
	this.close = function() {
		this.tags.wrap.removeClass("timepicker-open");
		this.tags.wrap.removeClass("timepicker-hour-open");
		this.tags.wrap.removeClass("timepicker-minute-open");
	}

	this.hourOpen = function() {
		this.tags.wrap.removeClass("timepicker-minute-open");
		this.tags.wrap.addClass("timepicker-hour-open");
	}
	this.minuteOpen = function() {
		this.tags.wrap.removeClass("timepicker-hour-open");
		this.tags.wrap.addClass("timepicker-minute-open");
	}

	this.hourClick = function(h) {
		this.setHour(h);
		this.minuteOpen();
	}
	this.minuteClick = function(m) {
		this.setMinute(m);
		this.close();
	}

	this.setHour = function(h) {
		this.value.h = h;
		this.renderValue();
	}
	this.setMinute = function(m) {
		this.value.m = m;
		this.renderValue();
	}

	this.init();

}

window.addEventListener("load", timepickerInit, false);