// pager in index
function incrPage(incr) {
	var page = document.getElementById("page").innerHTML - 1;
	var request = document.getElementById("request").value;

	if (page + incr >= 0)
		page += incr;

	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function () {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			document.getElementById("tasklist").innerHTML = xmlhttp.responseText;

			// show tags
			eval(document.getElementById("tagscript").text);

			// render math
			for (var id = 0; document.getElementById("prob" + id); id++)
				MathJax.Hub.Queue(["Typeset", MathJax.Hub, "prob" + id]);
		}
	}

	if (request.length > 0)
		request += "&";
	xmlhttp.open("GET", rootdir + "/tasklist.php?" + request + "page=" + page, true);
	xmlhttp.send();

	// set info
	document.getElementById("page").innerHTML = page + 1;
};

// dynamic tag list Ajax stuff
function TagList(form, init) {
	this.list = Array();
	this.taglist = document.getElementById("taglist");
	var self = this;

	this.add = function (newtag) {
		// if there's nothing to add, exit
		if (newtag == 0 || this.list.indexOf(newtag) >= 0)
			return;

		// add tag
		this.list.push(newtag);

		// find out how it looks like and add to list
		var xmlhttp = new XMLHttpRequest();
		xmlhttp.onreadystatechange = function () {
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				var resp = JSON.parse(xmlhttp.responseText);
				resp.id = newtag;
				self.taglist.appendChild(writeTag(resp, self));
				self.taglist.appendChild(document.createTextNode(" "));
			}
		}

		xmlhttp.open("GET", rootdir + "/tags.php?taginfo&id=" + newtag, true);
		xmlhttp.send();
	}

	this.remove = function (id, elem) {
		this.list.splice(this.list.indexOf(id), 1);
		elem.parentNode.removeChild(elem);
	}

	// add initial tags
	for (var i = 0; i < init.length; i++)
		this.add(init[i]);

	// function to write result on form submit
	document.forms[form].addEventListener("submit",
		function () { document.forms[form].tags.value = self.list.toString(); } )
}

// tag editor functions
function loadTag() {
	var id = document.forms['tageditor'].elements['id'].value;

	if (id != "") {
		var xmlhttp = new XMLHttpRequest();
		xmlhttp.onreadystatechange = function () {
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				var resp = JSON.parse(xmlhttp.responseText);
				document.forms['tageditor'].elements['name'].value = resp.name;
				document.forms['tageditor'].elements['description'].value = resp.description;
				document.forms['tageditor'].elements['color'].value = resp.color;
				document.forms['tageditor'].elements['hidden'].checked = resp.hidden;
				document.getElementById('delete_tag').disabled = false;
				document.getElementById('submit_tag').value = "\u00c4ndern";
				tagPreview();
			}
		}

		xmlhttp.open("GET", rootdir + "/tags.php?taginfo&id=" + id, true);
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

	xmlhttp.open("POST", rootdir + "/edit_user.php?id=" + id + "&update&" + name + "=" + (+elem.checked), true);
	xmlhttp.send();
}
