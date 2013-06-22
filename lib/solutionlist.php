<?php
	class SolutionList {
		private $pb;		// handle to the problembase
		public $idstr;		// comma-separated string of problem ids
		private $solutions;	// corresponding data

		function __construct($pb) {
			$this->pb = $pb;
		}

		function query($nonpublic) {
			$cond = $nonpublic ? "" : " AND public=1";
			$query = "SELECT solutions.id, problem_id, files.content AS solution, remarks, month, year, public FROM solutions "
				."LEFT JOIN files ON solutions.file_id=files.rowid WHERE solutions.id IN ($this->idstr)".$cond;

			$solutions = $this->pb->query($query);
			$this->solutions = Array();
			while($solution = $solutions->fetchArray(SQLITE3_ASSOC))
				$this->solutions[] = $solution;
		}

		// print given tasks as HTML
		function print_html($edit) {
			foreach ($this->solutions as $solution) {
				print '<div class="solution '.($solution['public'] ? "" : "nonpublic").'">';
				if ($edit)
					print "<a class='button inner' href='{$_SERVER["PBROOT"]}/{$solution['problem_id']}/{$solution['id']}'><i class='icon-pencil'></i> <span>Bearbeiten</span></a>";
				print '<div class="info">';
				printproposers($this->pb, "solution", $solution['id']);
				print '</div>';

				print '<div class="text" id="soln">';
				print htmlspecialchars($solution['solution']);
				print '</div></div>';
			}
		}
	}
?>
