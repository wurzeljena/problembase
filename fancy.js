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

// starring mechanism
function Stars(name) {
	this.input = document.getElementById(name);
	this.value = parseInt(this.input.value);
	this.stars = new Array();
	this.stars[0] = document.getElementById(name + "1");
	this.stars[1] = document.getElementById(name + "2");
	this.stars[2] = document.getElementById(name + "3");
	this.stars[3] = document.getElementById(name + "4");
	this.stars[4] = document.getElementById(name + "5");

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

// query proposer via Ajax
function queryProp(form) {
	var str = document.forms[form].elements["proposer"].value;
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function () {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
			document.getElementById("prop").innerHTML = xmlhttp.responseText;
	}

	xmlhttp.open("GET", "proposers.php?prop_query=" + escape(str), true);
	xmlhttp.send();
};

// dynamic tag list Ajax stuff
function addTag(form) {
	var tags = document.forms[form].elements["tags"].value;
	var newtag = document.forms[form].elements["tag"].value;
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function () {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
			document.getElementById("tags").innerHTML = xmlhttp.responseText;
	}

	xmlhttp.open("GET", "tags.php?taglist=" + escape(tags) + "&newtag=" + newtag + "&form=" + form, true);
	xmlhttp.send();
};

function deleteTag(form, id) {
	var tags = document.forms[form].elements["tags"].value;
	tags = tags.replace(RegExp(',' + id, 'g'), "");
	tags = tags.replace(RegExp(id + ',', 'g'), "");
	tags = tags.replace(RegExp(id, 'g'), "");
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function () {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
			document.getElementById("tags").innerHTML = xmlhttp.responseText;
	}

	xmlhttp.open("GET", "tags.php?taglist=" + escape(tags) + "&form=" + form, true);
	xmlhttp.send();
};

// validate password
function validate_password() {
	var correct = (document.forms['pw'].elements['new_pw'].value
		== document.forms['pw'].elements['new_pw_check'].value);

	if (!correct)
		alert("Passworte stimmen nicht überein!");
	return correct;
}

// rights management
function setright(id, name) {
	var elem = document.getElementById(name + id);
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function () { }

	xmlhttp.open("POST", "edit_user.php?id=" + id + "&" + name + "=" + (+elem.checked), true);
	xmlhttp.send();
}
