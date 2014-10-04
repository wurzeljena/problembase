<?php
	define("TASKS_PER_PAGE", 10);

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

			$public = !$_SESSION['editor'];
			if ($public)
				$filter[] = "public=1";

			if (isset($this->par['filter']))
				$filter[] = "problems.file_id IN (SELECT file_id AS problem_id FROM problems JOIN files ON files.rowid=problems.file_id "
					."WHERE ".$pb->ftsCond("content", $pb->escape($this->par['filter']))
					." UNION SELECT problem_id FROM solutions JOIN files ON files.rowid=solutions.file_id "
					."WHERE ".$pb->ftsCond("content", $pb->escape($this->par['filter']))." )";

			if (isset($this->par['proposer']))
				$filter[] = "EXISTS (SELECT proposer_id FROM fileproposers WHERE file_id=problems.file_id AND proposer_id IN "
					."(SELECT id AS proposer_id FROM proposers WHERE name LIKE '%{$pb->escape($this->par['proposer'])}%'))";

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
				$tags = new TagList;
				$tags->from_list($pb, str_replace("_", " ", $this->par['tags']));
				$filter[] = $tags->filter_condition();
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
				."(SELECT COUNT(comments.user_id) FROM comments WHERE problems.file_id=comments.problem_id) AS numcomm "
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
			// Code for writing tags
			$tag_code = "(function () {var taglists = document.getElementsByClassName('tags');";

			foreach ($this->problems as $num=>$problem) {
				print "<a class='textbox' href='".WEBROOT."/{$problem['file_id']}/'>";
				print "<div class='task ".($problem['public'] ? "" : "nonpublic")."'>";
				print "<div class='info'>";
				print "<div class='tags'></div>";
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

				// Create code for tags
				$tags = new TagList;
				$tags->from_file($this->pb, $problem["file_id"]);
				$tag_code .= $tags->js("taglists[$num]");
			}

			$tag_code .= "})();";
			print "<script id='tagscript'>$tag_code</script>";
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
		include '../../lib/master.php';
		$pb = load(LOAD_DB | INC_PROPOSERS | INC_TAGS);
		header("Content-Type: text/html; encoding=utf-8");

		$filter = new Filter($_GET['hash']);
		$tasklist = new TaskList($pb);
		$tasklist->slice($filter->array, $_GET['page'] * TASKS_PER_PAGE);
		$tasklist->query(array("proposed DESC", "year DESC", "month DESC"));
		$tasklist->print_html();
	}

	// answer to TeX requests from issue pages
	if (isset($_GET['tex'])) {
		include '../../lib/master.php';
		$pb = load(LOAD_DB | INC_PROPOSERS | INC_TAGS);
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
