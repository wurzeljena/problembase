<?php
	include 'master.php';

	// The query parameter tells us what the user wants to know.
	switch ($_GET["query"]) {
	// ### PROBLEMS ###
	case "problemlist":            // Problem lists
		$pb = load(LOAD_DB | INC_PROBLEMS);
		header("Content-Type: text/html");

		$filter = new Filter($_GET['hash']);
		$tasklist = new ProblemList($pb, $filter->array,
			$_GET['page'] * TASKS_PER_PAGE, TASKS_PER_PAGE);
		$tasklist->print_html();
		break;

	case "issue_problems":         // Answer to TeX requests from issue pages
		$pb = load(LOAD_DB | INC_PROBLEMS);
		header("Content-Type: application/x-tex");
		header("Content-Disposition: attachment; filename=aufg"
			.str_pad($_GET['year']%100, 2, "0", STR_PAD_LEFT)
			.str_pad($_GET['month'], 2, "0", STR_PAD_LEFT).".tex");

		$filter = new Filter();
		$hash = $filter->set_params($_GET);
		$filter->construct_query($pb, array("number ASC"));
		$filter->filter(false);
		$tasklist = new ProblemList($pb, $filter->array);
		$tasklist->print_tex();
		break;

	// ### SOLUTIONS ###
	case "issue_solutions":        // Answer to TeX requests from issue pages
		$pb = load(LOAD_DB | INC_SOLUTIONS);
		header("Content-Type: application/x-tex");
		header("Content-Disposition: attachment; filename=loes"
			.(str_pad($_GET['year']%100, 2, "0", STR_PAD_LEFT)).str_pad($_GET['month'], 2, "0", STR_PAD_LEFT).".tex");

		if ($_GET['month']<7)	{ $year = $_GET['year'] - 1;	$period = 1;	}
		else					{ $year = $_GET['year'];		$period = 2;	}

		$sollist = new SolutionList($pb, array("year={$_GET['year']}", "month={$_GET['month']}"));
		$sollist->print_tex($year, $period);
		break;
	case "picture":                // Fetch a picture
		$pb = load(LOAD_DB);
		$id = (int)$_GET["id"];
		$solution_id = (int)$_GET["solution_id"];

		$picture = $pb->querySingle("SELECT content FROM pictures WHERE file_id=$solution_id "
			."AND id=$id".($_SESSION["editor"] ? "" : " AND public=1"), false);

		if ($picture) {
			header("Content-Type: application/x-metapost");
			header("Content-Disposition: attachment; filename=$id.mp");

			// print picture
			print "input tftools;\n\n";
			print "beginfig($id);\n  ";
			print str_replace("\n", "\n  ", $picture);	// indent
			print "\nendfig;\n";
		}
		else
			http_error(404, "Bild nicht gefunden");
		break;

	// ### PROPOSERS ###
	case "proposer":               // Answer to Ajax queries for proposers
		$pb = load(LOAD_DB | INC_PROPOSERS);
		$proposers = new ProposerList;
		$name = str_replace("_", " ", $_GET["name"]);
		$proposers->get($pb, array("id", "location", "country"), $name);
		header("Content-Type: application/json");
		print $proposers->json_encode();
		$pb->close();
		break;

	// ### TAGS ###
	case "tag":                    // Answer to Ajax queries for tags
		$pb = load(LOAD_DB | INC_TAGS);

		$tag = new Tag;
		$tag->from_name($pb, str_replace("_", " ", $_GET['name']));
		$res = $tag->json_encode();
		if ($res) {
			header("Content-Type: application/json");
			print $res;
		}
		else
			http_error(404, "Tag nicht gefunden");

		$pb->close();
		break;
	}
?>
