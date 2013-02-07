<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title>Aufgabendatenbank - Aufgabe bearbeiten</title>
	<meta name="author" content="Wurzel e.V."/>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
	<link rel="stylesheet" type="text/css" href="pb.css"/>
	<link rel="icon" href="dw.ico"/>
	<script type="text/javascript" src="MathJax/MathJax.js?config=TeX-AMS_HTML"></script>
	<script type="text/javascript" src="preview.js"></script>
</head>
<body>
	<div class="head"><div style="width:50em;">
		<div class="logo">&#x221A;<span style="text-decoration:overline">WURZEL</span></div>
		<div class="login">Login</div>
		<div style="font-family:sans-serif; font-size:x-small;">Aufgabendatenbank <br /> &copy; 2012 <a href="http://www.wurzel.org/" target="_blank">Wurzel e.V.</a></div>
	</div></div>

	<div class="content">
	<h2 class="task">Aufgabe bearbeiten</h2>
	<form class="task" title="Aufgabenformular" action="">
		<input type="text" class="text" name="proposer" required placeholder="Einsender" style="width:165px;"/>
		<input type="text" class="text" name="location" required placeholder="Ort" style="width:100px"/>
		<input type="text" class="text" name="tags" placeholder="Tags" style="width:245px;"/> <br/>
		<textarea class="text" name="problem" id="problem" rows="20" cols="65" placeholder="Aufgabentext" style="height:200px;" onkeyup="Preview.Update()"></textarea> <br/>
		<div class="preview" id="preview"></div>
		<input type="button" value="Dummy" onclick="" style="visibility:hidden;"/>
		<input type="submit" value="Speichern" style="float:right;"/>
		<input type="button" value="Verwerfen" style="float:right;" onclick=""/>
	</form>
	</div>

	<script type="text/javascript">
		Preview.Init("problem", "preview");
	</script>
</body>
</html>