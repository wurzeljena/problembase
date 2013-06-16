<?php
	session_start();

	// if user isn't authenticated, throw a 403 error
	if (!isset($_SESSION['user_id'])) {
		include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/pages/error403.php';
		exit();
	}

	$pb = new SQLite3($_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/sqlite/problembase.sqlite');
	$problem_id = (int)$_GET['problem_id'];
	$problem = $pb->querySingle("SELECT problems.*, files.content AS problem FROM problems JOIN files ON problems.file_id=files.rowid WHERE id=$problem_id", true);
	if (empty($problem))
		$error = "Aufgabe nicht gefunden";

	if (isset($_GET['id'])) {
		$id = (int)$_GET['id'];
		$solution = $pb->querySingle("SELECT solutions.*, files.content AS solution FROM solutions JOIN files ON solutions.file_id=files.rowid WHERE id=$id", true);
		if (!isset($solution['problem_id']))
			$error = "L&ouml;sung nicht gefunden";
		if (isset($solution['problem_id']) && $solution['problem_id'] != $_GET['problem_id'])
			$error = "L&ouml;sung geh&ouml;rt zu anderer Aufgabe";
	}

	// answer invalid requests properly
	if (isset($error)) {
		include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/pages/error404.php';
		exit();
	}

	include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/lib/head.php';
	include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/lib/proposers.php';
	printhead("L&ouml;sung ".(isset($id) ? "bearbeiten" : "erstellen"));
?>
<body>
	<?php printheader(); ?>

	<div class="center">
	<div id="panel">
	<?php drawMenu("sidemenu"); ?>
	</div>

	<div class="content">
	<h2 class="solution">L&ouml;sung bearbeiten</h2>
	<form class="solution" id="solution" title="L&ouml;sungsformular"
		action="<?=$_SERVER["PBROOT"]?>/submit/<?php if (isset($id)) print $solution['problem_id']."/".$id; else print $problem_id."/"; ?>" method="POST">
		<div class="problem"><?php print htmlspecialchars($problem['problem']); ?></div>
		<?php proposer_form($pb, "solution", "solution", isset($id) ? $id : -1); ?>
		<textarea class="text" name="solution" id="text" rows="60" cols="80" placeholder="L&ouml;sungstext"
			style="height:400px;" onkeyup="Preview.Update()"><?php if (isset($id)) print $solution['solution']; ?></textarea> <br/>
		<div class="preview" id="preview"></div>
		<textarea class="text" name="remarks" rows="5" cols="65" placeholder="Anmerkungen"
			style="height:70px;"><?php if (isset($id)) print $solution['remarks']; ?></textarea>
		<label for="published">Ver&ouml;ffentlicht in:</label> <input type="text" class="text" name="published" id="published" placeholder="MM/JJ" pattern="([1-9]|0[1-9]|1[0-2])/[0-9]{2}" style="width:50px;" value="<?php if (isset($solution['month'])) print $solution['month']."/".($solution['year']%100); ?>"/>
		<input type="checkbox" name="public" id="public" <?php if (isset($id) && $solution['public']) print "checked"; ?>/>
			<label for="public">&ouml;ffentlich</label>
		<input type="submit" value="<?php if (isset($id)) print "Speichern"; else print "Erstellen"; ?>" style="float:right;"/>
		<?php if (isset($id)) {?>
		<input type="checkbox" name="delete"/>
		<input type="button" value="L&ouml;schen" style="float:right;"
			onclick="if (confirm('L&ouml;sung wirklich l&ouml;schen?')) postDelete('solution');"/>
		<?php } ?>
	</form>
	</div>
	</div>

	<?php $pb->close(); ?>

	<script type="text/javascript">
		Preview.Init("text", "preview");
		Preview.Update();
	</script>
</body>
</html>