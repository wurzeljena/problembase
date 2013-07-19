<?php
	session_start();
	include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/lib/database.php';
	include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/lib/proposers.php';
	if (!isset($_SESSION['user_id']) || !$_SESSION['editor']) {
		include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/pages/error403.php';
		exit();
	}
	$pb = Problembase();
	$pb->exec("BEGIN");

	// read parameters
	if (isset($_GET["id"]))
		$id = $pb->escape($_GET["id"]);
	$propnums = array_filter(explode(",", $_POST['propnums']), "strlen");
	$params = array_merge(array("problem", "remarks", "proposed", "tags"),
		(count($propnums) ? array("proposer", "proposer_id", "location", "country") : array()));
	foreach($params as $key)
		$$key = $_POST[$key];

	if (isset($_POST["delete"])) {
		$pb->exec("DELETE FROM problems WHERE file_id=$id");
		header("Location: {$_SERVER["PBROOT"]}/");
	}
	else {
		// write proposers
		if (count($propnums))
			$proposer_id = writeproposers($pb, $propnums, $proposer, $proposer_id, $location, $country);

		// write into db
		if (isset($id)) {
			$pb->exec("UPDATE files SET content='{$pb->escape($problem)}' WHERE rowid=$id");
			$pb->exec("UPDATE problems SET remarks='{$pb->escape($remarks)}', proposed=date('$proposed') WHERE file_id=$id");
		}
		else {
			$pb->exec("INSERT INTO files(content) VALUES('{$pb->escape($problem)}')");
			$id = $pb->lastInsertRowID("files", "rowid");
			$pb->exec("INSERT INTO problems(file_id, remarks, proposed, public) VALUES "
				."($id, '{$pb->escape($remarks)}', date('$proposed'), 0)");
		}

		// write proposers
		$pb->exec("DELETE FROM fileproposers WHERE file_id=$id");
		$stmt = $pb->prepare("INSERT INTO fileproposers (file_id, proposer_id) VALUES ($id, $1)");
		foreach ($propnums as $value) {
			$stmt->bind(1, $proposer_id[$value], SQLTYPE_INTEGER);
			$stmt->exec();
		}

		// write tags
		$pb->exec("DELETE FROM tag_list WHERE problem_id=$id");
		if ($tags != "") {
			$stmt = $pb->prepare("INSERT INTO tag_list (problem_id, tag_id) VALUES ($id, $1)");
			foreach (explode(",", $tags) as $value) {
				$stmt->bind(1, $value, SQLTYPE_INTEGER);
				$stmt->exec();
			}
		}

		// redirect to task page
		header("Location: {$_SERVER["PBROOT"]}/$id/");
	}

	$pb->exec("END");
	$pb->close();
?>
