<?php
	include '../../lib/master.php';
	$pb = load(LOAD_DB | INC_HEAD | INC_PROBLEMS | INC_SOLUTIONS | INC_EVALUATIONS);

	$id = (int)$_GET['id'];
	$problem = new Problem($pb, $id);

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
	<aside id="panel">
	<?php
		drawMenu("sidemenu");
		if ($_SESSION['user_id'] != -1)
			$problem->tag_selector($pb);
	?>
	</aside>

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
		$sollist = new SolutionList($pb, array("problem_id=$id"));
		$sollist->print_html($_SESSION['editor']);

		// show buttons between solutions and comments
		if ($_SESSION['editor'])
			print "<a class='button' href='".WEBROOT."/problem/$id/solution'><i class='fa fa-book'></i> Lösung hinzufügen</a>";
		if ($_SESSION['user_id'] != -1 && !$pb->querySingle("SELECT * FROM comments WHERE user_id={$_SESSION['user_id']} AND problem_id=$id", false))
			print "<a class='button' style='float:right;' href='".WEBROOT."/problem/$id/evaluate'><i class='fa fa-comment'></i> Kommentar schreiben</a>";

		// if not logged in, separate comments from solutions with a lightweight header
		if (!$_SESSION['editor'])
			print "<h3 id='comments'><i class='fa fa-comments-o'></i> Kommentare</h3>";

		$evals = new EvalList;
		$evals->get_for_problem($pb, $id);
		$evals->print_html(false);
	?>

		<?php $pb->close(); ?>
	</div>
	</div>
</body>
</html>
