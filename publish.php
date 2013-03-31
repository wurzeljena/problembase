<?php
	session_start();
	if (!isset($_SESSION['user_id']) || !$_SESSION['editor']) {
		include 'error403.php';
		exit();
	}
	$pb = new SQLite3('sqlite/problembase.sqlite');

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
		$pb->exec("INSERT OR REPLACE INTO published VALUES ($id, '$letter', $number, $year, $month)");
	}

	$pb->close();

	// redirect to task page
	header("Location: {$_SERVER["PBROOT"]}/$id/");
?>
