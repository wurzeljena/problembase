<?php
	$pb = new SQLite3('sqlite/problembase.sqlite', '0666');

	// read parameters
	foreach ($_REQUEST as $key=>$value)
		$$key = $value;

	// write into db
	if (isset($delete))
		$pb->exec("DELETE FROM comments WHERE user_id=1 AND problem_id=$id");
	else {
		$write_eval = $pb->prepare("INSERT OR REPLACE INTO comments VALUES (:user, :problem, :diff, :beauty, :know, :comm)");
		$write_eval->bindValue(':user', 1, SQLITE3_INTEGER);
		$write_eval->bindValue(':problem', $id, SQLITE3_INTEGER);
		$write_eval->bindValue(':diff', $diff, SQLITE3_INTEGER);
		$write_eval->bindValue(':beauty', $beauty, SQLITE3_INTEGER);
		$write_eval->bindValue(':know', $know, SQLITE3_INTEGER);
		$write_eval->bindValue(':comm', $comment, SQLITE3_TEXT);
		$write_eval->execute();
		$pb->close();
	}

	// redirect to task.php
	header('Location: task.php?id='.$id);
?>
