var _intervals = [];
function intervalInit() {
	var els = document.getElementsByClassName("form-interval");
	for (var i=0; i<els.length; i++) {
		if (!els[i].className.match("interval-init")) {
			els[i].addClass("interval-init");
			_intervals.push(new interval(els[i]));
		}
	}
	var observer = new MutationObserver(function(mutations) {
		mutations.forEach(function(mutation) {
			intervalObserve(mutation.target);
		});    
	});
	var config = { childList: true, subtree: true };
	observer.observe(document.body, config);
}
function intervalObserve(el) {
	var els = el.getElementsByClassName("form-interval");
	for (var i=0; i<els.length; i++) {
		if (!els[i].className.match("interval-init")) {
			els[i].addClass("interval-init");
			_intervals.push(new interval(els[i]));
		}
	}
}

function interval(el) {
	
	this.tags = {
		wrap: el
	};
	
	this.init = function() {
		var self = this;
		this.tags.wrap.addClass("interval-init");
		this.tags.item = formGetItem(this.tags.wrap);
		this.suffix = this.tags.wrap.getAttribute("interval_suffix");
		this.min = this.tags.wrap.getAttribute("interval_min");
		this.max = this.tags.wrap.getAttribute("interval_max");
		this.round = this.tags.wrap.getAttribute("interval_round");
		if (this.round.match("."))
			this.round = parseFloat(this.round);
		else
			this.round = parseInt(this.round);
		this.value_follow = false;
		this.min = (this.round%1 ? parseFloat(this.min) : parseInt(this.min));
		this.max = (this.round%1 ? parseFloat(this.max) : parseInt(this.max));
		this.tags.container = this.tags.wrap.getElementsByClassName("interval-slider")[0];
		this.drag_point = null;
		this.drag_pos = 0;
		window.addEventListener("mousemove", function(e){ self.onMouseMove(e); }, false);
		window.addEventListener("mouseup", function(e){ self.onMouseUp(e); }, false);
		window.addEventListener("touchmove", function(e){ self.onMouseMove(e); }, false);
		window.addEventListener("touchend", function(e){ self.onMouseUp(e); }, false);
		this.create();
		if (this.tags.item.hasClass("interval-dropdown")) {
			this.tags.label = this.tags.item.getElementsByClassName("form-label")[0];
			this.tags.label.addEventListener("click", function(){ self.toggle(); }, false);
			window.addEventListener("click", function(e){ self.windowClick(e); }, false);
		}
	}
	
	this.toggle = function() {
		if (this.isOpen())
			this.close();
		else
			this.open();
	}
	this.open = function() {
		this.tags.item.addClass("active");
	}
	this.close = function() {
		this.tags.item.removeClass("active");
	}
	this.isOpen = function() {
		if (this.tags.item.hasClass("active"))
			return true;
		return false;
	}
	
	this.windowClick = function(e) {
		if (!this.isOpen())
			return;
		for (var i=0, el = e.target; i<10 && el != null && el != this.tags.item; i++, el = el.parentNode);
		if (!el || i == 10)
			this.close();
	}
	
	this.onMouseDown = function(e, point) {
		if (point != "min" && point != "max")
			return;
		e.preventDefault();
		this.drag_point = point;
		this.drag_pos = getPos(this.tags.slider.outer);
	}
	this.onMouseUp = function(e) {
		this.drag_point = null;
	}
	this.onMouseMove = function(e) {
		if (!this.drag_point)
			return;
		e.preventDefault();
		var p = this.drag_point;
		var xy = getXY(e);
		var width = this.tags.slider.outer.offsetWidth;
		var x = Math.min(Math.max(xy.x - this.drag_pos.x, 0), width)/width;
		var val = Math.round((x*(this.max-this.min)+this.min)/this.round)*this.round;
		
		if (val == this.tags[p].input.value)
			return;
		
		if (p == "min" && val >= this.getValue("max"))
			val = this.getValue("max")-this.round;
		else if (p == "max" && val <= this.getValue("min"))
			val = this.getValue("min")+this.round;
		
		this.setValue(p, val);
		
		this.lastXY = xy;
	}
	
	this.numberFormat = function(val) {
		if (val < 100)
			return val+this.suffix;
		var str = val.toString();
		var r = "";
		for (var i=0; i<str.length; i++) {
			if (i%3 == str.length%3 && i != 0)
				r+= " ";
			r+= str[i];
		}
		r+= this.suffix;
		return r;
	}
	
	this.getPoint = function(p) {
		return parseFloat(this.tags[p].point.style.left);
	}
	this.setPoint = function(p, value) {
		this.tags[p].point.style.left = value+"%";
	}
	
	this.getValue = function(p) {
		var val = this.tags[p].input.value;
		if (this.round%1)
			val = parseFloat(val);
		else
			val = parseInt(val);
		return val;
	}
	this.setValue = function(p, value) {
		var x = (value-this.min)/(this.max-this.min)*100;
		this.tags[p].input.value = value;
		this.tags[p].text.textContent = this.numberFormat(value);
		this.updatePosition(p);
		this.tags.wrap.trigger("change");
	}
	
	this.updatePositions = function() {
		this.updatePosition("min");
		this.updatePosition("max");
	}
	this.updatePosition = function(p) {
		var value = this.tags[p].input.value;
		var x = (value-this.min)/(this.max-this.min)*100;
		this.setPoint(p, x);
		if (p == "min")
			this.tags.slider.inner.style.left = x+"%";
		else if (p == "max")
			this.tags.slider.inner.style.right = (100-x)+"%";
	}
	
	this.create = function() {
		var self = this;
		var inp = this.tags.wrap.getElementsByTagName("input");
		
		this.tags.min = {
			point: document.createElement("div"),
			text: document.createElement("div"),
			input: inp[0]
		};
		this.tags.min.point.className = "slider-point slider-point-min";
		this.tags.min.text.className = "slider-text slider-text-min";
		var minp = document.createElement("div");
		minp.className = "slider-point-inner";
		this.tags.min.point.appendChild(minp);
		this.tags.min.text.textContent = this.numberFormat(this.tags.min.input.value);
		this.tags.min.point.addEventListener("mousedown", function(e) { self.onMouseDown(e, "min"); }, false);
		this.tags.min.point.addEventListener("touchstart", function(e) { self.onMouseDown(e, "min"); }, false);
		
		this.tags.max = {
			point: document.createElement("div"),
			text: document.createElement("div"),
			input: inp[1]
		};
		this.tags.max.point.className = "slider-point slider-point-max";
		this.tags.max.text.className = "slider-text slider-text-max";
		var maxp = document.createElement("div");
		maxp.className = "slider-point-inner";
		this.tags.max.point.appendChild(maxp);
		this.tags.max.text.textContent = this.numberFormat(this.tags.max.input.value);
		this.tags.max.point.addEventListener("mousedown", function(e) { self.onMouseDown(e, "max"); }, false);
		this.tags.max.point.addEventListener("touchstart", function(e) { self.onMouseDown(e, "max"); }, false);
		
		this.tags.slider = {
			outer: document.createElement("div"),
			inner: document.createElement("div")
		};
		this.tags.slider.outer.className = "slider-outer";
		this.tags.slider.inner.className = "slider-inner";
		
		this.updatePositions();
		
		this.tags.slider.outer.appendChild(this.tags.slider.inner);
		this.tags.slider.outer.appendChild(this.tags.min.point);
		this.tags.slider.outer.appendChild(this.tags.max.point);
		if (this.value_follow) {
			this.tags.min.point.appendChild(this.tags.min.text);
			this.tags.container.appendChild(this.tags.slider.outer);
			this.tags.max.point.appendChild(this.tags.max.text);
		}
		else {
			this.tags.container.appendChild(this.tags.min.text);
			this.tags.container.appendChild(this.tags.slider.outer);
			this.tags.container.appendChild(this.tags.max.text);
		}
	}
	
	this.init();
	
}

window.addEventListener("load", intervalInit, false);