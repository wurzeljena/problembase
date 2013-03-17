<?php
	session_start();
	include 'proposers.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Aufgabendatenbank - L&ouml;sung bearbeiten</title>
	<meta name="author" content="Wurzel e.V."/>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<link rel="stylesheet" type="text/css" href="pb.css"/>
	<link rel="icon" href="dw.ico"/>
	<script type="text/javascript" src="MathJax/MathJax.js?config=TeX-AMS_HTML"></script>
	<script type="text/javascript" src="fancy.js"></script>
	<script type="text/javascript" src="ajax.js"></script>
</head>
<body>
	<?php include 'head.php'; ?>

	<?php
	$pb = new SQLite3('sqlite/problembase.sqlite', '0666');
	if (isset($_REQUEST['id'])) {
		$id = (int)$_REQUEST['id'];
		$solution = $pb->querySingle("SELECT solutions.*, files.content AS solution FROM solutions JOIN files ON solutions.file_id=files.rowid WHERE id=$id", true);
		$problem = $pb->querySingle("SELECT problems.*, files.content AS problem FROM problems JOIN files ON problems.file_id=files.rowid WHERE id=".$solution['problem_id'], true);
	}
	elseif (isset($_REQUEST['problem_id'])) {
		$problem_id = (int)$_REQUEST['problem_id'];
		$problem = $pb->querySingle("SELECT problems.*, files.content AS problem FROM problems JOIN files ON problems.file_id=files.rowid WHERE id=$problem_id", true);
	}
	else
		die("Invalid URL: no problem or solution given.");
	?>

	<div class="content">
	<h2 class="solution">L&ouml;sung bearbeiten</h2>
	<form class="solution" id="solution" title="L&ouml;sungsformular" action="submit_solution.php" method="POST">
		<div class="problem"><?php print htmlspecialchars($problem['problem']); ?></div>
		<?php if (isset($id)) print "<input type='hidden' name='id' value='$id'>"; ?>
		<input type="hidden" name="problem_id" value="<?php if (isset($id)) print $solution['problem_id']; else print $problem_id; ?>">
		<?php proposer_form($pb, "solution", "solution", isset($id) ? $id : -1); ?>
		<textarea class="text" name="solution" id="textarea" rows="60" cols="80" placeholder="L&ouml;sungstext"
			style="height:400px;" onkeyup="Preview.Update()"><?php if (isset($id)) print $solution['solution']; ?></textarea> <br/>
		<div class="preview" id="preview"></div>
		<textarea class="text" name="remarks" id="textarea" rows="5" cols="65" placeholder="Anmerkungen"
			style="height:70px;"><?php if (isset($id)) print $solution['remarks']; ?></textarea>
		<label for="published">Ver&ouml;ffentlicht in:</label> <input type="text" class="text" name="published" id="published" placeholder="MM/JJ" pattern="([1-9]|0[1-9]|1[0-2])/[0-9]{2}" style="width:50px;" value="<?php if (isset($solution['month'])) print $solution['month']."/".($solution['year']%100); ?>"/>
		<input type="button" value="Dummy" onclick="" style="visibility:hidden;"/>
		<input type="submit" value="<?php if (isset($id)) print "Speichern"; else print "Erstellen"; ?>" style="float:right;"/>
		<?php if (isset($id)) {?>
		<input type="checkbox" name="delete"/>
		<input type="button" value="L&ouml;schen" style="float:right;"
			onclick="if (confirm('L&ouml;sung wirklich l&ouml;schen?')) postDelete('solution');"/>
		<?php } ?>
	</form>
	</div>

	<?php $pb->close(); ?>

	<script type="text/javascript">
		Preview.Init("textarea", "preview");
	</script>
</body>
</html>
