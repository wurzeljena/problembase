// pager in index
function incrPage(incr) {
	var page = document.getElementById("page").innerHTML - 1;
	var request = document.getElementById("request").value;

	if (page + incr >= 0)
		page += incr;

	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function () {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
			document.getElementById("tasklist").innerHTML = xmlhttp.responseText;

		// render math
		for (var id = 0; id < 10; id++)
			MathJax.Hub.Queue(["Typeset", MathJax.Hub, "prob" + id]);
	}

	if (request.length > 0)
		request += "&";
	xmlhttp.open("GET", "tasklist.php?" + request + "page=" + page, true);
	xmlhttp.send();

	// set info
	document.getElementById("page").innerHTML = page + 1;
	document.getElementById("pagetasks").innerHTML = (10 * page + 1) + "&mdash;" + 10 * (page + 1);
};

// dynamic tag list Ajax stuff
function drawTags(form) {
	var tags = document.forms[form].tags.value;
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function () {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
			document.getElementById("taglist").innerHTML = xmlhttp.responseText;
	}
	xmlhttp.open("GET", "tags.php?taglist=" + encodeURIComponent(tags) + "&form=" + form, true);
	xmlhttp.send();
}

function addTag(form) {
	var tags = document.forms[form].tags.value;
	var newtag = document.forms[form].elements["tag"].value;

	// add tag
	if (newtag != 0) {
		if (tags.length)
			tags += "," + newtag;
		else
			tags = newtag;
	}

	document.forms[form].tags.value = tags;

	// reset select element
	document.forms[form].elements["tag"].value = "0";

	drawTags(form);
};

function removeTag(form, id) {
	var tags = document.forms[form].tags.value;
	tags = tags.replace(RegExp(',' + id, 'g'), "");
	tags = tags.replace(RegExp(id + ',', 'g'), "");
	tags = tags.replace(RegExp(id, 'g'), "");
	document.forms[form].tags.value = tags;

	drawTags(form);
};

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

		xmlhttp.open("GET", "tags.php?taginfo&id=" + id, true);
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

function deleteTag() {
	var id = document.forms['tageditor'].elements['id'].value;

	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function () {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
			document.forms['tageditor'].elements['id'].value = "";
			loadTag();
		}
	}

	xmlhttp.open("POST", "tags.php?id=" + id + "&delete=1", true);
	xmlhttp.send();
}

function tagPreview() {
	var name = document.forms['tageditor'].elements['name'].value;
	var desc = document.forms['tageditor'].elements['description'].value;
	var clr = document.forms['tageditor'].elements['color'].value;

	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function () {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
			document.getElementById('result_tag').innerHTML = xmlhttp.responseText;
	}

	xmlhttp.open("GET", "tags.php?drawtag&name=" + name + "&desc=" + encodeURIComponent(desc) + "&color=" + encodeURIComponent(clr), true);
	xmlhttp.send();
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

	xmlhttp.open("POST", "edit_user.php?id=" + id + "&update&" + name + "=" + (+elem.checked), true);
	xmlhttp.send();
}
