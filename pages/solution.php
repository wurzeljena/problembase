<?php
	session_start();
	include $_SERVER['DOCUMENT_ROOT'].$_ENV['PBROOT'].'/lib/database.php';

	// if user has no editor rights, throw a 403 error
	if (!isset($_SESSION['user_id']) || !$_SESSION['editor']) {
		include $_SERVER['DOCUMENT_ROOT'].$_ENV['PBROOT'].'/pages/error403.php';
		exit();
	}

	$pb = Problembase();
	$problem_id = (int)$_GET['problem_id'];
	$problem = $pb->querySingle("SELECT problems.*, files.content AS problem FROM problems JOIN files ON problems.file_id=files.rowid WHERE file_id=$problem_id", true);
	if (empty($problem))
		$error = "Aufgabe nicht gefunden";

	if (isset($_GET['id'])) {
		$id = (int)$_GET['id'];
		$solution = $pb->querySingle("SELECT solutions.*, files.content AS solution FROM solutions JOIN files ON solutions.file_id=files.rowid WHERE file_id=$id", true);
		if (!isset($solution['problem_id']))
			$error = "L&ouml;sung nicht gefunden";
		if (isset($solution['problem_id']) && $solution['problem_id'] != $_GET['problem_id'])
			$error = "L&ouml;sung geh&ouml;rt zu anderer Aufgabe";
	}

	// answer invalid requests properly
	if (isset($error)) {
		include $_SERVER['DOCUMENT_ROOT'].$_ENV['PBROOT'].'/pages/error404.php';
		exit();
	}

	include $_SERVER['DOCUMENT_ROOT'].$_ENV['PBROOT'].'/lib/head.php';
	include $_SERVER['DOCUMENT_ROOT'].$_ENV['PBROOT'].'/lib/proposers.php';
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
		<?php proposer_form($pb, "solution", isset($id) ? $id : -1); ?>

		<textarea class="text" name="solution" id="text" rows="60" cols="80" placeholder="L&ouml;sungstext"
			style="height:400px;" onkeyup="Preview.Update()"><?php if (isset($id)) print $solution['solution']; ?></textarea> <br/>
		<div class="preview" id="preview"></div>
		<textarea class="text" name="remarks" rows="5" cols="65" placeholder="Anmerkungen"
			title="Wenn keine Autoren angegeben sind, wird stattdessen diese Anmerkung gezeigt.
Enth&auml;lt sie eine '~', so wird die Autorenliste darum erg&auml;nzt, diese wird anstatt der Tilde eingef&uuml;gt."
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

	<input type="hidden" name="picnums" form="solution">
	<!-- here come the figure forms... -->
	</div>

	<a class="button" href="javascript:picForm.addPic();"><i class="icon-plus-sign"></i> Grafik hinzuf&uuml;gen</a>
	</div>

	<script type="text/javascript">
		Preview.Init("text", "preview");
		Preview.Update();

		var picForm = new Pictures("solution", [<?php
		if (isset($id)) {
			$num = 0;
			$pics = $pb->query("SELECT * FROM pictures WHERE file_id=$id");
			while($pic = $pics->fetchAssoc())
				print (($num++ > 0) ? ", " : "").json_encode($pic);
		}	?>]);
	</script>

	<?php $pb->close(); ?>
</body>
</html>
