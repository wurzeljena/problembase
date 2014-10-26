<?php
	include '../master.php';

	// What shall we write?
	switch ($_GET["write"]) {
	case "tag":                    // Write tag from tag form
		$pb = load(LOAD_DB | INC_HEAD | INC_TAGS);

		$tag = new Tag;
		if ($_POST["old_name"] != "")
			$tag->from_name($pb, str_replace("_", " ", $_POST["old_name"]), ACCESS_MODIFY);

		if (isset($_POST['delete']))
			$success = $tag->delete($pb);
		else {
			// Get parameters from POST request and write them into $tag
			$names = array("name", "description", "color");
			$par = array_intersect_key($_POST, array_fill_keys($names, 0));
			$par["hidden"] = isset($_POST["hidden"]) ? 1 : 0;
			if (isset($_POST["private"]))
				$par["private_user"] =  $_SESSION["user_id"];
			else
				$par["private_user"] =  null;

			$success = true;
			if ($tag->set($par))
				$tag->write($pb);
			else
				$success = false;
		}

		$pb->close();

		if ($success)
			header("Location: {$_SERVER['HTTP_REFERER']}");
		else
			http_error(403);
		break;
	case "problemtag":             // POST from tag selector
		$pb = load(LOAD_DB | INC_TAGS);

		$tag = new Tag;
		$tag->from_name($pb, str_replace("_", " ", $_POST["tag"]));
		$tag->set_for_file($pb, (int)$_GET["id"], $_POST["set"]);
		$pb->close();
		break;
	}
?>
