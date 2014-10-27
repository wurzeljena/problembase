// HIDDEN TRIGGER
function Trigger(id) {
	this.element = document.getElementById(id);
	this.is_hidden = true;

	this.Trig = function () {
		if (this.is_hidden) {
			this.element.style.visibility = "visible";
			this.element.style.position = "relative";
		}
		else {
			this.element.style.visibility = "hidden";
			this.element.style.position = "absolute";
		}
		this.is_hidden = !this.is_hidden;
	};
};

function PopupTrigger(id) {
	this.element = document.getElementById(id);
	var self = this;

	this.Show = function () {
		this.element.style.display = "inherit";
		document.body.addEventListener("click", Hide);
	}

	var Hide = function (event) {
		var elem = event.target;
		while (elem != self.element && elem != document.body)
			elem = elem.parentNode;
		if (elem == document.body) {
			self.element.style.display = "none";
			document.body.removeEventListener("click", Hide);
			event.preventDefault();
		}
	}
}

// PREVIEW CODE
// configure MathJax to accept $...$
MathJax.Hub.Config({
	tex2jax: {
		inlineMath: [['$', '$'], ['\\(', '\\)']],
		processEscapes: true
	}
});

// automatic preview class - essentially the "sample dynamic" from MathJax
var Preview = {
	input: null,
	output: null,

	timeout: null,     // store setTimout id
	mjRunning: false,  // true when MathJax is processing
	oldText: null,     // used to check if an update is needed

	Init: function (input, output) {
		this.input  = document.getElementById(input);
		this.output = document.getElementById(output);
	},

	Update: function () {
		if (this.timeout) { clearTimeout(this.timeout) }
		this.timeout = setTimeout(this.callback, 200);
	},

	CreatePreview: function () {
		Preview.timeout = null;
		if (this.mjRunning) return;
		var text = this.input.value;
		if (text === this.oldtext) return;
		this.output.innerHTML = this.oldtext = text;
		this.mjRunning = true;
		MathJax.Hub.Queue(["Typeset", MathJax.Hub, this.output], ["PreviewDone", this]);
	},

	PreviewDone: function () {
		this.mjRunning = false;
	}
};

Preview.callback = MathJax.Callback(["CreatePreview", Preview]);
Preview.callback.autoReset = true;  // make sure it can run more than once

// proposer list
function PropForm(form, list) {
	this.form = form;
	this.nums = Array();
	this.datalists = Array();
	this.data = Array();
	var self = this;

	this.addProp = function () {
		var max = Math.max.apply(Math, this.nums);
		var num = (max < 0) ? 0 : max + 1;

		var prop = document.createElement("div");
		prop.id = "prop" + num;

		var name = document.createElement("input");
		name.type = "text";
		name.className = "text name";
		name.name = "proposer[" + num + "]";
		name.setAttribute("list", "proposers");
		name.autocomplete = "off";
		name.required = true;
		name.placeholder = "Einsender";
		name.onblur = function () { self.queryProp(this, num, true); };
		prop.appendChild(name);

		var id = document.createElement("input");
		id.type = "hidden";
		id.name = "proposer_id[" + num + "]";
		id.value = "-1";
		prop.appendChild(id);

		var locationlist = document.createElement("datalist");
		locationlist.id = "propdata" + num;
		prop.appendChild(locationlist);
		this.datalists[num] = locationlist;

		var location = document.createElement("input");
		location.type = "text";
		location.className = "text location";
		location.name = "location[" + num + "]";
		location.setAttribute("list", "propdata" + num);
		location.autocomplete = "off";
		location.required = true;
		location.placeholder = "Ort";
		location.onblur = function () { self.setLoc(this, num); };
		prop.appendChild(location);

		var country = document.createElement("input");
		country.type = "text";
		country.className = "text country";
		country.name = "country[" + num + "]";
		country.placeholder = "Land";
		prop.appendChild(country);

		var remove = document.createElement("input");
		remove.type = "button";
		remove.value = "entfernen";
		remove.onclick = function () { self.removeProp(num); };
		prop.appendChild(remove);

		var propsDiv = document.getElementById("proplist");
		propsDiv.appendChild(prop);

		this.nums.push(num);
		return num;
	}

	// query proposer via Ajax
	this.queryProp = function (prop, num, async) {
		var xmlhttp = new XMLHttpRequest();
		xmlhttp.onreadystatechange = function () {
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				var resp = JSON.parse(xmlhttp.responseText);
				document.forms[form].elements["location[" + num + "]"].value = "";
				document.forms[form].elements["country[" + num + "]"].value = "";

				var option;
				while (option = self.datalists[num].firstChild)
					self.datalists[num].removeChild(option);

				self.data[num] = Object();
				for (var i = 0; i < resp.length; i++) {
					var option = document.createElement("option");
					option.value = resp[i].location;
					self.datalists[num].appendChild(option);

					// remember country and id
					self.data[num][resp[i].location] = { id: resp[i].id, country: resp[i].country };
				}
			}
		}

		var name = encodeURIComponent(prop.value.replace(/ /g, "_"));
		xmlhttp.open("GET", rootdir + "/proposers/" + name, async);
		xmlhttp.setRequestHeader("Accept", "application/json");
		xmlhttp.send();
	}

	// react to location blur
	this.setLoc = function (loc, num) {
		if (this.data[num].hasOwnProperty(loc.value)) {
			var propdata = this.data[num][loc.value];
			document.forms[form].elements["proposer_id[" + num + "]"].value = propdata.id;
			document.forms[form].elements["country[" + num + "]"].value = propdata.country;
		}
		else {
			document.forms[form].elements["proposer_id[" + num + "]"].value = -1;
			document.forms[form].elements["country[" + num + "]"].value = "";
		}
	}

	this.removeProp = function (num) {
		this.nums.splice(this.nums.indexOf(num), 1);
		var propDiv = document.getElementById("prop" + num);
		propDiv.parentNode.removeChild(propDiv);
	}

	// on sending form: write nums to an hidden input field
	document.forms[form].addEventListener("submit",
		function () { document.forms[form].elements["propnums"].value = self.nums.toString(); });

	// write initial data
	for (var i = 0; i < list.length; i++) {
		var num = this.addProp();
		document.forms[form].elements["proposer[" + num + "]"].value = list[i].name;
		this.queryProp(document.forms[form].elements["proposer[" + num + "]"], num, false);
		document.forms[form].elements["proposer_id[" + num + "]"].value = list[i].id;
		document.forms[form].elements["location[" + num + "]"].value = list[i].location;
		document.forms[form].elements["country[" + num + "]"].value = list[i].country;
	}
}

