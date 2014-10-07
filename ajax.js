/**
 * Pager in index. Create object with hash, total number of pages and current page.
 * Pages will be written to the element with id="tasklist".
 */
function PageLoader(hash, max_pages, page) {
	// find base URL
	var match = document.URL.match(/\?.*/);

	// If we have a page argument, cut it out
	var base_url = match ? match[0].replace(/page=[0-9]*&/, "").replace(/[\?&]page=[0-9]*/, "") : null;

	this.getPage = function () {return page;}

	this.setPage = function (new_page) {
		if (new_page >= 0 && new_page < max_pages) {
			page = new_page;
			loadPage();

			// add to history
			var state = {page: page};
			var title = "Suchergebnisse (Seite " + (page+1) + ")";
			var arg = "page=" + (page+1);
			var url = base_url ? (base_url + "&" + arg) : "?" + arg;
			if (startup) {
				history.replaceState(state, title, url);
				startup = false;
			}
			else
				history.pushState(state, title, url);
		}
	}

	var loadPage = function () {
		var xmlhttp = new XMLHttpRequest();
		xmlhttp.onreadystatechange = function () {
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				document.getElementById("tasklist").innerHTML = xmlhttp.responseText;

				// show tags
				eval(document.getElementById("tagscript").text);

				// render math
				for (var id = 0; document.getElementById("prob" + id); id++)
					MathJax.Hub.Queue(["Typeset", MathJax.Hub, "prob" + id]);

				// show current page number and scroll to the top
				document.getElementById("page").innerHTML = page + 1;
				window.scroll(0,0);
			}
		}

		xmlhttp.open("GET", rootdir + "/tasks?hash=" + hash + "&page=" + page, true);
		xmlhttp.send();
	}

	window.onpopstate = function (event) {
		page = event.state.page;
		loadPage();
	}

	// load given page
	var startup = true;
	this.setPage(page);
}

// dynamic tag list Ajax stuff
function TagList(form, init) {
	this.list = Array();
	this.taglist = document.getElementById("taglist");
	var self = this;

	this.add = function (newtag) {
		// if there's nothing to add, exit
		if (newtag.length == 0 || this.list.indexOf(newtag) >= 0)
			return;

		// add tag
		this.list.push(newtag);

		// find out how it looks like and add to list
		var xmlhttp = new XMLHttpRequest();
		xmlhttp.onreadystatechange = function () {
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				var resp = JSON.parse(xmlhttp.responseText);
				self.taglist.appendChild(writeTag(resp, self));
				self.taglist.appendChild(document.createTextNode(" "));
			}
		}

		xmlhttp.open("GET", rootdir + "/tags/" + newtag, true);
		xmlhttp.setRequestHeader("Accept", "application/json");
		xmlhttp.send();
	}

	this.remove = function (name, elem) {
		this.list.splice(this.list.indexOf(name), 1);
		elem.parentNode.removeChild(elem);
	}

	// add initial tags
	for (var i = 0; i < init.length; i++) {
		this.list.push(init[i].name.replace(/ /g, "_"));
		this.taglist.appendChild(writeTag(init[i], this));
		this.taglist.appendChild(document.createTextNode(" "));
	}

	// function to write result on form submit
	document.forms[form].addEventListener("submit",
		function () { document.forms[form].tags.value = self.list.toString(); } )
}

// tag editor functions
function loadTag() {
	var name = document.forms['tageditor'].elements['old_name'].value;

	if (name != "") {
		var xmlhttp = new XMLHttpRequest();
		xmlhttp.onreadystatechange = function () {
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				var resp = JSON.parse(xmlhttp.responseText);
				document.forms['tageditor'].elements['name'].value = resp.name;
				document.forms['tageditor'].elements['description'].value = resp.description;
				document.forms['tageditor'].elements['color'].value = resp.color;
				document.forms['tageditor'].elements['hidden'].checked = (resp.hidden == 1);
				document.getElementById('delete_tag').disabled = false;
				document.getElementById('submit_tag').value = "\u00c4ndern";
				tagPreview();
			}
		}

		xmlhttp.open("GET", rootdir + "/tags/" + name, true);
		xmlhttp.setRequestHeader("Accept", "application/json");
		xmlhttp.send();
	}
	else {
		document.forms['tageditor'].elements['name'].value = "";
		document.forms['tageditor'].elements['description'].value = "";
		document.forms['tageditor'].elements['color'].value = "";
		document.forms['tageditor'].elements['hidden'].checked = false;
		document.getElementById('delete_tag').disabled = true;
		document.getElementById('submit_tag').value = "Hinzuf\u00fcgen";
		document.getElementById('result_tag').innerHTML = "";
	}
}

// tag selector
function setTag(problem, tag, set) {
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function () {}

	xmlhttp.open("POST", rootdir + "/submit/" + problem, true);
	xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xmlhttp.send("tag=" + tag + "&set=" + set);
}

function tagPreview() {
	var taginfo = {
		name: document.forms['tageditor'].elements['name'].value,
		description: document.forms['tageditor'].elements['description'].value,
		color: document.forms['tageditor'].elements['color'].value
	};

	document.getElementById('result_tag').innerHTML = "";
	document.getElementById('result_tag').appendChild(writeTag(taginfo));
}

// validate password
function validate_password() {
	var correct = (document.forms['pw'].elements['new_pw'].value
		== document.forms['pw'].elements['new_pw_check'].value);

	if (!correct)
		alert("Passworte stimmen nicht \u00fcberein!");
	return correct;
}

// rights management
function setright(id, name) {
	var elem = document.getElementById(name + id);
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function () { }

	xmlhttp.open("POST", rootdir + "/users/" + id + "/edit", true);
	xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	xmlhttp.send("update=1&" + name + "=" + (+elem.checked));
}
