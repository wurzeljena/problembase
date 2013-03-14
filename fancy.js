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
	var self = this;

	this.newProp = function (num) {
		if (document.forms[form].elements["new" + num].checked) {
			document.forms[form].elements["proposer_id[" + num + "]"].value = -1;
			document.forms[form].elements["location[" + num + "]"].disabled = false;
			document.forms[form].elements["location[" + num + "]"].value = "";
			document.forms[form].elements["country[" + num + "]"].disabled = false;
			document.forms[form].elements["country[" + num + "]"].value = "";
		}
		else
			queryProp(self, form, num);
	}

	this.addProp = function () {
		var max = Math.max.apply(Math, this.nums);
		var num = (max < 0) ? 0 : max + 1;

		var prop = document.createElement("div");
		prop.id = "prop" + num;

		var name = document.createElement("input");
		name.type = name.className = "text";
		name.name = "proposer[" + num + "]";
		name.setAttribute("list", "proposers");
		name.required = true;
		name.placeholder = "Einsender";
		name.onblur = function () { queryProp(self, form, num); };
		prop.appendChild(name);

		var id = document.createElement("input");
		id.type = "hidden";
		id.name = "proposer_id[" + num + "]";
		id.value = "-1";
		prop.appendChild(id);

		var checknew = document.createElement("input");
		checknew.type = "checkbox";
		checknew.name = "new" + num;
		checknew.title = "neu";
		checknew.onclick = function () { self.newProp(num); };
		prop.appendChild(checknew);

		var location = document.createElement("input");
		location.type = location.className = "text";
		location.name = "location[" + num + "]";
		location.required = true;
		location.placeholder = "Ort";
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
		document.forms[form].elements["proposer_id[" + num + "]"].value = list[i].id;
		document.forms[form].elements["location[" + num + "]"].value = list[i].location;
		document.forms[form].elements["location[" + num + "]"].disabled = true;
		document.forms[form].elements["country[" + num + "]"].value = list[i].country;
		document.forms[form].elements["country[" + num + "]"].disabled = true;
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
				this.stars[i].src = "img/mandstar.png";
				this.stars[i].alt = "*";
			}
			else {
				this.stars[i].src = "img/mand.png";
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