// tag creation
function writeTag(taginfo, taglist) {
	// If it's not part of a tag list or a tag selector, it's a link
	var link = (taglist === undefined) && !taginfo.hasOwnProperty("active");

	// URL name
	var url_name = taginfo.name.replace(/ /g, "_");
	if (parseInt(taginfo.private))
		url_name = "private/" + url_name;

	var tag = document.createElement(link ? "a" : "span");
	tag.id = tag.textContent = taginfo.name;
	tag.title = taginfo.description;
	tag.className = "tag";
	if (link)
		tag.href = rootdir + "/tags/" + url_name;

	// compute other color
	var clr = taginfo.color.split("");
	for (var chan = 0; chan < 3; chan++) {
		var comp = parseInt(clr[2 * chan + 1], 16);
		if (comp >= 2)
			clr[2 * chan + 1] = (comp - 2).toString(16);
		else
			clr[2 * chan + 1] = (comp + 2).toString(16);
	}
	var altcolor = clr.join("");
	tag.style.backgroundColor = taginfo.color;
	tag.style.backgroundImage = "linear-gradient(to bottom, " + taginfo.color + ", " + altcolor + ")";

	// decide on text color
	var white = (0.07 * parseInt(taginfo.color.substr(5, 2), 16)
		+ 0.71 * parseInt(taginfo.color.substr(3, 2), 16)
		+ 0.21 * parseInt(taginfo.color.substr(1, 2), 16) < 0.7 * 256);
	tag.style.color = white ? "White" : "Black";
	tag.style.textShadow = "1px 1px 0px " + (white ? "Black" : "White");

	// if it's part of a tag list, show close button
	if (taglist !== undefined) {
		var close = document.createElement("i");
		close.className = "icon-remove close";
		close.style.cursor = "pointer";
		close.onclick = function () { taglist.remove(url_name, this.parentNode); };
		tag.appendChild(close);
	}

	// if it's part of a tag selector, add click handler
	if (taginfo.hasOwnProperty("active")) {
		if (parseInt(taginfo.active))
			tag.style.opacity = 1;
		else
			tag.style.opacity = 0.3;

		if (parseInt(taginfo.enabled)) {
			tag.style.cursor = "pointer";
			tag.onclick = function ()
			{
				if (this.style.opacity == 1) {
					this.style.opacity = 0.3;
					setTag(taginfo.problem, url_name, 0);
				}
				else {
					this.style.opacity = 1;
					setTag(taginfo.problem, url_name, 1);
				}
			}
		}
	}

	return tag;
}

// script for delete buttons
function postDelete(form) {
	document.forms[form].elements["delete"].checked = true;
	document.forms[form].submit();
}

