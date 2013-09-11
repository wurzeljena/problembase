<?php
	include '../../lib/master.php';
	$pb = load(LOAD_DB | INC_HEAD | INC_TAGS | INC_PROPOSERS | INC_SOLLIST);

	$id = (int)$_GET['id'];
	$problem = $pb->querySingle("SELECT problems.*, files.content AS problem FROM problems JOIN files ON problems.file_id=files.rowid WHERE file_id=$id", true);

	// if no such problem exists, throw a 404 error
	if (empty($problem))
		http_error(404, "Aufgabe nicht gefunden");

	// if the user isn't allowed to see it, throw a 403 error
	if (!$problem['public'] && !$_SESSION['editor'])
		http_error(403);

	printhead("Aufgabe $id");
?>
<body>
	<?php printheader(); ?>

	<div class="center">
	<div id="panel">
	<?php drawMenu("sidemenu"); ?>
	</div>

	<div class="content">
		<div class="task">
			<?php
				if ($_SESSION['editor'])
					print "<a class='button inner' href='".WEBROOT."/$id/edit'><i class='icon-pencil'></i> <span>Bearbeiten</span></a>";
			?>
			<div class="info">
			<div class="tags"></div>
			<?php tags($pb, get_tags($pb, $id)); ?>
			<?php printproposers($pb, "problem", $id); ?>
			</div>
			<div class="text" id="prob"><?php print htmlspecialchars($problem['problem']); ?></div>
			<div class="published">
			<?php
				$pub = $pb->querySingle("SELECT * FROM published WHERE problem_id=".$id, true);
				if ($pub && count($pub)) {
					$volume = $pub['month']."/".str_pad($pub['year']%100, 2, "0", STR_PAD_LEFT);
					$letter = $pub['letter'];
					$number = $pub['number'];
					print "Publiziert als $$letter\,$number$ im Heft $volume";
				}
				else {
					$volume = $number = $letter = "";
					print "Noch nicht publiziert";
				}
				print $problem['public'] ? "." : ", nicht &ouml;ffentlich.";
				if ($_SESSION['editor'])
					print "<a class='button danger' style='float:right' href='javascript:Publ.Show();'><i class='icon-globe'></i> <span>&Auml;ndern</span></a>";
			?>
			<form id="publish" style="display:none;" action="<?=WEBROOT?>/<?=$id?>/publish" method="POST">
				<input type="submit" style="float:right;" value="Speichern">
				<div style="display:inline;white-space:nowrap;">
				Im <label for="volume">Heft</label>
				<input type="text" class="text" id="volume" name="volume" placeholder="MM/JJ" pattern="([1-9]|0[1-9]|1[0-2])/[0-9]{2}"
					style="width:40px;" value="<?=$volume?>">
				</div>
				<div style="display:inline;white-space:nowrap;">
				<label for="letter">als</label>
				<input type="text" class="text" id="letter" name="letter" placeholder="Buchstabe"
					style="width:50px;" value="<?=$letter?>">
				<input type="text" class="text" name="number" placeholder="Nummer" pattern="[1-9]|[0-5][0-9]|60"
					style="width:20px;" value="<?=$number?>">,
				</div>
				<div style="display:inline;white-space:nowrap;">
				<input type="checkbox" name="public" id="public" <?=$problem['public'] ? "checked" : "";?>>
					<label for="public">&ouml;ffentlich</label>
				</div>
			</form>
			</div>
		</div>

		<?php
		$sollist = new SolutionList($pb);
		$sollist->idstr = $pb->querysingle("SELECT group_concat(file_id) FROM solutions WHERE problem_id=$id", false);
		$sollist->query($_SESSION['editor']);
		$sollist->print_html($_SESSION['editor']);

		// show buttons between solutions and comments
		if ($_SESSION['editor'])
			print "<a class='button' href='".WEBROOT."/$id/addsolution'><i class='icon-book'></i> L&ouml;sung hinzuf&uuml;gen</a>";
		if ($_SESSION['user_id'] != -1 && !$pb->querySingle("SELECT * FROM comments WHERE user_id={$_SESSION['user_id']} AND problem_id=$id", false))
			print "<a class='button' style='float:right;' href='".WEBROOT."/$id/evaluate'><i class='icon-comments'></i> Kommentar schreiben</a>";

		// if not logged in, separate comments from solutions with a lightweight header
		if (!$_SESSION['editor'])
			print "<h3 id='comments'><i class='icon-comment-alt'></i> Kommentare</h3>";

		$cond = $_SESSION['editor'] ? "" : " AND editorial=0";
		$comments = $pb->query("SELECT * FROM comments JOIN users ON comments.user_id=users.id WHERE problem_id=$id".$cond);
		while($comment=$comments->fetchAssoc()) {
			print "<div class='comment".($comment['user_id']==$_SESSION['user_id'] ? " own" : "")
				.($comment['editorial'] ? " editorial" : "")."'>";
			if ($comment['user_id']==$_SESSION['user_id'])
				print "<a class='button inner' href='".WEBROOT."/$id/evaluate'><i class='icon-pencil'></i> <span>Bearbeiten</span></a>";
			print "<div class='author'>{$comment['name']}";
			if ($_SESSION['user_id'] != -1)
				print " <a href='mailto:{$comment['email']}'><i class='icon-envelope-alt'></i></a>";
			print '</div><div class="text">'.htmlspecialchars($comment['comment']).'</div>';

			print '<div class="eval">';
			$critnames = array('Eleganz', 'Schwierigkeit', 'Wissen');
			$critcols = array('beauty', 'difficulty', 'knowledge_required');
			for ($crit=0; $crit<3; ++$crit) {
				print '<span class="evalspan">';
				print '<span class="eval">'.$critnames[$crit].'</span> ';
				for ($star=1; $star<=5; ++$star)
					print ($star<=$comment[$critcols[$crit]]) ?
						"<img class='star' src='".WEBROOT."/img/mandstar.png' alt='*'> " :
						"<img class='star' src='".WEBROOT."/img/mand.png' alt='o'> ";
				print '</span> ';
			}
			print '</div></div>';
		};
		?>

		<?php $pb->close(); ?>
	</div>
	</div>

	<script type="text/javascript">
		var Publ = new PopupTrigger("publish");
	</script>
</body>
</html>
