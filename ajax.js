// query proposer via Ajax
function queryProp(form) {
	var str = document.forms[form].elements["proposer"].value;
	var xmlhttp = new XMLHttpRequest();
	xmlhttp.onreadystatechange = function () {
		if (xmlhttp.readyState == 4 && xmlhttp.status == 200)
			document.getElementById("prop").innerHTML = xmlhttp.responseText;
	}

	xmlhttp.open("GET", "proposers.php?prop_query=" + encodeURIComponent(str), true);
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

	xmlhttp.open("GET", "tags.php?taglist=" + encodeURIComponent(tags) + "&newtag=" + newtag + "&form=" + form, true);
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

	xmlhttp.open("GET", "tags.php?taglist=" + encodeURIComponent(tags) + "&form=" + form, true);
	xmlhttp.send();
};

// tag editor functions
function loadTag() {
	var id = document.forms['tageditor'].elements['id'].value;

	if (id != "") {
		var xmlhttp = new XMLHttpRequest();
		xmlhttp.onreadystatechange = function () {
			if (xmlhttp.readyState == 4 && xmlhttp.status == 200) {
				eval("var resp = " + xmlhttp.responseText);
				document.forms['tageditor'].elements['name'].value = resp.name;
				document.forms['tageditor'].elements['description'].value = resp.desc;
				document.forms['tageditor'].elements['color'].value = resp.color;
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
		document.getElementById('submit_tag').value = "Hinzuf\u00fcgen";
		document.getElementById('result_tag').innerHTML = "";
	}
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
		alert("Passworte stimmen nicht &uuml;berein!");
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
