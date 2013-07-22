<?php
	include $_SERVER['DOCUMENT_ROOT'].$_ENV['PBROOT'].'/lib/master.php';
	$pb = load(LOAD_DB);

	if (!$_SESSION['editor'])
		http_error(403);

	$pb->exec("BEGIN");

	// read parameters
	$id = $pb->escape($_GET["id"]);
	foreach(array("letter", "number", "volume") as $key)
		$$key = $pb->escape($_POST[$key]);

	// write into db
	if ($volume == "")
		$pb->exec("DELETE FROM published WHERE problem_id=$id");
	else {
		list($month, $year) = explode("/", $volume);
		if ($year > 50)		// translate YY to 19JJ/20JJ
			$year += 1900;
		else
			$year += 2000;
		$pb->exec("DELETE FROM published WHERE problem_id=$id");
		$pb->exec("INSERT INTO published (problem_id, letter, number, year, month) VALUES ($id, '$letter', $number, $year, $month)");
	}

	// write "public" flag
	$public = isset($_POST['public']) ? 1 : 0;
	$pb->exec("UPDATE problems SET public=$public WHERE file_id=$id");

	$pb->exec("END");
	$pb->close();

	// redirect to task page
	header("Location: {$_SERVER["PBROOT"]}/$id/");
?>
