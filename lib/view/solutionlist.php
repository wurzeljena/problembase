<?php
	class SolutionList {
		private $pb;		// handle to the problembase
		public $idstr;		// comma-separated string of problem ids
		private $solutions;	// corresponding data

		function __construct($pb) {
			$this->pb = $pb;
		}

		function query($nonpublic) {
			if (!$this->idstr) {
				$this->solutions = array();
				return;
			}

			$cond = $nonpublic ? "" : " AND problems.public=1 AND solutions.public=1";
			$query = "SELECT solutions.file_id, solutions.problem_id, files.content AS solution, solutions.remarks, solutions.month, solutions.year, "
				."solutions.public, problemfiles.content AS problem, published.letter, published.number "
				."FROM solutions INNER JOIN files ON solutions.file_id=files.rowid "
				."INNER JOIN problems ON solutions.problem_id=problems.file_id INNER JOIN files AS problemfiles ON problems.file_id=problemfiles.rowid "
				."LEFT JOIN published ON published.problem_id = solutions.problem_id WHERE solutions.file_id IN ($this->idstr)".$cond;

			$solutions = $this->pb->query($query);
			$this->solutions = Array();
			while($solution = $solutions->fetchAssoc())
				$this->solutions[] = $solution;
		}

		// print given tasks as HTML
		function print_html($edit, $linkback = false) {
			foreach ($this->solutions as $solution) {
				print '<div class="solution '.($solution['public'] ? "" : "nonpublic").'">';
				if ($edit)
					print "<a class='button inner' href='{$_SERVER["PBROOT"]}/{$solution['problem_id']}/{$solution['file_id']}'><i class='icon-pencil'></i> <span>Bearbeiten</span></a>";
				if ($linkback)
					print "<a class='button inner' href='{$_SERVER["PBROOT"]}/{$solution['problem_id']}/'><i class='icon-hand-right '></i> <span>Zur Aufgabe</span></a>";
				print '<div class="info">';
				printproposers($this->pb, "solution", $solution['file_id']);
				print '</div>';

				print '<div class="text" id="soln">';
				print htmlspecialchars($solution['solution']);
				print '</div></div>';
			}
		}

		// print published solutions and their respective problems as TeX
		function print_tex($probyear, $period) {
			if ($period = 1) {
				$monbegin = 1;
				$monend = 6;
			}
			else {
				$monbegin = 7;
				$monend = 12;
			}

			print "\\documentclass[solutions]{wurzel2008}\n\\toexercises{"
				."$monbegin/$probyear bis $monend/$probyear}\n\n\\begin{document}\n\\maketitle\n";

			foreach ($this->solutions as $num=>$solution) {
				print "\\losbox{\${$solution['letter']}\,{$solution['number']}$}{";
				printproposers($this->pb, "problem", $solution['problem_id']);
				print "}{%\n{$solution['problem']}}{L\xC3\xB6sung von ";
				printproposers($this->pb, "solution", $solution['file_id']);
				print ":}{%\n{$solution['solution']}}\n\n";
			}

			print "\\end{document}\n";
		}
	}

	// answer to TeX requests from issue pages
	if (isset($_GET['tex'])) {
		include $_SERVER['DOCUMENT_ROOT'].$_ENV['PBROOT'].'/lib/master.php';
		$pb = load(LOAD_DB | INC_PROPOSERS);
		header("Content-Type: application/x-tex; encoding=utf-8");
		header("Content-Disposition: attachment; filename=loes"
			.(str_pad($_GET['year']%100, 2, "0", STR_PAD_LEFT)).str_pad($_GET['month'], 2, "0", STR_PAD_LEFT).".tex");

		if ($_GET['month']<7)	{ $year = $_GET['year'] - 1;	$period = 1;	}
		else					{ $year = $_GET['year'];		$period = 2;	}

		$sollist = new SolutionList($pb);
		$sollist->idstr = $pb->querysingle("SELECT group_concat(file_id) FROM solutions WHERE year={$_GET['year']} AND month={$_GET['month']}", false);
		$sollist->query($_SESSION['editor']);
		$sollist->print_tex($year, $period);
	}
?>
