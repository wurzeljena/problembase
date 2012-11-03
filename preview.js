// HIDDEN TRIGGER
var Trigger = {
	element: null,
	is_hidden: true,

	Init: function (id) {
		this.element = document.getElementById(id);
	},
	Trig: function () {
		if (this.is_hidden) {
			this.element.style.visibility = "visible";
			this.element.style.position = "relative";
		}
		else {
			this.element.style.visibility = "hidden";
			this.element.style.position = "absolute";
		}
		this.is_hidden = !this.is_hidden;
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