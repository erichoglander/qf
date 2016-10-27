var _datepickers = [];
function datepickerInit() {
  var els = document.getElementsByClassName("form-date");
  for (var i=0; i<els.length; i++) {
    if (!els[i].className.match("datepicker-init")) {
      els[i].addClass("datepicker-init");
      _datepickers.push(new datepicker(els[i]));
    }
  }
  if (typeof(MutationObserver) == "function") {
    var observer = new MutationObserver(function(mutations) {
      mutations.forEach(function(mutation) {
        datepickerObserve(mutation.target);
      });    
    });
    var config = { childList: true, subtree: true };
    observer.observe(document.body, config);
  }
  else {
    setInterval(function() {
      datepickerObserve(document.body);
    }, 1000);
  }
}
function datepickerObserve(el) {
  var els = el.getElementsByClassName("form-date");
  for (var i=0; i<els.length; i++) {
    if (!els[i].className.match("datepicker-init")) {
      els[i].addClass("datepicker-init");
      _datepickers.push(new datepicker(els[i]));
    }
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
  this.selectActive = null;
  this.months = [
    "January", "February", "March", 
    "April", "May", "June", 
    "July", "August", "September", 
    "October", "November", "December",
  ];

  this.init = function() {
    var self = this;
    this.tags.input.addEventListener("click", function(){ self.toggle(); }, false);
    window.addEventListener("click", function(e){ self.windowClick(e); }, false);
    this.loadInput();
    this.render();
  }
  
  this.loadInput = function() {
    if (this.tags.input.value.length) {
      var arr = this.tags.input.value.split("-");
      this.value.y = parseInt(arr[0]);
      this.value.m = parseInt(arr[1]);
      this.value.d = parseInt(arr[2]);
      this.active.y = this.value.y;
      this.active.m = this.value.m;
      if (this.tags.year)
        this.tags.year.textContent = this.value.y;
      if (this.tags.month)
        this.tags.month.textContent = this.months[this.value.m-1];
    }
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
    self.tags.prev.addEventListener("click", function(){ self.monthPrev(); }, false);
    self.tags.next.addEventListener("click", function(){ self.monthNext(); }, false);
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
    this.tags.weekdays = document.createElement("div");
    this.tags.weekdays.className = "datepicker-weekdays";
    var arr = ["Mon", "Tue", "Wed", "Thu", "Fri", "Sat", "Sun"];
    for (var i=0; i<7; i++) {
      var wd = document.createElement("div");
      wd.className = "datepicker-weekday";
      wd.textContent = arr[i];
      this.tags.weekdays.appendChild(wd);
    }
    inner.appendChild(this.tags.weekdays);
    this.tags.calendar = document.createElement("div");
    this.tags.calendar.className = "datepicker-calendar";
    inner.appendChild(this.tags.calendar);
  }
  
  this.renderCal = function() {
    var dates = this.getCalendarDates(this.active.y, this.active.m);
    var today = new Date();
    this.tags.calendar.innerHTML = "";
    var week;
    for (var i=0; i<dates.length; i++) {
      if (i%7 == 0) {
        if (week)
          this.tags.calendar.appendChild(week);
        week = document.createElement("div");
        week.className = "datepicker-calendar-week";
      }
      var day = document.createElement("div");
      day.className = "datepicker-calendar-day";
      if (dates[i].d == today.getDate() && dates[i].m == today.getMonth()+1 && dates[i].y == today.getFullYear())
        day.className+= " today";
      if (dates[i].m == this.active.m)
        day.className+= " current-month";
      if (dates[i].d == this.value.d && dates[i].m == this.value.m && this.active.y == this.value.y)
        day.className+= " active";
      day.textContent = dates[i].d;
      week.appendChild(day);
      (function(self, tag, y, m, d) {
        tag.addEventListener("click", function(){ self.chooseDate(y, m, d); }, false);
      }(this, day, dates[i].y, dates[i].m, dates[i].d));
    }
    this.tags.calendar.appendChild(week);
  }
  
  this.getCalendarDates = function(y, m) {
    var date = new Date(y+"-"+this.addZero(m)+"-01");
    var dotw = this.getDay(date);
    var days = [];
    if (dotw != 0) {
      var d = new Date(date.getTime()-1000*60*60*24*dotw);
      for (var i=0, day = d.getDate(); i<dotw; i++) {
        days.push({
          y: d.getFullYear(),
          m: d.getMonth()+1,
          d: day+i
        });
      }
    }
    var d = new Date(date.getTime());
    while(d.getMonth()+1 == m) {
      days.push({
        y: d.getFullYear(),
        m: d.getMonth()+1,
        d: d.getDate()
      });
      d.setTime(d.getTime()+1000*60*60*24);
    }
    dotw = this.getDay(d);
    if (dotw != 0) {
      for (var i=7; i>dotw; i--) {
        days.push({
          y: d.getFullYear(),
          m: d.getMonth()+1,
          d: d.getDate()
        });
        d.setTime(d.getTime()+1000*60*60*24);
      }
    }
    return days;
  }
  
  this.getDay = function(date) {
    return (date.getDay()+6)%7;
  }
  
  this.selectToggle = function(key) {
    if (this.tags[key].parentNode.className.match("active"))
      this.selectClose(key);
    else
      this.selectOpen(key);
  }
  this.selectOpen = function(key) {
    if (this.selectActive)
      this.selectClose(this.selectActive);
    this.tags[key].parentNode.addClass("active");
    this.selectActive = key;
  }
  this.selectClose = function(key) {
    this.tags[key].parentNode.removeClass("active");
    if (key == this.selectActive)
      this.selectActive = null;
  }
  
  this.monthPrev = function() {
    if (this.active.m == 1) {
      this.active.y--;
      this.active.m = 12;
      this.tags.year.textContent = this.active.y;
    }
    else {
      this.active.m--;
    }
    this.tags.month.textContent = this.months[this.active.m-1];
    this.renderCal();
  }
  this.monthNext = function() {
    if (this.active.m == 12) {
      this.active.y++;
      this.active.m = 1;
      this.tags.year.textContent = this.active.y;
    }
    else {
      this.active.m++;
    }
    this.tags.month.textContent = this.months[this.active.m-1];
    this.renderCal();
  }
  
  this.chooseDate = function(y, m, d) {
    this.close();
    this.value.y = y;
    this.value.m = m;
    this.value.d = d;
    this.tags.input.value = y+"-"+this.addZero(m)+"-"+this.addZero(d);
    this.tags.input.trigger("change");
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
    this.loadInput();
    this.renderCal();
    this.tags.wrap.addClass("datepicker-open");
  }
  this.close = function() {
    this.tags.wrap.removeClass("datepicker-open");
  }
  
  this.addZero = function(val) {
    if (val < 10)
      return "0"+val;
    return val;
  }

  this.windowClick = function(e) {
    if (!this.isOpen())
      return;
    this.checkWrap(e.target);
    this.checkSelect(e.target);
  }
  this.checkSelect = function(el) {
    if (!this.selectActive)
      return;
    for (var i=0; el  && i<8; el = el.parentNode, i++) {
      if (el == this.tags[this.selectActive].parentNode)
        return;
    }
    this.selectClose(this.selectActive);
  }
  this.checkWrap = function(el) {
    for (var i=0; el && i<8; el = el.parentNode, i++) {
      if (el == this.tags.picker || el == this.tags.input)
        return;
    }
    this.close();
  }

  this.init();

}

window.addEventListener("load", datepickerInit, false);