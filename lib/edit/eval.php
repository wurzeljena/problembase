<?php
	include '../../lib/master.php';
	$pb = load(LOAD_DB);

	if ($_SESSION['user_id'] == -1)
		http_error(403);

	// read parameters
	$id = $pb->escape($_GET["id"]);
	foreach(array("diff", "beauty", "know", "comment") as $key)
		$$key = $pb->escape($_POST[$key]);
	$editorial = isset($_POST['editorial']) ? 1 : 0;

	// write into db
	$pb->exec("DELETE FROM comments WHERE user_id={$_SESSION['user_id']} AND problem_id=$id");
	if (!isset($_POST["delete"]))
		$pb->exec("INSERT INTO comments (user_id, problem_id, difficulty, beauty, knowledge_required, comment, editorial) VALUES ({$_SESSION['user_id']}, $id, $diff, $beauty, $know, '$comment', $editorial)");

	$pb->close();

	// redirect to task page
	header("Location: ".WEBROOT."/problem/$id");
?>
