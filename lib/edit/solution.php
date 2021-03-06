<?php
	include '../../lib/master.php';
	$pb = load(LOAD_DB | INC_PROPOSERS);

	if (!$_SESSION['editor'])
		http_error(403);

	// read parameters
	if (isset($_GET["id"]))
		$id = (int)$_GET["id"];
	$problem_id = (int)$_GET["problem_id"];
	$propnums = array_filter(explode(",", $_POST['propnums']), "strlen");
	$params = array_merge(array("solution", "remarks", "published", "picnums"),
		(count($propnums) ? array("proposer", "proposer_id", "location", "country") : array()));
	foreach($params as $key)
		$$key = $_POST[$key];

	// Textareas send \r\n as linebreaks, we want \n only.
	$solution = $pb->escape(str_replace("\r", "", $solution));
	$remarks = $pb->escape(str_replace("\r", "", $remarks));
	$public = isset($_POST['public']) ? 1 : 0;
	$picnums = array_filter(explode(",", $picnums), "strlen");

	$pb->exec("BEGIN");
	if (isset($_POST["delete"])) {
		$problem_id = $pb->querySingle("SELECT problem_id FROM solutions WHERE file_id=$id", false);
		$pb->exec("DELETE FROM solutions WHERE file_id=$id");
	}
	else {
		// write proposers
		$proposers = new ProposerList;
		if (count($propnums)) {
			$proposers->fromserialdata($propnums, $proposer, $proposer_id, $location, $country);
			$proposers->write($pb);
		}

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
			$pb->exec("UPDATE files SET content='$solution' WHERE rowid=$id");
			$pb->exec("UPDATE solutions SET remarks='$remarks', year=$year, month=$month, public=$public WHERE file_id=$id");
		}
		else {
			$pb->exec("INSERT INTO files(content) VALUES('$solution')");
			$id = $pb->lastInsertRowID("files", "rowid");
			$pb->exec("INSERT INTO solutions(file_id, problem_id, remarks, year, month, public) "
				."VALUES ($id, $problem_id, '$remarks', $year, $month, $public)");
		}

		// write proposers
		$proposers->set_for_file($pb, $id);

		// write pictures
		$pb->exec("DELETE FROM pictures WHERE file_id=$id");
		$stmt = $pb->prepare("INSERT INTO pictures (file_id, id, public, content) VALUES ($id, $1, $2, $3)");
		foreach ($picnums as $value) {
			$stmt->bind(1, $_POST['pic_id'][$value], SQLTYPE_INTEGER);
			$stmt->bind(2, isset($_POST['pic_public'][$value]) ? 1 : 0, SQLTYPE_INTEGER);
			$stmt->bind(3, $_POST['pic_content'][$value], SQLTYPE_TEXT);
			$stmt->exec();
		}
	}

	$pb->exec("END");
	$pb->close();

	// redirect to task page
	header("Location: ".WEBROOT."/problem/$problem_id");
?>
