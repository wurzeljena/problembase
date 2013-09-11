<?php
	include '../../lib/master.php';
	$pb = load(LOAD_DB | INC_HEAD);

	// if user isn't authenticated, throw a 403 error
	if ($_SESSION['user_id'] == -1)
		http_error(403);

	$id = (int)$_GET['id'];
	$problem = $pb->querySingle("SELECT problems.*, files.content AS problem FROM problems JOIN files ON problems.file_id=files.rowid WHERE file_id=$id", true);
	$comment = $pb->querySingle("SELECT * FROM comments WHERE user_id={$_SESSION['user_id']} AND problem_id=$id", true);
	$pb->close();

	// if no such problem exists, throw a 404 error
	if (empty($problem))
		http_error(404, "Aufgabe nicht gefunden");

	printhead("Aufgabe bewerten");
?>
<body>
	<?php printheader(); ?>

	<div class="center">
	<div id="panel">
	<?php drawMenu("sidemenu"); ?>
	</div>

	<div class="content">
	<h2 class="eval">Aufgabe bewerten</h2>
	<form class="eval" id="eval" title="Bewertungsformular" action="<?=WEBROOT?>/submit/<?=$id?>/eval" method="POST">
		<div class="problem" id="prob"><?php print htmlspecialchars($problem['problem']); ?></div>
		<?php
			$critnames = array('Eleganz', 'Schwierigkeit', 'Wissen');
			$critcols = array('beauty', 'difficulty', 'knowledge_required');
			$critobj = array('Beauty', 'Diff', 'Know');
			$critid = array('beauty', 'diff', 'know');
			for ($crit=0; $crit<3; ++$crit) {
				print "<span class='evalspan'>";
				print "<label class='eval' for='{$critid[$crit]}stars'>{$critnames[$crit]}</label>";
				print "<span id='{$critid[$crit]}stars' onmouseout='{$critobj[$crit]}.reset();'>";
				for ($star=1; $star<=5; ++$star)
					print "<img class='star' id='{$critid[$crit]}$star' "
						."onmouseover='{$critobj[$crit]}.show($star);' onclick='{$critobj[$crit]}.set($star);'/> ";
				print "</span>";
				print "<input type='hidden' name='{$critid[$crit]}' id='{$critid[$crit]}' value='".
					(empty($comment) ? -1 : $comment[$critcols[$crit]])."'/>";
				print "</span> ";
			}
		?>
		<textarea name="comment" rows="10" cols="80" placeholder="Kommentar" style="height:100px;"><?php
			if (!empty($comment)) print $comment['comment']?></textarea> <br/>
		<input type="checkbox" name="editorial" id="editorial" <?=(!empty($comment) && $comment['editorial']) ? "checked":""?>/>
			<label for="editorial" style="font-size:small;">redaktionsintern</label>
		<input type="submit" value="Speichern" style="float:right;"/>
		<?php if (isset($id)) {?>
		<input type="checkbox" name="delete"/>
		<input type="button" value="L&ouml;schen" style="float:right;"
			onclick="if (confirm('Kommentar wirklich l&ouml;schen?')) postDelete('eval');"/>
		<?php } ?>
	</form>
	</div>
	</div>

	<script type="text/javascript">
		var Beauty = new Stars("beauty");
		var Diff = new Stars("diff");
		var Know = new Stars("know");
	</script>
</body>
</html>
