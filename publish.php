<?php
	session_start();
	if (!isset($_SESSION['user_id']) || !$_SESSION['editor'])
		die("&Auml;nderungen nur als Redakteur m&ouml;glich!");
	$pb = new SQLite3('sqlite/problembase.sqlite', '0666');

	// read parameters
	foreach ($_REQUEST as $key=>$value)
		$$key = $pb->escapeString($value);

	// write into db
	if ($volume == "")
		$pb->exec("DELETE FROM published WHERE problem_id=$id");
	else {
		list($month, $year) = explode("/", $volume);
		if ($year > 50)		// translate YY to 19JJ/20JJ
			$year += 1900;
		if ($year <= 50)
			$year += 2000;
		$pb->exec("INSERT OR REPLACE INTO published VALUES ($id, '$letter', $number, $month, $year)");
	}

	$pb->close();

	// redirect to task.php
	header('Location: task.php?id='.$id);
?>
