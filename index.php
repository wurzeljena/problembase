<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title>Aufgabendatenbank</title>
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
		<form class="filter" title="Filter" action="">
		<input type="button" value="+ Erweitert" style="float:right;" onclick="Trigger.Trig();"/>
		<div class="caption">Filter</div>
		<input type="text" name="simple_filter" placeholder="Suchbegriff" style="width:330px;"/>
		<div id="hidden_filter" style="visibility:hidden; position:absolute;">
			<div class="info">Erweiterte Suche</div>
			<span class="question">Wer?</span> <input type="text" name="proposer" placeholder="Autor" style="width:200px;"/>
			<span class="question">Was?</span>
			<input type="text" name="tags" placeholder="Tags" style="width:160px;"/>
			<input type="checkbox" name="with_solution"/><span class="info">mit L�sung</span> <br/>
			<span class="question">Wie?</span> {Bewertungsbereich} <br/>
			<span class="question">Wann?</span> <input type="text" name="number" placeholder="No." style="width:40px;"/>
			<input type="checkbox" name="if_start"/> <span class="info">j�nger als</span> <input type="date" name="start" style="width:80px;"/>
			<input type="checkbox" name="if_end"/> <span class=info>�lter als</span>
			<input type="date" name="end" style="width:80px;"/>
		</div>
		</form>

		<div class="main">
		<a href="problem.htm" class="button" style="float:right;">Neue Aufgabe</a>
		<div class="caption">Aufgaben</div>
		<div class="problem_list">
			<div class="problem">
			<span class="info proposer">Autor, Ort</span>
			<span class="tag tag_test">Test</span>
			Hier kommt ein hoffentlich sehr langer Aufgabentext hin, der mindestens �ber zwei Zeilen geht.
			<table class="info"><tr>
			<td style="width:20%; border:none;">11.11.2011</td>
			<td style="width:30%;">Heft 11/11, Aufgabe x11</td>
			<td style="width:40%;">1 L�sung vorhanden <a>(anzeigen)</a></td>
			</tr></table>
			</div>
		</div>
		</div>
	</div>

	<form action="index.htm" class="taglist">
		<h3 class="caption" style="color:Gray;">[Tags]</h3>
		<input type="text" name="tag" placeholder="Tag hinzuf�gen"/>
		<input type="hidden" name="tags" value="Test"/> <br/>
		<div style="margin:5px; margin-bottom:2em;">
			<span class="tag tag_test">Test</span>
		</div>
		<a class="button" href="tags.htm">Bearbeiten</a>
	</form>

	<script type="text/javascript">
		Trigger.Init("hidden_filter");
	</script>
</body>
</html>