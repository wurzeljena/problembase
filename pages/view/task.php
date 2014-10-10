<?php
	include '../../lib/master.php';
	$pb = load(LOAD_DB | INC_HEAD | INC_TAGS | INC_PROPOSERS | INC_TASKLIST | INC_SOLLIST);

	$id = (int)$_GET['id'];
	$problem = new Task($pb, $id);

	// if no such problem exists, throw a 404 error
	if (!$problem->is_valid())
		http_error(404, "Aufgabe nicht gefunden");

	// if the user isn't allowed to see it, throw a 403 error
	if (!$problem->access(ACCESS_READ))
		http_error(403);

	printhead("Aufgabe $id");
?>
<body>
	<?php printheader(); ?>

	<div class="center">
	<div id="panel">
	<?php
		drawMenu("sidemenu");
		if ($_SESSION['user_id'] == -1)
			$problem->tag_selector($pb);
	?>
	</div>

	<div class="content">
		<?php
			$tag_code = "";
			$problem->print_html($tag_code);
			if ($_SESSION['editor'])
				$problem->publish_form();
		?>
		<script> (function () {
			var taglist = document.getElementsByClassName("tags")[0];
			<?php print $tag_code; ?>
		})();</script>

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
</body>
</html>
