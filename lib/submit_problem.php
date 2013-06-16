<?php
	session_start();
	include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/lib/proposers.php';
	if (!isset($_SESSION['user_id']) || !$_SESSION['editor']) {
		include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/pages/error403.php';
		exit();
	}
	$pb = new SQLite3($_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/sqlite/problembase.sqlite');

	// read parameters
	if (isset($_GET["id"]))
		$id = $pb->escapeString($_GET["id"]);
	foreach(array("propnums", "proposer", "proposer_id", "location", "country",
			"tags", "problem", "remarks", "proposed") as $key)
		$$key = $_POST[$key];

	if (isset($_POST["delete"])) {
		$pb->exec("PRAGMA foreign_keys=on;");
		$pb->exec("DELETE FROM problems WHERE id=$id");
		header("Location: {$_SERVER["PBROOT"]}/");
	}
	else {
		// write proposer
		$proposer_id = writeproposers($pb, explode(",", $propnums), $proposer, $proposer_id, $location, $country);

		// write into db
		if (isset($id)) {
			$file_id = $pb->querySingle("SELECT file_id FROM problems WHERE id=$id");
			$pb->exec("UPDATE files SET content='{$pb->escapeString($problem)}' WHERE rowid=$file_id");
			$pb->exec("UPDATE problems SET remarks='{$pb->escapeString($remarks)}', proposed=date('$proposed') WHERE id=$id");
		}
		else {
			$pb->exec("INSERT INTO files(content) VALUES('{$pb->escapeString($problem)}')");
			$file_id = $pb->lastInsertRowID();
			$pb->exec("INSERT INTO problems(file_id, remarks, proposed, public) VALUES "
				."($file_id, '{$pb->escapeString($remarks)}', date('$proposed'), 0)");
			$id = $pb->lastInsertRowID();
		}

		// write proposers
		$pb->exec("DELETE FROM problemproposers WHERE problem_id=$id");
		$stmt = $pb->prepare("INSERT OR REPLACE INTO problemproposers (problem_id, proposer_id) VALUES ($id, :proposer)");
		foreach ($proposer_id as $value) {
			$stmt->bindValue(":proposer", $value, SQLITE3_INTEGER);
			$stmt->execute();
		}

		// write tags
		$pb->exec("DELETE FROM tag_list WHERE problem_id=$id");
		if ($tags != "") {
			$stmt = $pb->prepare("INSERT OR REPLACE INTO tag_list (problem_id, tag_id) VALUES ($id, :tag)");
			foreach (explode(",", $tags) as $value) {
				$stmt->bindValue(":tag", $value, SQLITE3_INTEGER);
				$stmt->execute();
			}
		}

		// redirect to task page
		header("Location: {$_SERVER["PBROOT"]}/$id/");
	}

	$pb->close();
?>
