<?php
	session_start();
	include 'tags.php';
	include 'proposers.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title>Aufgabendatenbank - Aufgabe bearbeiten</title>
	<meta name="author" content="Wurzel e.V."/>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
	<link rel="stylesheet" type="text/css" href="pb.css"/>
	<link rel="icon" href="dw.ico"/>
	<script type="text/javascript" src="MathJax/MathJax.js?config=TeX-AMS_HTML"></script>
	<script type="text/javascript" src="fancy.js"></script>
</head>
<body>
	<?php include 'head.php'; ?>

	<?php
	$pb = new SQLite3('sqlite/problembase.sqlite', '0666');
	if (isset($_REQUEST['id'])) {
		$id = (int)$_REQUEST['id'];
		$problem = $pb->querySingle("SELECT * FROM problems WHERE id=".$id, true);
		$tags = get_tags($pb, $problem['id']);
	}
	?>

	<div class="content">
	<h2 class="task">Aufgabe bearbeiten</h2>
	<form class="task" id="task" title="Aufgabenformular" action="submit_problem.php" method="POST">
		<?php if (isset($id)) print "<input type='hidden' name='id' value='$id'>"; ?>
		<?php proposer_form($pb, 'task', isset($id) ? $problem['proposer_id'] : -1); ?>
		<input type="text" class="text" name="tag" placeholder="Tag hinzufügen" list="tag_datalist"/>
		<input type="button" value="+" onclick="addTag('task');">
		<?php tags_datalist($pb); ?>
		<span id="tags" style="margin:3px;">
			<input type="hidden" name="tags" value="<?php if (isset($id)) print $tags; ?>"/>
			<?php if (isset($id)) tags($pb, $tags, 'task'); ?>
		</span>
		<textarea class="text" name="problem" id="textarea" rows="20" cols="65" placeholder="Aufgabentext"
			style="height:200px;" onkeyup="Preview.Update()"><?php if (isset($id)) print $problem['problem']; ?></textarea>
		<div class="preview" id="preview"></div>
		<textarea class="text" name="remarks" id="textarea" rows="5" cols="65" placeholder="Anmerkungen"
			style="height:70px;"><?php if (isset($id)) print $problem['remarks']; ?></textarea>
		Vorgeschlagen am: <input type="date" class="text" name="proposed" placeholder="JJJJ-MM-TT" value="<?php if (isset($id)) print $problem['proposed']; ?>"/>
		<input type="submit" value="<?php if (isset($id)) print "Speichern"; else print "Erstellen"; ?>" style="float:right;"/>
		<input type="button" value="Verwerfen" style="float:right;" onclick="history.back();"/>
	</form>
	</div>

	<div id="panel">
		<?php if (isset($user_id))
			include "tagpanel.php";
		?>
	</div>

	<?php $pb->close(); ?>

	<script type="text/javascript">
		Preview.Init("textarea", "preview");
	</script>
</body>
</html>