// interactive calendar
var calendar = {
	decade: null,	// decade showed
	year: null,		// year showed
	date: null,		// current date

	years: null,	// div containing the years
	months: null,	// div containing the months

	month_names: ["Jan", "Feb", "Mar+Apr", "Mai", "Jun", "Jul", "Aug", "Sep+Okt", "Nov", "Dez"],
	month_numbers: [1, 2, 3, 5, 6, 7, 8, 9, 11, 12],

	set_decade: function (decade) {
		this.decade = decade;

		// delete everything in "years"
		var year;
		while (year = years.firstChild)
			years.removeChild(year);

		// create new nodes and put them in "years"
		for (var i = 0; i < 10; i++) {
			var year = document.createElement("a");
			year.id = year.textContent = 10 * decade + i;
			if (10 * decade + i != this.year)
				year.setAttribute("href", "javascript:calendar.set_year(" + (10 * decade + i) + ")");
			years.appendChild(year);
			years.appendChild(document.createTextNode(" "));
		}
	},

	incr_decade: function (incr) {
		this.set_decade(this.decade + incr);
	},

	set_year: function (year) {
		// update year links
		if (document.getElementById(this.year))
			document.getElementById(this.year).setAttribute("href", "javascript:calendar.set_year(" + this.year + ")");
		this.year = year;
		document.getElementById(this.year).removeAttribute("href");


		// delete everything in "months"
		var month;
		while (month = months.firstChild)
			months.removeChild(month);

		// create new nodes and put them in "months"
		for (var i = 0; i < this.month_names.length; i++) {
			var month = document.createElement("a");
			month.textContent = this.month_names[i];
			if ((i % 5) == 2) month.className = "long";
			if (this.date === null || year != this.date.year || this.month_numbers[i] != this.date.month)
				month.setAttribute("href", rootdir + "/issues/" + year + "/" + this.month_numbers[i]);
			months.appendChild(month);
			months.appendChild(document.createTextNode(" "));
		}
	},

	init: function (year, month) {
		this.years = document.getElementById("years");
		this.months = document.getElementById("months");

		// save date, if set
		if (month != -1)
			this.date = {year: year, month: month};

		// write links
		this.set_decade(Math.floor(year / 10));
		this.set_year(year);
	}
};

// pictures
function Pictures(form, list) {
	this.form = form;
	this.nums = Array();
	this.datalists = Array();
	this.data = Array();
	var self = this;

	this.addPic = function () {
		var max = Math.max.apply(Math, this.nums);
		var num = (max < 0) ? 0 : max + 1;

		var pic = document.createElement("div");
		pic.id = "pic" + num;
		pic.className = "picform";

		var header = document.createElement("h4");
		header.className = "icon-picture";
		pic.appendChild(header);

		var numfield = document.createElement("input");
		numfield.type = "text";
		numfield.className = "text id";
		numfield.name = "pic_id[" + num + "]";
		numfield.setAttribute("form", this.form);
		numfield.required = true;
		numfield.placeholder = "No.";
		pic.appendChild(numfield);

		var public = document.createElement("input");
		public.type = "checkbox";
		public.id = "public" + num;
		public.name = "pic_public[" + num + "]";
		public.setAttribute("form", this.form);
		pic.appendChild(public);

		var label = document.createElement("label");
		label.setAttribute("for", "public" + num);
		label.textContent = "\u00f6ffentlich";
		pic.appendChild(label);

		var remove = document.createElement("input");
		remove.type = "button";
		remove.value = "entfernen";
		remove.style = "float:right;";
		remove.onclick = function () { self.removePic(num); };
		pic.appendChild(remove);

		var content = document.createElement("textarea");
		content.className = "text";
		content.name = "pic_content[" + num + "]";
		content.setAttribute("form", this.form);
		content.rows = 10;
		content.cols = 65;
		content.placeholder = "METAPOST-Code";
		pic.appendChild(content);

		var doccont = document.getElementsByClassName("content");
		doccont[0].appendChild(pic);

		this.nums.push(num);
	}

	this.removePic = function (num) {
		this.nums.splice(this.nums.indexOf(num), 1);
		var picDiv = document.getElementById("pic" + num);
		picDiv.parentNode.removeChild(picDiv);
	}

	// on sending form: write nums to an hidden input field
	document.forms[form].addEventListener("submit",
		function () { document.forms[form].elements["picnums"].value = self.nums.toString(); });

	// write initial data
	for (var num = 0; num < list.length; num++) {
		this.addPic();
		document.forms[form].elements["pic_id[" + num + "]"].value = list[num].id;
		document.forms[form].elements["pic_public[" + num + "]"].checked = list[num].public;
		document.forms[form].elements["pic_content[" + num + "]"].textContent = list[num].content;
	}
}

// starring mechanism
function Stars(name) {
	this.input = document.getElementById(name);
	this.value = parseInt(this.input.value);
	this.stars = new Array();
	for (var i = 0; i < 5; i++)
		this.stars[i] = document.getElementById(name + (i+1));

	this.show = function (num) {
		for (var i = 0; i < 5; i++) {
			if (i < num) {
				this.stars[i].src = rootdir + "/img/mandstar.png";
				this.stars[i].alt = "*";
			}
			else {
				this.stars[i].src = rootdir + "/img/mand.png";
				this.stars[i].alt = "o";
			}
		}
	};
	this.set = function (num) {
		this.value = num;
		this.input.value = this.value;
	};
	this.reset = function () {
		this.show(this.value);
	};

	this.reset();
};

// wait timer for failed logins
function WaitTimer(timestr) {
	var self = this;
	this.time = new Date(timestr)
	this.span = document.getElementById("wait")

	this.setWait = function () {
		var d = new Date();
		var diff = this.time - d;
		if (diff > 0)
			this.span.textContent = "Erneut in " + Math.round(diff / 1000) + " Sek.";
		else {
			this.span.textContent = "";
			clearInterval(this.Timer);
		}
	}

	this.Timer = setInterval(function () { self.setWait() }, 250)
}
