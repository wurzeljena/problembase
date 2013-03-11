<?php
	include 'tags.php';

	function tasklist($pb, $page) {
		$query = "SELECT problems.id, files.content AS problem, problems.proposed, proposers.name, "
			."proposers.location, proposers.country, letter, number, month, year,"
			."(SELECT COUNT(solutions.id) FROM solutions WHERE problems.id=solutions.problem_id) AS numsol, "
			."(SELECT COUNT(comments.user_id) FROM comments WHERE problems.id=comments.problem_id) AS numcomm, "
			."(SELECT group_concat(tag_id) FROM tag_list WHERE problems.id=tag_list.problem_id) AS tags "
			."FROM problems JOIN files ON problems.file_id=files.rowid "
			."LEFT JOIN proposers ON problems.proposer_id=proposers.id "
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

			if ($_REQUEST['proposer'] != "")
				$filter[] = "proposers.name LIKE '%".$_REQUEST['proposer']."%'";
			
			if ($_REQUEST['number'] != "") {
				list($month, $year) = explode("/", $_REQUEST['number']);
				if ($year > 50)		// translate YY to 19JJ/20JJ
					$year += 1900;
				if ($year <= 50)
					$year += 2000;
				$filter[] = "month = $month AND year = $year";
			}
			
			if ($_REQUEST['start'] != "")
				$filter[] = "proposed > '".$_REQUEST['start']."'";
			if ($_REQUEST['end'] != "")
				$filter[] = "proposed < '".$_REQUEST['end']."'";

			$tags = array_filter(explode(',', $_REQUEST['tags']));
			foreach ($tags as $tag)
				$filter[] = "$tag IN (SELECT tag_id FROM tag_list WHERE problems.id=tag_list.problem_id)";

			if (count($filter))
				$query .= " WHERE ".implode(" AND ", $filter);
		}

		// order entries
		$query .= " ORDER BY year DESC, month DESC";

		// show proper page
		$query .= " LIMIT 10 OFFSET ".(10*$page);

		$problems = $pb->query($query);

		$problem_id=0;
		while($problem = $problems->fetchArray(SQLITE3_ASSOC)) {
			print '<a class="textbox" href="task.php?id='.$problem['id'].'">';
			print '<div class="task problem_list">';
			print '<div class="info"><div class="tags">';
			tags($pb, $problem['tags']);
			print '</div>'.$problem['name'].", ".$problem['location'];
			if ($problem['country'] != "") print " (".$problem['country'].")";
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
	}

	if (isset($_REQUEST['page'])) {
		session_start();
		$pb = new SQLite3('sqlite/problembase.sqlite', '0666');
		tasklist($pb, $_REQUEST['page']);
	}
?>