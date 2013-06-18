<?php
	session_start();

	// if user has no editor rights, throw a 403 error
	if (!isset($_SESSION['user_id']) || !$_SESSION['editor']) {
		include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/pages/error403.php';
		exit();
	}

	$pb = new SQLite3($_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/sqlite/problembase.sqlite');
	if (isset($_GET['id'])) {
		$id = (int)$_GET['id'];
		$problem = $pb->querySingle("SELECT problems.*, files.content AS problem FROM problems JOIN files ON problems.file_id=files.rowid WHERE id=$id", true);
	}

	// if no such problem exists, throw a 404 error
	if (isset($id) && empty($problem)) {
		$error = "Aufgabe nicht gefunden";
		include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/pages/error404.php';
		exit();
	}

	include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/lib/head.php';
	include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/lib/tags.php';
	include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/lib/proposers.php';
	printhead("Aufgabe ".(isset($id) ? "bearbeiten" : "erstellen"));
?>
<body>
	<?php printheader(); ?>

	<div class="center">
	<div id="panel">
	<?php drawMenu("sidemenu"); ?>
		<iframe src="<?=$_SERVER["PBROOT"]?>/tagpanel" style="border:none;overflow:hidden" width="270" height="210"></iframe>
	</div>

	<div class="content">
	<h2 class="task">Aufgabe bearbeiten</h2>
	<form class="task" id="task" title="Aufgabenformular" action="<?=$_SERVER["PBROOT"]?>/submit/<?= isset($id) ? $id:"" ?>" method="POST">
		<?php
			proposer_form($pb, "task", "problem", isset($id) ? $id : -1);
			if (isset($id))
				$tags = get_tags($pb, $problem['id']);
			else
				$tags = "";
			tag_form($pb, "task", $tags);
		?>
		<textarea class="text" name="problem" id="text" rows="20" cols="65" placeholder="Aufgabentext"
			style="height:200px;" onkeyup="Preview.Update()"><?php if (isset($id)) print $problem['problem']; ?></textarea>
		<div class="preview" id="preview"></div>
		<textarea class="text" name="remarks" rows="5" cols="65" placeholder="Anmerkungen"
			style="height:70px;"><?php if (isset($id)) print $problem['remarks']; ?></textarea>
		<label for="proposed">Vorgeschlagen am:</label> <input type="date" class="text" name="proposed" id="proposed" style="width:100px;"
			placeholder="JJJJ-MM-TT" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])"
			value="<?php if (isset($id)) print $problem['proposed']; else print date("Y-m-d"); ?>"/>
		<input type="submit" value="<?php if (isset($id)) print "Speichern"; else print "Erstellen"; ?>" style="float:right;"/>
		<?php if (isset($id)) {?>
		<input type="checkbox" name="delete"/>
		<input type="button" value="L&ouml;schen" style="float:right;"
			onclick="if (confirm('Aufgabe wirklich l&ouml;schen?')) postDelete('task');"/>
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
