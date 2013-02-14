<?php
	session_start();
	if (!isset($_SESSION['user_id']))
		die("Änderungen nur angemeldet möglich!");
	$user_id = $_SESSION['user_id'];
	$pb = new SQLite3('sqlite/problembase.sqlite', '0666');

	// read parameters
	foreach ($_REQUEST as $key=>$value)
		$$key = $value;

	// write into db
	if (isset($delete))
		$pb->exec("DELETE FROM comments WHERE user_id=$user_id AND problem_id=$id");
	else {
		$pb->exec("INSERT OR REPLACE INTO comments VALUES ($user_id, $id, $diff, $beauty, $know, '$comment')");
		$pb->close();
	}

	// redirect to task.php
	header('Location: task.php?id='.$id);
?>
