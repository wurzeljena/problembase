<?php
	session_start();
	include 'tags.php';
	include 'proposers.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Aufgabendatenbank - Aufgabe bearbeiten</title>
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
		$problem = $pb->querySingle("SELECT problems.*, files.content AS problem FROM problems JOIN files ON problems.file_id=files.rowid WHERE id=$id", true);
		$tags = get_tags($pb, $problem['id']);
	}
	?>

	<div class="content">
	<h2 class="task">Aufgabe bearbeiten</h2>
	<form class="task" id="task" title="Aufgabenformular" action="submit_problem.php" method="POST">
		<?php if (isset($id)) print "<input type='hidden' name='id' value='$id'>"; ?>
		<?php proposer_form($pb, 'task', isset($id) ? $problem['proposer_id'] : -1); ?>
		<?php tag_select($pb, "task"); ?>
		<input type="hidden" name="tags" value="<?php if (isset($id)) print $tags; ?>"/>
		<span id="taglist" style="margin:3px;">
			<?php if (isset($id)) tags($pb, $tags, 'task'); ?>
		</span>
		<textarea class="text" name="problem" id="textarea" rows="20" cols="65" placeholder="Aufgabentext"
			style="height:200px;" onkeyup="Preview.Update()"><?php if (isset($id)) print $problem['problem']; ?></textarea>
		<div class="preview" id="preview"></div>
		<textarea class="text" name="remarks" id="textarea" rows="5" cols="65" placeholder="Anmerkungen"
			style="height:70px;"><?php if (isset($id)) print $problem['remarks']; ?></textarea>
		Vorgeschlagen am: <input type="date" class="text" name="proposed" style="width:100px;" placeholder="JJJJ-MM-TT" value="<?php if (isset($id)) print $problem['proposed']; ?>"/>
		<input type="submit" value="<?php if (isset($id)) print "Speichern"; else print "Erstellen"; ?>" style="float:right;"/>
		<input type="button" value="Verwerfen" style="float:right;" onclick="history.back();"/>
	</form>
	</div>

	<div id="panel">
		<iframe src="tagpanel.php" style="border:none;" width="270" height="270"></iframe>
	</div>

	<?php $pb->close(); ?>

	<script type="text/javascript">
		Preview.Init("textarea", "preview");
	</script>
</body>
</html>
