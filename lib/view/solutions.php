<?php
	class Solution {
		private array $data = array();  // Named array containing the data
		private Problem $problem;
		private ProposerList $proposers;
		private static ?SQLStmt $query = null;

		// Prepare query for the constructor
		private static function prepareQuery(SQLDatabase $pb) {
			$query = "SELECT solutions.file_id, solutions.problem_id, files.content AS solution, "
				."solutions.remarks, solutions.month, solutions.year, solutions.public "
				."FROM solutions INNER JOIN files ON solutions.file_id=files.rowid WHERE solutions.file_id=$1";
			self::$query = $pb->prepare($query);
		}

		// Construct from id, eventually test if it's a solution for problem problem_id.
		// If id == -1, create empty solution for problem problem_id.
		function __construct(SQLDatabase $pb, int $id, int $problem_id = -1) {
			if (!self::$query)
				self::prepareQuery($pb);

			// Get data
			if ($id != -1) {
				self::$query->bind(1, $id, SQLTYPE_INTEGER);
				$data = self::$query->exec()->fetchAssoc();
				if (($problem_id != -1) && ($data["problem_id"] != $problem_id))
					return;
			}
			else
				$data = array("file_id" => -1, "problem_id" => $problem_id);

			// Got something valid?
			if ($data)
				$this->data = $data;

			// Get problem
			$this->problem = new Problem($pb,
				($problem_id == -1) ? $data["problem_id"] : $problem_id);

			// Get proposers
			$this->proposers = new ProposerList();
			if (isset($data["file_id"]))
				$this->proposers->from_file($pb, $data["file_id"]);
		}

		// Print solution as HTML
		function print_html(bool $edit, bool $linkback) : void {
			print '<article class="solution'.($this->data['public'] ? "" : " nonpublic").'">';
			if ($edit)
				print "<a class='button inner' href='".WEBROOT."/problem/{$this->data['problem_id']}/solution/{$this->data['file_id']}'><i class='fa fa-pencil'></i> <span>Bearbeiten</span></a>\n";
			if ($linkback)
				print "<a class='button inner' href='".WEBROOT."/problem/{$this->data['problem_id']}'><i class='fa fa-hand-o-right'></i> <span>Zur Aufgabe</span></a>\n";
			print "<div class='info top'>";
			print $this->proposers->to_string($this->data["remarks"], true);
			print "</div>\n";

			print "<div class='text' id='soln'>";
			$replacement = "<div class='picture'><a href='".WEBROOT."/problem/{$this->data['problem_id']}"
				."/solution/{$this->data['file_id']}/picture-$1' class='fa fa-picture-o'></a></div>";
			$solution = preg_replace("/\\\\includegraphics{([0-9]+)}/", $replacement,
				htmlspecialchars($this->data['solution']));
			print $solution;
			print "</div></article>\n\n";
		}

		// Print the solution as TeX code
		function print_tex() : void {
			print "\\losbox";
			$this->problem->print_tex(true);
			print "\n{Lösung von ";
			print $this->proposers->to_string($this->data["remarks"], false);
			print ":}{%\n{$this->data['solution']}}\n\n";
		}

		// Print form
		function print_form(SQLDatabase $pb) : void { ?>
	<form class="solution" id="solution" title="Lösungsformular"
		action="<?=WEBROOT?>/submit/<?=$this->data['problem_id']?>/<?php if (!$this->is_empty()) print $this->data["file_id"]; ?>" method="POST">
		<?php
			$this->problem->print_simplified();
			proposer_form($pb, "solution", $this->proposers);
		?>

		<textarea class="text" name="solution" id="text" rows="60" cols="80" placeholder="Lösungstext"
			style="height:400px;" onkeyup="Preview.Update()"><?php if (!$this->is_empty()) print $this->data['solution']; ?></textarea> <br/>
		<div class="preview" id="preview"></div>
		<textarea class="text" name="remarks" rows="5" cols="65" placeholder="Anmerkungen"
			title="Wenn keine Autoren angegeben sind, wird stattdessen diese Anmerkung gezeigt.
Enthält sie eine '~', so wird die Autorenliste darum ergänzt, diese wird anstatt der Tilde eingefügt."
			style="height:70px;"><?php if (!$this->is_empty()) print $this->data['remarks']; ?></textarea>

		<label for="published">Veröffentlicht in:</label>
		<input type="text" class="text" name="published" id="published" placeholder="MM/JJ"
			pattern="([1-9]|0[1-9]|1[0-2])/[0-9]{2}" style="width:50px;" value="<?php if (isset($this->data['month']))
			print $this->data['month']."/".str_pad($this->data['year']%100, 2, "0", STR_PAD_LEFT); ?>"/>
		<input type="checkbox" name="public" id="public" <?php if (!$this->is_empty() && $this->data['public']) print "checked"; ?>/>
			<label for="public">öffentlich</label>
		<input type="submit" value="<?php if (!$this->is_empty()) print "Speichern"; else print "Erstellen"; ?>" style="float:right;"/>
		<?php if (!$this->is_empty()): ?>
		<input type="checkbox" name="delete"/>
		<input type="button" value="Löschen" style="float:right;"
			onclick="if (confirm('Lösung wirklich löschen?')) postDelete('solution');"/>
		<?php endif; ?>
	</form>

	<input type="hidden" name="picnums" form="solution">
	<!-- here come the figure forms... -->
	</div>

	<script type="text/javascript">
		Preview.Init("text", "preview");
		Preview.Update();

		var picForm = new Pictures("solution", [<?php
		if (!$this->is_empty()) {
			$num = 0;
			$pics = $pb->query("SELECT * FROM pictures WHERE file_id={$this->data["file_id"]}");
			while($pic = $pics->fetchAssoc())
				print (($num++ > 0) ? ", " : "").json_encode($pic);
		}	?>]);
	</script>

	<a class="button" href="javascript:picForm.addPic();"><i class="fa fa-plus-circle"></i> Grafik hinzufügen</a>
<?php	}

		// Do we have valid data? (i.e. any data at all)
		function is_valid() : bool {
			return (bool)$this->data && $this->problem->is_valid();
		}

		function is_empty() : bool {
			return $this->data["file_id"] == -1;
		}

		// Is the current user allowed to see the solution?
		function access(int $right) : bool {
			if ($right == ACCESS_READ)
				return ($this->data["id"] != -1) && ($_SESSION['editor']
					|| ($this->problem->access(ACCESS_READ) && $this->data['public']));
			else             // write or modify
				return $_SESSION['editor'];
		}
	}

	class SolutionList {
		private array $solutions;    // Array containing of Solution objects

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
		function print_html(bool $edit, bool $linkback = false) : void {
			foreach ($this->solutions as $solution)
				$solution->print_html($edit, $linkback);
		}

		// Print published solutions and their respective problems as TeX
		function print_tex(int $probyear, int $period) : void {
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
