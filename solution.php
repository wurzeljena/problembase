<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title>Aufgabendatenbank - L�sung bearbeiten</title>
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
		<div style="font-family:sans-serif; font-size:x-small;">Aufgabendatenbank <br /> &copy; 2012 <a href="http://www.wurzel.org/">Wurzel e.V.</a></div>
	</div></div>

	<div class="content">
	<h2 class="solution">L�sung bearbeiten</h2>
	<form class="solution" title="L�sungsformular" action="">
		<div class="problem">Aufgabe</div>
		<!-- evtl. noch TeX-Code der Aufgabe einblenden -->
		<textarea class="text" name="solution" id="solution" rows="60" cols="80" placeholder="L�sungstext" style="height:400px;" onkeyup="Preview.Update()"></textarea> <br/>
		<div class="preview" id="preview"></div>
		<input type="button" value="Dummy" onclick="" style="visibility:hidden;"/>
		<input type="submit" value="Speichern" style="float:right;"/>
		<input type="button" value="Verwerfen" style="float:right;" onclick=""/>
	</form>
	</div>

	<script type="text/javascript">
		Preview.Init("solution", "preview");
	</script>
</body>
</html>