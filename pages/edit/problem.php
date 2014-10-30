<?php
	include '../../lib/master.php';
	$pb = load(LOAD_DB | INC_HEAD | INC_PROBLEMS);

	// Get problem
	$id = isset($_GET['id']) ? (int)$_GET['id'] : -1;
	$problem = new Problem($pb, $id);

	// If no such problem exists, throw a 404 error
	if ($id != -1 && !$problem->is_valid())
		http_error(404, "Aufgabe nicht gefunden");

	// Are we allowed to modify it?
	if (!$problem->access(ACCESS_MODIFY))
		http_error(403);

	printhead("Aufgabe ".($id != -1 ? "bearbeiten" : "erstellen"));
?>
<body>
	<?php printheader(); ?>

	<div class="center">
	<div id="panel">
	<?php drawMenu("sidemenu"); ?>
		<iframe src="<?=WEBROOT?>/tags?iframe" style="border:none;overflow:hidden" width="270" height="270"></iframe>
	</div>

	<div class="content">
	<h2 class="task">Aufgabe bearbeiten</h2>
	<?php $problem->print_form($pb); ?>
	</div>
	</div>

	<?php $pb->close(); ?>
</body>
</html>
