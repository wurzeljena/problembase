<?php
	include '../../lib/master.php';
	$pb = load(LOAD_DB | INC_HEAD | INC_TAGS | INC_PROPOSERS);

	// if user has no editor rights, throw a 403 error
	if (!$_SESSION['editor'])
		http_error(403);

	$tags = new TagList;
	if (isset($_GET['id'])) {
		$id = (int)$_GET['id'];
		$problem = $pb->querySingle("SELECT problems.*, files.content AS problem FROM problems JOIN files ON problems.file_id=files.rowid WHERE file_id=$id", true);
		$tags->from_file($pb, $id);
	}

	// if no such problem exists, throw a 404 error
	if (isset($id) && empty($problem))
		http_error(404, "Aufgabe nicht gefunden");

	printhead("Aufgabe ".(isset($id) ? "bearbeiten" : "erstellen"));
?>
<body>
	<?php printheader(); ?>

	<div class="center">
	<div id="panel">
	<?php drawMenu("sidemenu"); ?>
		<iframe src="<?=WEBROOT?>/tagpanel" style="border:none;overflow:hidden" width="270" height="270"></iframe>
	</div>

	<div class="content">
	<h2 class="task">Aufgabe bearbeiten</h2>
	<form class="task" id="task" title="Aufgabenformular" action="<?=WEBROOT?>/submit/<?= isset($id) ? $id:"" ?>" method="POST">
		<?php
			proposer_form($pb, "task", isset($id) ? $id : -1);
			tag_form($pb, "task", $tags);
		?>
		<textarea class="text" name="problem" id="text" rows="20" cols="65" placeholder="Aufgabentext"
			style="height:200px;" onkeyup="Preview.Update()"><?php if (isset($id)) print $problem['problem']; ?></textarea>
		<div class="preview" id="preview"></div>
		<textarea class="text" name="remarks" rows="5" cols="65" placeholder="Anmerkungen"
			title="Wenn keine Autoren angegeben sind, wird stattdessen diese Anmerkung gezeigt.
Enth&auml;lt sie eine '~', so wird die Autorenliste darum erg&auml;nzt, diese wird anstatt der Tilde eingef&uuml;gt."
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
