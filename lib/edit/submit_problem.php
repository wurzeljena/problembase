<?php
	include '../../lib/master.php';
	$pb = load(LOAD_DB | INC_PROPOSERS | INC_TAGS);

	if (!$_SESSION['editor'])
		http_error(403);

	// read parameters
	if (isset($_GET["id"]))
		$id = (int)$_GET["id"];

	// post from tag selector?
	if (isset($_POST["tag"])) {
		$tag = new Tag;
		Tag::prepare_name_query($pb);
		$tag->from_name($_POST["tag"]);
		$tag->set_for_file($pb, $id, $_POST["set"]);
		$pb->close();
		exit();
	}

	// otherwise, posting from the problem form
	$propnums = array_filter(explode(",", $_POST['propnums']), "strlen");
	$params = array_merge(array("problem", "remarks", "proposed", "tags"),
		(count($propnums) ? array("proposer", "proposer_id", "location", "country") : array()));
	foreach($params as $key)
		$$key = $_POST[$key];

	$pb->exec("BEGIN");
	if (isset($_POST["delete"])) {
		$pb->exec("DELETE FROM problems WHERE file_id=$id");
		header("Location: ".WEBROOT."/");
	}
	else {
		// write proposers
		$proposers = new ProposerList;
		if (count($propnums)) {
			$proposers->fromserialdata($propnums, $proposer, $proposer_id, $location, $country);
			$proposers->write($pb);
		}

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
		$proposers->set_for_file($pb, $id);

		// write tags
		$taglist = new TagList;
		$taglist->from_list($pb, $tags);
		$taglist->set_for_file($pb, $id);

		// redirect to task page
		header("Location: ".WEBROOT."/$id/");
	}

	$pb->exec("END");
	$pb->close();
?>
