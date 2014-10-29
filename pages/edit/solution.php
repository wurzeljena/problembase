<?php
	include '../../lib/master.php';
	$pb = load(LOAD_DB | INC_HEAD | INC_PROPOSERS | INC_TAGS | INC_TASKLIST | INC_SOLLIST);

	$problem_id = (int)$_GET['problem_id'];
	$id = isset($_GET['id']) ? (int)$_GET['id']: -1;
	$solution = new Solution($pb, $id, $problem_id);

	// Return 404, if problem or solution not found
	if (!$solution->is_valid())
		http_error(404, "Aufgabe oder Lösung nicht gefunden");

	// Are we allowed to modify it?
	if (!$solution->access(ACCESS_MODIFY))
		http_error(403);

	printhead("Lösung ".(isset($id) ? "bearbeiten" : "erstellen"));
?>
<body>
	<?php printheader(); ?>

	<div class="center">
	<div id="panel">
	<?php drawMenu("sidemenu"); ?>
	</div>

	<div class="content">
	<h2 class="solution">Lösung bearbeiten</h2>
	<?php $solution->print_form($pb); ?>
	</div>

	<?php $pb->close(); ?>
</body>
</html>
