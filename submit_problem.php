<?php
	session_start();
	include 'proposers.php';
	if (!isset($_SESSION['user_id']))
		die("&Auml;nderungen nur angemeldet m&ouml;glich!");
	$pb = new SQLite3('sqlite/problembase.sqlite', '0666');

	// read parameters
	$proposer = $proposer_id = $location = $country = [];
	foreach ($_REQUEST as $key=>$value)
		$$key = $value;

	if (isset($delete)) {
		$pb->exec("DELETE FROM problems WHERE id=$id");
		header('Location: index.php');
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
			$pb->exec("INSERT INTO problems(file_id, remarks, proposed) VALUES "
				."($file_id, '{$pb->escapeString($remarks)}', date('$proposed'))");
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

		// redirect to task.php
		header('Location: task.php?id='.$id);
	}

	$pb->close();
?>
