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
		name.type = name.className = "text";
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
		location.type = location.className = "text";
		location.name = "location[" + num + "]";
		location.setAttribute("list", "propdata" + num);
		location.autocomplete = "off";
		location.required = true;
		location.placeholder = "Ort";
		location.onblur = function () { self.setLoc(this, num); };
		prop.appendChild(location);

		var country = document.createElement("input");
		country.type = country.className = "text";
		country.name = "country[" + num + "]";
		country.placeholder = "Land";
		prop.appendChild(country);

		var remove = document.createElement("input");
		remove.type = "button";
		remove.value = "entfernen";
		remove.onclick = function () { self.removeProp(num); };
		remove.placeholder = "Land";
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

		xmlhttp.open("GET", rootdir + "/proposers.php?prop_query=" + encodeURIComponent(prop.value), async);
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
	document.forms[form].onsubmit =
		function () { document.forms[form].elements["propnums"].value = self.nums.toString(); };

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
function writeTag(name, description, color) {
	var tag = document.createElement("span");
	tag.textContent = name;
	tag.title = description;
	tag.className = "tag";

	// compute other color
	var clr = color.split("");
	for (var chan = 0; chan < 3; chan++) {
		var comp = parseInt(clr[2 * chan + 1], 16);
		if (comp >= 2)
			clr[2 * chan + 1] = (comp - 2).toString(16);
		else
			clr[2 * chan + 1] = (comp + 2).toString(16);
	}
	var altcolor = clr.join("");
	tag.style.backgroundColor = color;
	tag.style.backgroundImage = "linear-gradient(to bottom, " + color + ", " + altcolor + ")";

	// decide on text color
	var white = (0.07 * parseInt(color.substr(5, 2), 16)
		+ 0.71 * parseInt(color.substr(3, 2), 16)
		+ 0.21 * parseInt(color.substr(1, 2), 16) < 0.7 * 256);
	tag.style.color = white ? "White" : "Black";
	tag.style.textShadow = "1px 1px 0px " + (white ? "Black" : "White");
	return tag;
}

// script for delete buttons
function postDelete(form) {
	document.forms[form].elements["delete"].checked = true;
	document.forms[form].submit();
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
