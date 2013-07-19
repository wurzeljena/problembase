<?php
	session_start();
	if (!isset($_SESSION['user_id']) || !$_SESSION['editor']) {
		include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/pages/error403.php';
		exit();
	}
	$pb = new SQLite3($_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/sqlite/problembase.sqlite');
	$pb->exec("BEGIN TRANSACTION");

	// read parameters
	$id = $pb->escapeString($_GET["id"]);
	foreach(array("letter", "number", "volume") as $key)
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

	// write "public" flag
	$public = isset($_POST['public']) ? 1 : 0;
	$pb->exec("UPDATE problems SET public=$public WHERE file_id=$id");

	$pb->exec("END TRANSACTION");
	$pb->close();

	// redirect to task page
	header("Location: {$_SERVER["PBROOT"]}/$id/");
?>
