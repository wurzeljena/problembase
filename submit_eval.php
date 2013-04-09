<?php
	session_start();
	if (!isset($_SESSION['user_id'])) {
		include 'error403.php';
		exit();
	}
	$user_id = $_SESSION['user_id'];
	$pb = new SQLite3('sqlite/problembase.sqlite');

	// read parameters
	foreach ($_REQUEST as $key=>$value)
		$$key = $pb->escapeString($value);

	// write into db
	if (isset($delete))
		$pb->exec("DELETE FROM comments WHERE user_id=$user_id AND problem_id=$id");
	else
		$pb->exec("INSERT OR REPLACE INTO comments (user_id, problem_id, difficulty, beauty, knowledge_required, comment) VALUES ($user_id, $id, $diff, $beauty, $know, '$comment')");

	$pb->close();

	// redirect to task page
	header("Location: {$_SERVER["PBROOT"]}/$id/");
?>
