<?php
	define("TASKS_PER_PAGE", 10);

	include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/lib/tags.php';

	// filter tasks, save the result in the session cache and return the index
	function taskfilter($pb) {
		$query = "SELECT problems.id, problems.proposed, month, year "
			."FROM problems LEFT JOIN published ON problems.id=published.problem_id";

		// add filter constraints
		$filter = array();
		$public = !isset($_SESSION['user_id']) || !$_SESSION['editor'];
		if (isset($_GET['filter'])) {
			if ($_GET['filter'] != "") {
				$pb->exec("CREATE TEMPORARY TABLE filter AS "
					."SELECT id AS problem_id FROM problems JOIN files ON files.rowid=problems.file_id "
					."WHERE content MATCH '{$pb->escapeString($_GET['filter'])}' "
					."UNION SELECT problem_id FROM solutions JOIN files ON files.rowid=solutions.file_id "
					."WHERE content MATCH '{$pb->escapeString($_GET['filter'])}'");
				$filter[] = "problems.id IN filter";
			}

			if ($_GET['proposer'] != "") {
				$pb->exec("CREATE TEMPORARY TABLE propfilter AS "
					."SELECT id AS problem_id FROM proposers WHERE name LIKE '%{$pb->escapeString($_GET['proposer'])}%'");
				$filter[] = "EXISTS (SELECT proposer_id FROM problemproposers WHERE problem_id=problems.id AND proposer_id IN propfilter)";
			}

			if ($_GET['number'] != "") {
				list($month, $year) = explode("/", $_GET['number']);
				if ($year > 50)		// translate YY to 19JJ/20JJ
					$year += 1900;
				else
					$year += 2000;
				$filter[] = "month = $month AND year = $year";
			}

			if (isset($_GET['with_solution']))
				$filter[] = "EXISTS (SELECT solutions.id FROM solutions WHERE problems.id=solutions.problem_id "
					.($public ? " AND public=1" : "").")";

			if ($_GET['start'] != "")
				$filter[] = "proposed > '{$pb->escapeString($_GET['start'])}'";
			if ($_GET['end'] != "")
				$filter[] = "proposed < '{$pb->escapeString($_GET['end'])}'";

			$tags = array_filter(explode(',', $_GET['tags']));
			foreach ($tags as $tag)
				$filter[] = "EXISTS (SELECT rowid FROM tag_list WHERE problems.id=tag_list.problem_id and tag_list.tag_id={$pb->escapeString($tag)})";
		}

		if ($public)
			$filter[] = "public=1";
		if (count($filter))
			$query .= " WHERE ".implode(" AND ", $filter);

		// order entries
		$query .= " ORDER BY year DESC, month DESC";

		// write results to array
		$res = $pb->query($query);
		$array = array();
		while ($problem = $res->fetchArray(SQLITE3_ASSOC))
			$array[] = $problem['id'];

		// save in session
		$hash = md5($_SERVER['QUERY_STRING']);
		$_SESSION['cache'][$hash] = $array;
		return $hash;
	}

	// get the data for a specific task page
	function taskquery($pb, $hash, $page) {
		$ids = array_slice($_SESSION['cache'][$hash], TASKS_PER_PAGE*$page, TASKS_PER_PAGE);
		$idstr = implode(",", $ids);

		$query = "SELECT problems.id, files.content AS problem, problems.proposed, public, letter, number, month, year, "
			."(SELECT COUNT(solutions.id) FROM solutions WHERE problems.id=solutions.problem_id) AS numsol, "
			."(SELECT COUNT(comments.user_id) FROM comments WHERE problems.id=comments.problem_id) AS numcomm, "
			."(SELECT group_concat(tag_id) FROM tag_list WHERE problems.id=tag_list.problem_id) AS tags "
			."FROM problems JOIN files ON problems.file_id=files.rowid "
			."LEFT JOIN published ON problems.id=published.problem_id "
			."WHERE problems.id IN ($idstr) ORDER BY year DESC, month DESC";

		return $pb->query($query);
	}

	// print given tasks as HTML
	function tasklist($pb, $problems) {
		$problem_id=0;
		$tags = Array(TASKS_PER_PAGE);
		while($problem = $problems->fetchArray(SQLITE3_ASSOC)) {
			print "<a class='textbox' href='{$_SERVER["PBROOT"]}/{$problem['id']}/'>";
			print '<div class="task '.($problem['public'] ? "" : "nonpublic").'">';
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

	if (isset($_GET['page'])) {
		session_start();
		include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/lib/proposers.php';
		$pb = new SQLite3($_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/sqlite/problembase.sqlite');
		header("Content-Type: text/html; encoding=utf-8");
		tasklist($pb, taskquery($pb, $_GET['hash'], $_GET['page']));
	}
?>
