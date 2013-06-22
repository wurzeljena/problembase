<?php
	define("TASKS_PER_PAGE", 10);

	include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/lib/tags.php';

	class Filter {
		private $par;		// parameter list
		private $hash;		// ... and its hash
		private $query;		// SQLite query
		public $array;		// result array

		function __construct($hash = null) {
			$this->hash = $hash;
			if ($hash) {
				$cache = $_SESSION['cache'][$hash];
				$this->par = $cache['filter'];
				$this->array = $cache['data'];
			}
		}

		// construct from given parameter list (could be a GET request)
		function set_params($par) {
			$names = array('filter', 'proposer', 'number',
				'with_solution', 'start', 'end', 'tags');
			$filter = array_intersect_key($par, array_fill_keys($names, 0));
			$this->par = array_filter($filter);
			$this->hash = md5(serialize($this->par));
			return $this->hash;
		}

		// translate filter criterions to SQL
		function construct_query($pb, $order = null) {
			$query = "SELECT problems.id, problems.proposed, month, year "
				."FROM problems LEFT JOIN published ON problems.id=published.problem_id";

			// add filter constraints
			$filter = array();

			$public = !isset($_SESSION['user_id']) || !$_SESSION['editor'];
			if ($public)
				$filter[] = "public=1";

			if (isset($this->par['filter'])) {
				$pb->exec("CREATE TEMPORARY TABLE filter AS "
					."SELECT id AS problem_id FROM problems JOIN files ON files.rowid=problems.file_id "
					."WHERE content MATCH '{$pb->escapeString($this->par['filter'])}' "
					."UNION SELECT problem_id FROM solutions JOIN files ON files.rowid=solutions.file_id "
					."WHERE content MATCH '{$pb->escapeString($this->par['filter'])}'");
				$filter[] = "problems.id IN filter";
			}

			if (isset($this->par['proposer'])) {
				$pb->exec("CREATE TEMPORARY TABLE propfilter AS "
					."SELECT id AS problem_id FROM proposers WHERE name LIKE '%{$pb->escapeString($this->par['proposer'])}%'");
				$filter[] = "EXISTS (SELECT proposer_id FROM problemproposers WHERE problem_id=problems.id AND proposer_id IN propfilter)";
			}

			if (isset($this->par['number'])) {
				list($month, $year) = explode("/", $this->par['number']);
				if ($year > 50)		// translate YY to 19JJ/20JJ
					$year += 1900;
				else
					$year += 2000;
				$filter[] = "month = $month AND year = $year";
			}

			if (isset($this->par['with_solution']))
				$filter[] = "EXISTS (SELECT solutions.id FROM solutions WHERE problems.id=solutions.problem_id "
					.($public ? " AND public=1" : "").")";

			if (isset($this->par['start']))
				$filter[] = "proposed > '{$pb->escapeString($this->par['start'])}'";
			if (isset($this->par['end']))
				$filter[] = "proposed < '{$pb->escapeString($this->par['end'])}'";

			if (isset($this->par['tags'])) {
				$tags = array_filter(explode(',', $this->par['tags']));
				foreach ($tags as $tag)
					$filter[] = "EXISTS (SELECT rowid FROM tag_list WHERE problems.id=tag_list.problem_id and tag_list.tag_id={$pb->escapeString($tag)})";
			}

			if (count($filter))
				$query .= " WHERE ".implode(" AND ", $filter);

			// order entries
			if ($order)
				$query .= " ORDER BY ".implode(", ", $order);

			// prepare query
			$this->query = $pb->prepare($query);
		}

		// filter tasks, save the result in the session cache and return the index
		function filter($cache = false) {
			// write results to array
			$res = $this->query->execute();
			$this->array = array();
			while ($problem = $res->fetchArray(SQLITE3_ASSOC))
				$this->array[] = $problem['id'];

			// save in session
			if ($cache)
				$_SESSION['cache'][$this->hash] = array('filter' => $this->par, 'data' => $this->array);
		}
	}

	class TaskList {
		private $pb;		// handle to the problembase
		public $idstr;		// comma-separated string of problem ids
		private $problems;	// corresponding data

		function __construct($pb) {
			$this->pb = $pb;
		}

		// get the data for a specific task page
		function slice($array, $start, $length = TASKS_PER_PAGE) {
			$ids = array_slice($array, $start, $length);
			$this->idstr = implode(",", $ids);
		}

		function query($order = null) {
			$query = "SELECT problems.id, files.content AS problem, problems.proposed, public, letter, number, month, year, "
				."(SELECT COUNT(solutions.id) FROM solutions WHERE problems.id=solutions.problem_id) AS numsol, "
				."(SELECT COUNT(comments.user_id) FROM comments WHERE problems.id=comments.problem_id) AS numcomm, "
				."(SELECT group_concat(tag_id) FROM tag_list WHERE problems.id=tag_list.problem_id) AS tags "
				."FROM problems JOIN files ON problems.file_id=files.rowid "
				."LEFT JOIN published ON problems.id=published.problem_id "
				."WHERE problems.id IN ($this->idstr)";

			// order entries
			if ($order)
				$query .= " ORDER BY ".implode(", ", $order);

			$problems = $this->pb->query($query);
			$this->problems = Array();
			while($problem = $problems->fetchArray(SQLITE3_ASSOC))
				$this->problems[] = $problem;
		}

		// print given tasks as HTML
		function print_html() {
			$taglists = Array(substr_count($this->idstr, ",") + 1);
			foreach ($this->problems as $num=>$problem) {
				print "<a class='textbox' href='{$_SERVER["PBROOT"]}/{$problem['id']}/'>";
				print "<div class='task ".($problem['public'] ? "" : "nonpublic")."'>";
				print "<div class='info'>";
				print "<div class='tags'></div>";
				$taglists[$num] = $problem['tags'];
				printproposers($this->pb, "problem", $problem['id']);
				print "</div>";

				print "<div class='text' id='prob$num'>";
				print htmlspecialchars($problem['problem']);
				print "<table class='info' style='margin-top:1em;'><tr>";
				print "<td style='width:70px; border:none;'>{$problem['proposed']}</td>";

				// find out if published
				if (isset($problem['year']))
					print "<td style='width:200px;'>Heft {$problem['month']}/{$problem['year']}, "
						."Aufgabe \${$problem['letter']}{$problem['number']}$</td>";
				else
					print "<td style='width:200px;'>nicht publiziert</td>";

				$solstr = ($problem['numsol'] <= 1) ? ($problem['numsol'] ? "" : "k")."eine L&ouml;sung" : $problem['numsol']." L&ouml;sungen";
				$commstr = ($problem['numcomm'] <= 1) ? ($problem['numcomm'] ? "" : "k")."ein Kommentar" : $problem['numcomm']." Kommentare";
				print "<td style='width:200px;'>$commstr, $solstr</td>";
				print "</tr></table>";
				print "</div></div></a>";
			}

			print "<script id='tagscript'> (function () {";
			print "var taglists = document.getElementsByClassName('tags');";
			foreach ($taglists as $index=>$taglist)
				tags($this->pb, $taglist, "taglists[$index]");
			print "})();</script>";
		}
	}

	if (isset($_GET['page'])) {
		session_start();
		include $_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/lib/proposers.php';
		$pb = new SQLite3($_SERVER['DOCUMENT_ROOT'].$_SERVER['PBROOT'].'/sqlite/problembase.sqlite');
		header("Content-Type: text/html; encoding=utf-8");

		$filter = new Filter($_GET['hash']);
		$tasklist = new TaskList($pb);
		$tasklist->slice($filter->array, $_GET['page'] * TASKS_PER_PAGE);
		$tasklist->query(array("year DESC", "month DESC"));
		$tasklist->print_html();
	}
?>
