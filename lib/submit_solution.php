<?php
	session_start();
	include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/lib/proposers.php';
	if (!isset($_SESSION['user_id']) || !$_SESSION['editor']) {
		include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/pages/error403.php';
		exit();
	}
	$pb = new SQLite3($_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/sqlite/problembase.sqlite');
	$pb->exec("BEGIN TRANSACTION");

	// read parameters
	if (isset($_GET["id"]))
		$id = $pb->escapeString($_GET["id"]);
	$problem_id = $pb->escapeString($_GET["problem_id"]);
	foreach(array("propnums", "proposer", "proposer_id", "location", "country",
			"solution", "remarks", "published") as $key)
		$$key = $_POST[$key];
	$public = isset($_POST['public']) ? 1 : 0;

	if (isset($_POST["delete"])) {
		$pb->exec("PRAGMA foreign_keys=on;");
		$problem_id = $pb->querySingle("SELECT problem_id FROM solutions WHERE file_id=$id", false);
		$pb->exec("DELETE FROM solutions WHERE file_id=$id");
	}
	else {
		// write proposer
		$proposer_id = writeproposers($pb, explode(",", $propnums), $proposer, $proposer_id, $location, $country);

		// write into db
		if ($published != "") {
			list($month, $year) = explode("/", $published);
			if ($year > 50)		// translate YY to 19JJ/20JJ
				$year += 1900;
			if ($year <= 50)
				$year += 2000;
		}
		else
			$month = $year = "NULL";
		if (isset($id)) {
			$pb->exec("UPDATE files SET content='{$pb->escapeString($solution)}' WHERE rowid=$id");
			$pb->exec("UPDATE solutions SET remarks='{$pb->escapeString($remarks)}', year=$year, month=$month, public=$public WHERE file_id=$id");
		}
		else {
			$pb->exec("INSERT INTO files(content) VALUES('{$pb->escapeString($solution)}')");
			$id = $pb->lastInsertRowID();
			$pb->exec("INSERT INTO solutions(file_id, problem_id, remarks, year, month, public) "
				."VALUES ($id, $problem_id, '{$pb->escapeString($remarks)}', $year, $month, $public)");
		}

		// write proposers
		if (isset($id))
			$pb->exec("DELETE FROM fileproposers WHERE file_id=$id");
		$stmt = $pb->prepare("INSERT OR REPLACE INTO fileproposers (file_id, proposer_id) VALUES ($id, :proposer)");
		foreach ($proposer_id as $value) {
			$stmt->bindValue(":proposer", $value, SQLITE3_INTEGER);
			$stmt->execute();
		}
	}

	$pb->exec("END TRANSACTION");
	$pb->close();

	// redirect to task page
	header("Location: {$_SERVER["PBROOT"]}/$problem_id/");
?>
