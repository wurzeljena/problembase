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
	$propnums = array_filter(explode(",", $_POST['propnums']), "strlen");
	$params = array_merge(array("solution", "remarks", "published"),
		(count($propnums) ? array("proposer", "proposer_id", "location", "country") : array()));
	foreach($params as $key)
		$$key = $_POST[$key];

	if (isset($_POST["delete"])) {
		$pb->exec("PRAGMA foreign_keys=on;");
		$pb->exec("DELETE FROM problems WHERE file_id=$id");
		header("Location: {$_SERVER["PBROOT"]}/");
	}
	else {
		// write proposers
		if (count($propnums))
			$proposer_id = writeproposers($pb, $propnums, $proposer, $proposer_id, $location, $country);

		// write into db
		if (isset($id)) {
			$pb->exec("UPDATE files SET content='{$pb->escapeString($problem)}' WHERE rowid=$id");
			$pb->exec("UPDATE problems SET remarks='{$pb->escapeString($remarks)}', proposed=date('$proposed') WHERE id=$id");
		}
		else {
			$pb->exec("INSERT INTO files(content) VALUES('{$pb->escapeString($problem)}')");
			$id = $pb->lastInsertRowID();
			$pb->exec("INSERT INTO problems(file_id, remarks, proposed, public) VALUES "
				."($id, '{$pb->escapeString($remarks)}', date('$proposed'), 0)");
		}

		// write proposers
		$pb->exec("DELETE FROM fileproposers WHERE file_id=$id");
		$stmt = $pb->prepare("INSERT OR REPLACE INTO fileproposers (file_id, proposer_id) VALUES ($id, :proposer)");
		foreach ($propnums as $value) {
			$stmt->bindValue(":proposer", $proposer_id[$value], SQLITE3_INTEGER);
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

	$pb->exec("END TRANSACTION");
	$pb->close();
?>
