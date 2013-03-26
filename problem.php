<?php
	session_start();
	include 'head.php';
	include 'tags.php';
	include 'proposers.php';

	printhead();
?>
<body>
	<?php printheader(); ?>

	<?php
	$pb = new SQLite3('sqlite/problembase.sqlite');
	if (isset($_REQUEST['id'])) {
		$id = (int)$_REQUEST['id'];
		$problem = $pb->querySingle("SELECT problems.*, files.content AS problem FROM problems JOIN files ON problems.file_id=files.rowid WHERE id=$id", true);
		$tags = get_tags($pb, $problem['id']);
	}
	?>

	<div class="content">
	<h2 class="task">Aufgabe bearbeiten</h2>
	<form class="task" id="task" title="Aufgabenformular" action="<?=$_SERVER["PBROOT"]?>/submit_problem.php" method="POST">
		<?php if (isset($id)) print "<input type='hidden' name='id' value='$id'>"; ?>
		<?php proposer_form($pb, "task", "problem", isset($id) ? $id : -1); ?>
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
		<label for="proposed">Vorgeschlagen am:</label> <input type="date" class="text" name="proposed" id="proposed" style="width:100px;" placeholder="JJJJ-MM-TT" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" value="<?php if (isset($id)) print $problem['proposed']; ?>"/>
		<input type="submit" value="<?php if (isset($id)) print "Speichern"; else print "Erstellen"; ?>" style="float:right;"/>
		<?php if (isset($id)) {?>
		<input type="checkbox" name="delete"/>
		<input type="button" value="L&ouml;schen" style="float:right;"
			onclick="if (confirm('Aufgabe wirklich l&ouml;schen?')) postDelete('task');"/>
		<?php } ?>
	</form>
	</div>

	<div id="panel">
		<iframe src="<?=$_SERVER["PBROOT"]?>/tagpanel.php" style="border:none;" width="270" height="270"></iframe>
	</div>

	<?php $pb->close(); ?>

	<script type="text/javascript">
		Preview.Init("textarea", "preview");
	</script>
</body>
</html>
