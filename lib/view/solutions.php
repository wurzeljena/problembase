<?php
	class Solution {
		private $data = array();  // Named array containing the data
		private $problem;         // Task object
		private $proposers;       // ProposerList object containing the proposers
		private static $query;    // Query variable

		// Prepare query for the constructor
		private static function prepareQuery(SQLDatabase $pb) {
			$query = "SELECT solutions.file_id, solutions.problem_id, files.content AS solution, "
				."solutions.remarks, solutions.month, solutions.year, solutions.public "
				."FROM solutions INNER JOIN files ON solutions.file_id=files.rowid WHERE solutions.file_id=$1";
			self::$query = $pb->prepare($query);
		}

		// Construct from id, eventually test if it's a solution for problem problem_id.
		// If id == -1, create empty solution for problem problem_id.
		function __construct(SQLDatabase $pb, $id, $problem_id = -1) {
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
				$data = array("file_id" => -1);

			// Got something valid?
			if ($data)
				$this->data = $data;

			// Get problem
			$this->problem = new Task($pb,
				($problem_id == -1) ? $data["problem_id"] : $problem_id);

			// Get proposers
			$this->proposers = new ProposerList();
			if (isset($data["file_id"]))
				$this->proposers->from_file($pb, $data["file_id"]);
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
			print "\\losbox";
			$this->problem->print_tex(true);
			print "{Lösung von ";
			$this->proposers->print_list($this->data["remarks"]);
			print ":}{%\n{$this->data['solution']}}\n\n";
		}

		// Print form
		function print_form(SQLDatabase $pb) { ?>
	<form class="solution" id="solution" title="L&ouml;sungsformular"
		action="<?=WEBROOT?>/submit/<?=$this->data['problem_id']?>/<?php if (!$this->is_empty()) print $this->data["file_id"]; ?>" method="POST">
		<?php
			$this->problem->print_simplified();
			proposer_form($pb, "solution", $this->proposers);
		?>

		<textarea class="text" name="solution" id="text" rows="60" cols="80" placeholder="L&ouml;sungstext"
			style="height:400px;" onkeyup="Preview.Update()"><?php if (!$this->is_empty()) print $this->data['solution']; ?></textarea> <br/>
		<div class="preview" id="preview"></div>
		<textarea class="text" name="remarks" rows="5" cols="65" placeholder="Anmerkungen"
			title="Wenn keine Autoren angegeben sind, wird stattdessen diese Anmerkung gezeigt.
Enth&auml;lt sie eine '~', so wird die Autorenliste darum erg&auml;nzt, diese wird anstatt der Tilde eingef&uuml;gt."
			style="height:70px;"><?php if (!$this->is_empty()) print $this->data['remarks']; ?></textarea>

		<label for="published">Ver&ouml;ffentlicht in:</label>
		<input type="text" class="text" name="published" id="published" placeholder="MM/JJ"
			pattern="([1-9]|0[1-9]|1[0-2])/[0-9]{2}" style="width:50px;" value="<?php if (isset($this->data['month']))
			print $this->data['month']."/".str_pad($this->data['year']%100, 2, "0", STR_PAD_LEFT); ?>"/>
		<input type="checkbox" name="public" id="public" <?php if (!$this->is_empty() && $this->data['public']) print "checked"; ?>/>
			<label for="public">&ouml;ffentlich</label>
		<input type="submit" value="<?php if (!$this->is_empty()) print "Speichern"; else print "Erstellen"; ?>" style="float:right;"/>
		<?php if (!$this->is_empty()): ?>
		<input type="checkbox" name="delete"/>
		<input type="button" value="L&ouml;schen" style="float:right;"
			onclick="if (confirm('L&ouml;sung wirklich l&ouml;schen?')) postDelete('solution');"/>
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

	<a class="button" href="javascript:picForm.addPic();"><i class="icon-plus-sign"></i> Grafik hinzuf&uuml;gen</a>
<?php	}

		// Do we have valid data? (i.e. any data at all)
		function is_valid() {
			return (bool)$this->data && $this->problem->is_valid();
		}

		function is_empty() {
			return $this->data["file_id"] == -1;
		}

		// Is the current user allowed to see the solution?
		function access($right) {
			if ($right == ACCESS_READ)
				return ($this->data["id"] != -1) && ($_SESSION['editor']
					|| ($this->problem->access(ACCESS_READ) && $this->data['public']));
			else             // write or modify
				return $_SESSION['editor'];
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
