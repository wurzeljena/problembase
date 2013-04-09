<?php
	define("TASKS_PER_PAGE", 10);

	include 'tags.php';
	
	function taskquery($pb, $page) {
		$query = "SELECT problems.id, files.content AS problem, problems.proposed, letter, number, month, year,"
			."(SELECT COUNT(solutions.id) FROM solutions WHERE problems.id=solutions.problem_id) AS numsol, "
			."(SELECT COUNT(comments.user_id) FROM comments WHERE problems.id=comments.problem_id) AS numcomm, "
			."(SELECT group_concat(tag_id) FROM tag_list WHERE problems.id=tag_list.problem_id) AS tags "
			."FROM problems JOIN files ON problems.file_id=files.rowid "
			."LEFT JOIN published ON problems.id=published.problem_id";

		// add filter constraints
		if (isset($_REQUEST['filter'])) {
			$filter = array();

			if ($_REQUEST['filter'] != "") {
				$pb->exec("CREATE TEMPORARY TABLE filter AS "
					."SELECT id AS problem_id FROM problems JOIN files ON files.rowid=problems.file_id "
					."WHERE content MATCH '".$pb->escapeString($_REQUEST['filter'])."' "
					."UNION SELECT problem_id FROM solutions JOIN files ON files.rowid=solutions.file_id "
					."WHERE content MATCH '".$pb->escapeString($_REQUEST['filter'])."'");
				$filter[] = "problems.id IN filter";
			}

			if ($_REQUEST['proposer'] != "") {
				$pb->exec("CREATE TEMPORARY TABLE propfilter AS "
					."SELECT id AS problem_id FROM proposers WHERE name LIKE '%".$_REQUEST['proposer']."%'");
				$filter[] = "EXISTS (SELECT proposer_id FROM problemproposers WHERE problem_id=problems.id AND proposer_id IN propfilter)";
			}

			if ($_REQUEST['number'] != "") {
				list($month, $year) = explode("/", $_REQUEST['number']);
				if ($year > 50)		// translate YY to 19JJ/20JJ
					$year += 1900;
				if ($year <= 50)
					$year += 2000;
				$filter[] = "month = $month AND year = $year";
			}

			if (isset($_REQUEST['with_solution']))
				$filter[] = "EXISTS (SELECT solutions.id FROM solutions WHERE problems.id=solutions.problem_id)";

			if ($_REQUEST['start'] != "")
				$filter[] = "proposed > '".$_REQUEST['start']."'";
			if ($_REQUEST['end'] != "")
				$filter[] = "proposed < '".$_REQUEST['end']."'";

			$tags = array_filter(explode(',', $_REQUEST['tags']));
			foreach ($tags as $tag)
				$filter[] = "EXISTS (SELECT rowid FROM tag_list WHERE problems.id=tag_list.problem_id and tag_list.tag_id=$tag)";

			if (count($filter))
				$query .= " WHERE ".implode(" AND ", $filter);
		}

		// order entries
		$query .= " ORDER BY year DESC, month DESC";

		// show proper page
		$query .= " LIMIT ".TASKS_PER_PAGE." OFFSET ".(TASKS_PER_PAGE*$page);

		return $pb->query($query);
	}

	function tasklist($pb, $problems) {
		$problem_id=0;
		$tags = Array(TASKS_PER_PAGE);
		while($problem = $problems->fetchArray(SQLITE3_ASSOC)) {
			print "<a class='textbox' href='{$_SERVER["PBROOT"]}/{$problem['id']}/'>";
			print '<div class="task">';
			print '<div class="info">';
			print "<div class='tags'></div>";
			$tags[$problem_id] = $problem['tags'];
			printproposers($pb, "problem", $problem['id']);
			print '</div>';

			print '<div class="text" id="prob'.($problem_id++).'">';
			print htmlspecialchars($problem['problem']);
			print '<table class="info" style="margin-top:1em;"><tr>';
			print '<td style="width:70px; border:none;">'.$problem['proposed'].'</td>';

			// find out if published
			if (isset($problem['year']))
				print '<td style="width:200px;">Heft '.$problem['month'].'/'.$problem['year'].
					', Aufgabe $'.$problem['letter'].$problem['number'].'$</td>';
			else
				print '<td style="width:200px;">nicht publiziert</td>';

			$solstr = ($problem['numsol'] <= 1) ? ($problem['numsol'] ? "" : "k")."eine L&ouml;sung" : $problem['numsol']." L&ouml;sungen";
			$commstr = ($problem['numcomm'] <= 1) ? ($problem['numcomm'] ? "" : "k")."ein Kommentar" : $problem['numcomm']." Kommentare";
			print '<td style="width:200px;">'.$commstr.', '.$solstr.'</td>';
			print '</tr></table>';
			print '</div></div></a>';
		}

		print "<script id='tagscript'> (function () {";
		print "var taglists = document.getElementsByClassName('tags');";
		for (--$problem_id; $problem_id >= 0; --$problem_id)		// go backwards
			tags($pb, $tags[$problem_id], "taglists[$problem_id]");
		print "})();</script>";
	}

	if (isset($_REQUEST['page'])) {
		session_start();
		include 'proposers.php';
		$pb = new SQLite3('sqlite/problembase.sqlite');
		header("Content-Type: text/html; encoding=utf-8");
		tasklist($pb, taskquery($pb, $_REQUEST['page']));
	}
?>
