<?php
	session_start();
	include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/lib/database.php';
	if (!isset($_SESSION['user_id'])) {
		include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/pages/error403.php';
		exit();
	}
	$user_id = $_SESSION['user_id'];
	$pb = Problembase();

	// read parameters
	$id = $pb->escape($_GET["id"]);
	foreach(array("diff", "beauty", "know", "comment") as $key)
		$$key = $pb->escape($_POST[$key]);
	$editorial = isset($_POST['editorial']) ? 1 : 0;

	// write into db
	$pb->exec("DELETE FROM comments WHERE user_id=$user_id AND problem_id=$id");
	if (!isset($_POST["delete"]))
		$pb->exec("INSERT INTO comments (user_id, problem_id, difficulty, beauty, knowledge_required, comment, editorial) VALUES ($user_id, $id, $diff, $beauty, $know, '$comment', $editorial)");

	$pb->close();

	// redirect to task page
	header("Location: {$_SERVER["PBROOT"]}/$id/");
?>
