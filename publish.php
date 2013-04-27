<?php
	session_start();
	if (!isset($_SESSION['user_id']) || !$_SESSION['editor']) {
		include 'error403.php';
		exit();
	}
	$pb = new SQLite3('sqlite/problembase.sqlite');

	// read parameters
	foreach(array("id", "letter", "number", "volume") as $key)
		$$key = $pb->escapeString($_POST[$key]);

	// write into db
	if ($volume == "")
		$pb->exec("DELETE FROM published WHERE problem_id=$id");
	else {
		list($month, $year) = explode("/", $volume);
		if ($year > 50)		// translate YY to 19JJ/20JJ
			$year += 1900;
		else
			$year += 2000;
		$pb->exec("INSERT OR REPLACE INTO published (problem_id, letter, number, year, month) VALUES ($id, '$letter', $number, $year, $month)");
	}

	$pb->close();

	// redirect to task page
	header("Location: {$_SERVER["PBROOT"]}/$id/");
?>
