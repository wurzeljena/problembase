<?php
	define("TASKS_PER_PAGE", 10);

	include $_SERVER['DOCUMENT_ROOT'].$_ENV['PBROOT'].'/lib/tags.php';

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
			$names = array('filter', 'proposer', 'number', 'month', 'year',
				'with_solution', 'not_published', 'start', 'end', 'tags');
			$filter = array_intersect_key($par, array_fill_keys($names, 0));
			$this->par = array_filter($filter);
			$this->hash = md5(serialize($this->par));
			return $this->hash;
		}

		// translate filter criterions to SQL
		function construct_query($pb, $order = null) {
			$query = "SELECT problems.file_id, problems.proposed, letter, number, month, year "
				."FROM problems LEFT JOIN published ON problems.file_id=published.problem_id";

			// add filter constraints
			$filter = array();

			$public = !isset($_SESSION['user_id']) || !$_SESSION['editor'];
			if ($public)
				$filter[] = "public=1";

			if (isset($this->par['filter'])) {
				$filter[] = "problems.file_id IN (SELECT file_id AS problem_id FROM problems JOIN files ON files.rowid=problems.file_id "
					."WHERE to_tsvector('german', content) @@ to_tsquery('german', '{$pb->escape($this->par['filter'])}') "
					."UNION SELECT problem_id FROM solutions JOIN files ON files.rowid=solutions.file_id "
					."WHERE to_tsvector('german', content) @@ to_tsquery('german', '{$pb->escape($this->par['filter'])}') )";
			}

			if (isset($this->par['proposer'])) {
				$pb->exec("CREATE TEMPORARY TABLE propfilter AS "
					."SELECT id AS proposer_id FROM proposers WHERE name LIKE '%{$pb->escape($this->par['proposer'])}%'");
				$filter[] = "EXISTS (SELECT proposer_id FROM fileproposers WHERE file_id=problems.file_id AND proposer_id IN propfilter)";
			}

			if (isset($this->par['number'])) {
				list($month, $year) = explode("/", $this->par['number']);
				if ($year > 50)		// translate YY to 19JJ/20JJ
					$year += 1900;
				else
					$year += 2000;
				$filter[] = "month = $month AND year = $year";
			}

			if (isset($this->par['year']))
				$filter[] = "year = {$pb->escape($this->par['year'])}";
			if (isset($this->par['month']))
				$filter[] = "month = {$pb->escape($this->par['month'])}";

			if (isset($this->par['with_solution']))
				$filter[] = "EXISTS (SELECT solutions.file_id FROM solutions WHERE problems.file_id=solutions.problem_id "
					.($public ? " AND public=1" : "").")";

			if (isset($this->par['not_published']))
				$filter[] = "public=0";

			if (isset($this->par['start']))
				$filter[] = "proposed > '{$pb->escape($this->par['start'])}'";
			if (isset($this->par['end']))
				$filter[] = "proposed < '{$pb->escape($this->par['end'])}'";

			if (isset($this->par['tags'])) {
				$tags = array_filter(explode(',', $this->par['tags']));
				foreach ($tags as $tag)
					$filter[] = "EXISTS (SELECT rowid FROM tag_list WHERE problems.file_id=tag_list.problem_id and tag_list.tag_id={$pb->escape($tag)})";
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
			$res = $this->query->exec();
			$this->array = array();
			while ($problem = $res->fetchAssoc())
				$this->array[] = $problem['file_id'];

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

		// get the data for an array
		function set($array) {
			$this->idstr = implode(",", $array);
		}

		// get the data for a specific task page
		function slice($array, $start, $length = TASKS_PER_PAGE) {
			$ids = array_slice($array, $start, $length);
			$this->idstr = implode(",", $ids);
		}

		function query($order = null) {
			if (!$this->idstr) {
				$this->problems = Array();
				return;
			}

			$query = "SELECT problems.file_id, files.content AS problem, problems.proposed, public, letter, number, month, year, "
				."(SELECT COUNT(solutions.file_id) FROM solutions WHERE problems.file_id=solutions.problem_id) AS numsol, "
				."(SELECT COUNT(comments.user_id) FROM comments WHERE problems.file_id=comments.problem_id) AS numcomm, "
				."(SELECT group_concat(tag_id) FROM tag_list WHERE problems.file_id=tag_list.problem_id) AS tags "
				."FROM problems JOIN files ON problems.file_id=files.rowid "
				."LEFT JOIN published ON problems.file_id=published.problem_id "
				."WHERE problems.file_id IN ($this->idstr)";

			// order entries
			if ($order)
				$query .= " ORDER BY ".implode(", ", $order);

			$problems = $this->pb->query($query);
			$this->problems = Array();
			while($problem = $problems->fetchAssoc())
				$this->problems[] = $problem;
		}

		// print given tasks as HTML
		function print_html() {
			$taglists = Array(substr_count($this->idstr, ",") + 1);
			foreach ($this->problems as $num=>$problem) {
				print "<a class='textbox' href='{$_SERVER["PBROOT"]}/{$problem['file_id']}/'>";
				print "<div class='task ".($problem['public'] ? "" : "nonpublic")."'>";
				print "<div class='info'>";
				print "<div class='tags'></div>";
				$taglists[$num] = $problem['tags'];
				printproposers($this->pb, "problem", $problem['file_id']);
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

		// print published tasks as TeX
		function print_tex() {
			print "\\documentclass[exercises]{wurzel2008}\n\\title{\\Wurzel-Aufgaben}\n\n"
				."\\begin{document}\n\\maketitle\n";

			foreach ($this->problems as $num=>$problem) {
				print "\\aufbox{\${$problem['letter']}\,{$problem['number']}$}{";
				printproposers($this->pb, "problem", $problem['file_id']);
				print "}{%\n{$problem['problem']}}\n\n";
			}

			print "\\end{document}\n";
		}
	}

	// answer to page requests from index
	if (isset($_GET['hash'])) {
		session_start();
		include $_SERVER['DOCUMENT_ROOT'].$_ENV['PBROOT'].'/lib/database.php';
		include $_SERVER['DOCUMENT_ROOT'].$_ENV['PBROOT'].'/lib/proposers.php';
		$pb = Problembase();
		header("Content-Type: text/html; encoding=utf-8");

		$filter = new Filter($_GET['hash']);
		$tasklist = new TaskList($pb);
		$tasklist->slice($filter->array, $_GET['page'] * TASKS_PER_PAGE);
		$tasklist->query(array("proposed DESC", "year DESC", "month DESC"));
		$tasklist->print_html();
	}

	// answer to TeX requests from issue pages
	if (isset($_GET['tex'])) {
		session_start();
		include $_SERVER['DOCUMENT_ROOT'].$_ENV['PBROOT'].'/lib/database.php';
		include $_SERVER['DOCUMENT_ROOT'].$_ENV['PBROOT'].'/lib/proposers.php';
		$pb = Problembase();
		header("Content-Type: application/x-tex; encoding=utf-8");
		header("Content-Disposition: attachment; filename=aufg"
			.(str_pad($_GET['year']%100, 2, "0", STR_PAD_LEFT)).str_pad($_GET['month'], 2, "0", STR_PAD_LEFT).".tex");

		$filter = new Filter();
		$hash = $filter->set_params($_GET);
		$filter->construct_query($pb, array("number ASC"));
		$filter->filter(false);
		$tasklist = new TaskList($pb);
		$tasklist->set($filter->array);
		$tasklist->query(array("number ASC"));
		$tasklist->print_tex();
	}
?>
