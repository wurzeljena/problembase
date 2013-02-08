<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title>Aufgabendatenbank - Lösung bearbeiten</title>
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

	<?php
	$pb = new SQLite3('sqlite/problembase.sqlite', '0666');
	if (isset($_REQUEST['id'])) {
		$id = (int)$_REQUEST['id'];
		$solution = $pb->querySingle("SELECT * FROM solutions WHERE id=".$id, true);
		$problem = $pb->querySingle("SELECT * FROM problems WHERE id=".$solution['problem_id'], true);
		$proposer = $pb->querySingle("SELECT * FROM proposers WHERE id=".$solution['proposer_id'], true);
	}
	elseif (isset($_REQUEST['problem_id'])) {
		$problem_id = (int)$_REQUEST['problem_id'];
		$problem = $pb->querySingle("SELECT * FROM problems WHERE id=".$problem_id, true);
	}
	else
		die("Invalid URL: no problem or solution given.");
	?>

	<div class="content">
	<h2 class="solution">Lösung bearbeiten</h2>
	<form class="solution" title="Lösungsformular" action="">
		<div class="problem"><?php print $problem['problem']; ?></div>
		<input type="hidden" name="id" value="<?php if (isset($id)) print $id; ?>">
		<input type="hidden" name="problem_id" value="<?php if (isset($id)) print $solution['problem_id']; else $problem_id; ?>">
		<input type="text" class="text" name="proposer" required placeholder="Einsender" style="width:165px;"
			value="<?php if (isset($id)) print $proposer['name']; ?>"/>
		<input type="text" class="text" name="location" required placeholder="Ort" style="width:100px;"
			value="<?php if (isset($id)) print $proposer['location']; ?>"/>
		<input type="text" class="text" name="country" placeholder="Land" style="width:245px;"
			value="<?php if (isset($id)) print $proposer['country']; ?>"/> <br/>
		<textarea class="text" name="solution" id="solution" rows="60" cols="80" placeholder="Lösungstext"
			style="height:400px;" onkeyup="Preview.Update()"><?php if (isset($id)) print $solution['solution']; ?></textarea> <br/>
		<div class="preview" id="preview"></div>
		<input type="button" value="Dummy" onclick="" style="visibility:hidden;"/>
		<input type="submit" value="<?php if (isset($id)) print "Speichern"; else print "Erstellen"; ?>" style="float:right;"/>
		<input type="button" value="Verwerfen" style="float:right;" onclick="history.back();"/>
	</form>
	</div>

	<?php $pb->close(); ?>

	<script type="text/javascript">
		Preview.Init("solution", "preview");
	</script>
</body>
</html>
