<?php
	class Solution {
		private $data;      // Named array containing the data
		private $problem_proposers;  // ProposerList object containing the proposers
		private $proposers; // ProposerList object containing the proposers
		private static $query;       // Query variable

		// Prepare query for the constructor
		private static function prepareQuery(SQLDatabase $pb) {
			$query = "SELECT solutions.file_id, solutions.problem_id, files.content AS solution, "
				."solutions.remarks, solutions.month, solutions.year, solutions.public, "
				."problems.file_id AS problem_id, problems.remarks AS problem_remarks, "
				."problemfiles.content AS problem, published.letter, published.number "
				."FROM solutions INNER JOIN files ON solutions.file_id=files.rowid "
				."INNER JOIN problems ON solutions.problem_id=problems.file_id "
				."INNER JOIN files AS problemfiles ON problems.file_id=problemfiles.rowid "
				."LEFT JOIN published ON published.problem_id = solutions.problem_id WHERE solutions.file_id=$1";
			self::$query = $pb->prepare($query);
		}

		// Construct from id
		function __construct(SQLDatabase $pb, $id) {
			if (!self::$query)
				self::prepareQuery($pb);

			// get data
			self::$query->bind(1, $id, SQLTYPE_INTEGER);
			$this->data = self::$query->exec()->fetchAssoc();

			// get proposers
			$this->proposers = new ProposerList();
			$this->proposers->from_file($pb, $this->data["file_id"]);
			$this->problem_proposers = new ProposerList();
			$this->problem_proposers->from_file($pb, $this->data["problem_id"]);
		}

		// Print solution as HTML
		function print_html($edit, $linkback) {
			print '<div class="solution '.($this->data['public'] ? "" : "nonpublic").'">';
			if ($edit)
				print "<a class='button inner' href='".WEBROOT."/problem/{$this->data['problem_id']}/solution/{$this->data['file_id']}'><i class='icon-pencil'></i> <span>Bearbeiten</span></a>\n";
			if ($linkback)
				print "<a class='button inner' href='".WEBROOT."/problem/{$this->data['problem_id']}'><i class='icon-hand-right'></i> <span>Zur Aufgabe</span></a>\n";
			print "<div class='info top'>";
			$this->proposers->print_list($this->data["remarks"]);
			print "</div>\n";

			print "<div class='text' id='soln'>";
			print htmlspecialchars($this->data['solution']);
			print "</div></div>\n\n";
		}

		// Print the solution as TeX code
		function print_tex() {
			print "\\losbox{\${$this->data['letter']}\,{$this->data['number']}$}{";
			$this->problem_proposers->print_list($this->data["problem_remarks"]);
			print "}{%\n{$this->data['problem']}}{L\xC3\xB6sung von ";
			$this->proposers->print_list($this->data["remarks"]);
			print ":}{%{$this->data['solution']}}\n\n";
		}
	}

	class SolutionList {
		private $solutions;    // Array containing of Solution objects

		// Build list of solutions fulfilling the given $conditions
		function __construct(SQLDatabase $pb, array $conditions) {
			// prepare additional conditions
			$cond = implode(" AND ", $conditions);
			$cond .= $_SESSION["editor"] ? "" : " AND problems.public=1 AND solutions.public=1";
			if ($cond != "")
				$cond = "WHERE ".$cond;

			$sol_ids = $pb->query("SELECT solutions.file_id FROM solutions "
				."INNER JOIN problems ON solutions.problem_id=problems.file_id $cond");
			$this->solutions = Array();
			while($solution = $sol_ids->fetchAssoc())
				$this->solutions[] = new Solution($pb, $solution["file_id"]);
		}

		// Print solutions as HTML
		function print_html($edit, $linkback = false) {
			foreach ($this->solutions as $solution)
				$solution->print_html($edit, $linkback);
		}

		// Print published solutions and their respective problems as TeX
		function print_tex($probyear, $period) {
			$monbegin = ($period == 1) ? 1 : 7;
			$monend = $monbegin + 5;

			print "\\documentclass[solutions]{wurzel2008}\n\\toexercises{"
				."$monbegin/$probyear bis $monend/$probyear}\n\n\\begin{document}\n\\maketitle\n";

			foreach ($this->solutions as $solution)
				$solution->print_tex();

			print "\\end{document}\n";
		}
	}
?>
