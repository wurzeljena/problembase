<?php
	class Filter {
		private $par;       // parameter list
		private $hash;      // ... and its hash
		private $query;     // SQLite query
		public $array;      // result array

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
			$names = array('filter', 'proposer', 'name', 'location', 'number', 'month',
				'year', 'with_solution', 'not_published', 'start', 'end', 'tags');
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

			if (isset($this->par['name'])) {
				$extra = isset($this->par['location']) ? " AND location='{$pb->escape($this->par['location'])}'" : "";
				$filter[] = "EXISTS (SELECT proposer_id FROM fileproposers WHERE file_id=problems.file_id AND proposer_id IN "
					."(SELECT id AS proposer_id FROM proposers WHERE name='{$pb->escape($this->par['name'])}'$extra))";
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

	class Task {
		private $data;      // Named array containing the properties
		private $proposers; // ProposerList for the problem
		private $tags;      // TagList for the problem
		private static $query = null;   // Constructor query

		// Prepare query
		private static function prepareQuery(SQLDatabase $pb) {
			self::$query = $pb->prepare("SELECT problems.*, files.content AS problem, "
				."(SELECT COUNT(solutions.file_id) FROM solutions WHERE problems.file_id=solutions.problem_id) AS numsol, "
				."(SELECT COUNT(comments.user_id) FROM comments WHERE problems.file_id=comments.problem_id) AS numcomm, "
				."letter, number, month, year FROM problems JOIN files ON problems.file_id=files.rowid "
				."LEFT JOIN published ON problems.file_id=published.problem_id WHERE file_id=$1");
		}

		// Construct from file id
		function __construct(SQLDatabase $pb, $id) {
			if (!self::$query)
				self::prepareQuery($pb);
			self::$query->bind(1, $id, SQLTYPE_INTEGER);
			$data = self::$query->exec()->fetchAssoc();
			if ($data) {
				$this->data = $data;
				$this->proposers = new ProposerList;
				$this->proposers->from_file($pb, $this->data["file_id"]);
				$this->tags = new TagList;
				$this->tags->from_file($pb, $this->data["file_id"]);
			}
		}

		// Print problem as HTML, write tag code in string
		function print_html(&$tag_code, $num = -1) {
			print "<div class='task ".($this->data['public'] ? "" : "nonpublic")."'"
				.($num != -1 ? " id='prob$num'" : "").">\n";
			print "<div class='info top'>";
			print "<div class='tags'></div>\n";
			// Create code for tags
			$tag_code .= $this->tags->js($num != -1 ? "taglists[$num]" : "taglist");
			$this->proposers->print_list($this->data["remarks"]);
			if (isset($this->data['proposed']))
				print " <span class='proposed'>{$this->data['proposed']}</span>";

			print "</div>\n<div class='text'>";
			print htmlspecialchars($this->data['problem']);
			print "</div>\n";

			if ($num != -1)
				print "<a class='button inner bottom' href='".WEBROOT."/{$this->data['file_id']}/'>"
					."<i class='icon-hand-right'></i> <span>Lösungen/Kommentare</span></a>\n";
			if ($num == -1 && $_SESSION['editor'])
				print "<a class='button inner bottom' href='".WEBROOT."/{$this->data['file_id']}/edit'>"
					."<i class='icon-pencil'></i> <span>Bearbeiten</span></a>\n";

			print "<div class='info'>";
			// find out if published
			if (isset($this->data['year'])) {
				print "<a href='".WEBROOT."/issues/{$this->data['year']}/{$this->data['month']}'>"
					."Heft {$this->data['month']}/{$this->data['year']}</a>, "
					."Aufgabe \${$this->data['letter']}{$this->data['number']}$";
			}
			else
				print "Nicht publiziert";

			if (isset($this->data['numsol']))
				print " | <i class='icon-book' title='Lösungen'></i> {$this->data['numsol']}";
			if (isset($this->data['numcomm']))
				print " | <i class='icon-comments' title='Kommentare'></i> {$this->data['numcomm']}";
			print "</div></div>\n\n";
		}

		// Print problem as TeX code
		function print_tex() {
			print "\\aufbox{\${$this->data['letter']}\,{$this->data['number']}$}{";
			$this->proposers->print_list($this->data["remarks"]);
			print "}{%\n{$this->data['problem']}}\n\n";
		}

		// Print the tag selector for the problem
		function tag_selector(SQLDatabase $pb) {
			// create empty div for tags
			print "<div class='tag_selector'><i class='icon-tags'></i></div>";

			// initial script to print and mark the right ones
			print "<script> var tagSelector = document.getElementsByClassName('tag_selector')[0];";
			$all_tags = new TagList;
			$all_tags->get($pb, array("*", "(name IN ({$this->tags->print_names()})) AS active",
				"1 AS enabled", "{$this->data["file_id"]} as problem"));
			print $all_tags->js("tagSelector", true);
			print "</script>";
		}

		// print the publishing form
		function publish_form() {
			if (isset($this->data["year"]))
				$volume = $this->data["month"]."/".
					str_pad($this->data["year"]%100, 2, "0", STR_PAD_LEFT);
			else
				$volume = "";
?>			<a class='button danger' href='javascript:Publ.Show();'><i class='icon-globe'></i> <span>&Auml;ndern</span></a>
			<form id="publish" style="display:none;" action="<?=WEBROOT?>/<?=$this->data["file_id"]?>/publish" method="POST">
				Im <label for="volume">Heft</label>
				<input type="text" class="text" id="volume" name="volume" placeholder="MM/JJ"
					pattern="([1-9]|0[1-9]|1[0-2])/[0-9]{2}" style="width:40px;" value="<?=$volume?>">
				<label for="letter">als</label>
				<input type="text" class="text" id="letter" name="letter" placeholder="Buchstabe"
					style="width:50px;" value="<?=$this->data["letter"]?>">
				<input type="text" class="text" name="number" placeholder="Nummer" pattern="[1-9]|[0-5][0-9]|60"
					style="width:20px;" value="<?=$this->data["number"]?>">,
				<input type="checkbox" name="public" id="public" <?=$this->data["public"] ? "checked" : "";?>>
					<label for="public">&ouml;ffentlich</label>
				<input type="submit" value="Speichern">
			</form>

			<script type="text/javascript">
				var Publ = new PopupTrigger("publish");
			</script>
<?php	}

		// Do we have valid data? (i.e. any data at all)
		function is_valid() { return (bool)$this->data; }

		// Is the current user allowed to see the problem?
		function access($right) {
			if ($right == ACCESS_READ)
				return $this->data['public'] || $_SESSION['editor'];
			else    // write or modify
				return $_SESSION['editor'];
		}
	}

	class TaskList {
		private $ids;       // array of problem ids
		private $problems;  // corresponding data

		function __construct(SQLDatabase $pb, array $array, $start=0, $length=0) {
			// get ids
			if ($length)
				$this->ids = array_slice($array, $start, $length);
			else
				$this->ids = $array;

			// get problems
			$this->problems = Array();
			foreach ($this->ids AS $id)
				$this->problems[] = new Task($pb, $id);
		}

		// print given tasks as HTML
		function print_html() {
			// Code for writing tags
			$tag_code = "(function () {var taglists = document.getElementsByClassName('tags');";

			foreach ($this->problems as $num=>$problem)
				$problem->print_html($tag_code, $num);

			$tag_code .= "})();";
			print "<script id='tagscript'>$tag_code</script>";
		}

		// print published tasks as TeX
		function print_tex() {
			print "\\documentclass[exercises]{wurzel2008}\n\\title{\\Wurzel-Aufgaben}\n\n"
				."\\begin{document}\n\\maketitle\n";

			foreach ($this->problems as $problem)
				$problem->print_tex();

			print "\\end{document}\n";
		}
	}
?>
