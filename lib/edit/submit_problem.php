<?php
	include $_SERVER['DOCUMENT_ROOT'].$_ENV['PBROOT'].'/lib/master.php';
	$pb = load(LOAD_DB | INC_PROPOSERS);

	if (!$_SESSION['editor'])
		http_error(403);

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
		header("Location: {$_ENV["PBROOT"]}/");
	}
	else {
		// write proposers
		if (count($propnums))
			$proposer_id = writeproposers($pb, $propnums, $proposer, $proposer_id, $location, $country);

		// write into db
		$proposed = $proposed ? "date('$proposed')" : "null";	// convert to date if not empty
		if (isset($id)) {
			$pb->exec("UPDATE files SET content='{$pb->escape($problem)}' WHERE rowid=$id");
			$pb->exec("UPDATE problems SET remarks='{$pb->escape($remarks)}', proposed=$proposed WHERE file_id=$id");
		}
		else {
			$pb->exec("INSERT INTO files(content) VALUES('{$pb->escape($problem)}')");
			$id = $pb->lastInsertRowID("files", "rowid");
			$pb->exec("INSERT INTO problems(file_id, remarks, proposed, public) VALUES "
				."($id, '{$pb->escape($remarks)}', $proposed, 0)");
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
		header("Location: {$_ENV["PBROOT"]}/$id/");
	}

	$pb->exec("END");
	$pb->close();
?>
