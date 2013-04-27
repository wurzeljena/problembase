<?php
	session_start();
	include 'proposers.php';
	if (!isset($_SESSION['user_id'])) {
		include 'error403.php';
		exit();
	}
	$pb = new SQLite3('sqlite/problembase.sqlite');

	// read parameters
	foreach(array("id", "problem_id", "propnums", "proposer", "proposer_id", "location", "country",
			"tags", "solution", "remarks", "published") as $key)
		$$key = $_POST[$key];

	if (isset($_POST["delete"])) {
		$pb->exec("PRAGMA foreign_keys=on;");
		$problem_id = $pb->querySingle("SELECT problem_id FROM solutions WHERE id=$id", false);
		$pb->exec("DELETE FROM solutions WHERE id=$id");
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
			$file_id = $pb->querySingle("SELECT file_id FROM solutions WHERE id=$id");
			$pb->exec("UPDATE files SET content='{$pb->escapeString($solution)}' WHERE rowid=$file_id");
			$pb->exec("UPDATE solutions SET remarks='{$pb->escapeString($remarks)}', year=$year, month=$month WHERE id=$id");
		}
		else {
			$pb->exec("INSERT INTO files(content) VALUES('{$pb->escapeString($solution)}')");
			$file_id = $pb->lastInsertRowID();
			$pb->exec("INSERT INTO solutions(problem_id, file_id, remarks, year, month) "
				."VALUES ($problem_id, $file_id, '{$pb->escapeString($remarks)}', $year, $month)");
			$id = $pb->lastInsertRowID();
		}

		// write proposers
		if (isset($id))
			$pb->exec("DELETE FROM solutionproposers WHERE solution_id=$id");
		$stmt = $pb->prepare("INSERT OR REPLACE INTO solutionproposers (solution_id, proposer_id) VALUES ($id, :proposer)");
		foreach ($proposer_id as $value) {
			$stmt->bindValue(":proposer", $value, SQLITE3_INTEGER);
			$stmt->execute();
		}
	}

	$pb->close();

	// redirect to task page
	header("Location: {$_SERVER["PBROOT"]}/$problem_id/");
?>
