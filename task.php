<?php
	session_start();

	$id = (int)$_REQUEST['id'];
	$pb = new SQLite3('sqlite/problembase.sqlite');
	$problem = $pb->querySingle("SELECT problems.*, files.content AS problem FROM problems JOIN files ON problems.file_id=files.rowid WHERE id=$id", true);

	// if no such problem exists, throw a 404 error
	if (empty($problem)) {
		$error = "Aufgabe nicht gefunden";
		include 'error404.php';
		exit();
	}

	include 'head.php';
	include 'tags.php';
	include 'proposers.php';

	printhead("Aufgabe $id");
?>
<body>
	<?php printheader(); ?>
	<div class="content">
		<div class="task">
		<?php
			if (isset($user_id))
				print "<a class='button outer' style='top:0em;' href='{$_SERVER["PBROOT"]}/$id/edit'>Bearbeiten</a>";
		?>
			<div class="info">
			<div class="tags"></div>
			<?php tags($pb, get_tags($pb, $id)); ?>
			<?php printproposers($pb, "problem", $id); ?>
			</div>
			<div class="text" id="prob"><?php print htmlspecialchars($problem['problem']); ?></div>
			<div class="published">
			<?php
				$pub = $pb->querySingle("SELECT * FROM published WHERE problem_id=".$id, true);
				if (count($pub)) {
					$volume = $pub['month']."/".($pub['year']%100);
					$letter = $pub['letter'];
					$number = $pub['number'];
					print "Ver&ouml;ffentlicht als $$letter$number$ im Heft $volume.";
					if (isset($user_id) && $_SESSION['editor'])
						print "<a class='button danger' style='float:right' href='javascript:Publ.Trig();'>&Auml;ndern</a>";
				}
				else {
					$date = getdate();	++$date['mon'];
					if ($date['mon'] > 12) {
						$date['mon'] -= 12;
						++$date['year'];
					}
					$volume = $date['mon']."/".($date['year']%100);
					$number = $letter = "";
					print "Noch nicht ver&ouml;ffentlicht.";
					if (isset($user_id) && $_SESSION['editor'])
						print "<a class='button' style='float:right' href='javascript:Publ.Trig();'>Ver&ouml;ffentlichen</a>";
				}
			?>
			<form id="publish" style="visibility:hidden; position:absolute;" action="<?=$_SERVER["PBROOT"]?>/publish.php" method="POST">
				<input type="hidden" name="id" value="<?php print $id; ?>">
				Ausgabe: <input type="text" name="volume" placeholder="MM/JJ" pattern="([1-9]|0[1-9]|1[0-2])/[0-9]{2}"
					style="width:40px;" value="<?php print $volume; ?>">
				Nummer: <input type="text" name="letter" placeholder="xxx"
					style="width:50px;" value="<?php print $letter; ?>">
					<input type="text" name="number" placeholder="NN" pattern="[1-9]|[0-5][0-9]|60"
					style="width:20px;" value="<?php print $number; ?>">
				<input type="submit" value="Speichern">
			</form>
			</div>
		</div>

		<div class="caption" id="comments" style="margin-top:1.5em;">Kommentare
		<?php
		if(isset($user_id) && !$pb->querySingle("SELECT * FROM comments WHERE user_id=$user_id AND problem_id=$id", false))
			print "<a class='button' style='float:right;' href='{$_SERVER["PBROOT"]}/$id/evaluate'>Schreiben</a>";
		?>
		</div>
		<table class="comments">
		<?php
			$comments = $pb->query("SELECT * FROM comments JOIN users ON comments.user_id=users.id WHERE problem_id=$id");
			while($comment=$comments->fetchArray(SQLITE3_ASSOC)) {
				if (isset($user_id) && $comment['user_id']==$user_id)
					print '<tr class="own">';
				else
					print '<tr>';
				print "<td class='author'><a href='{$_SERVER["PBROOT"]}/users/{$comment['user_id']}'>{$comment['name']}</a></td>";
				print '<td class="comment">';
				if (isset($user_id) && $comment['user_id']==$user_id)
					print "<a class='button' style='float:right;' href='{$_SERVER["PBROOT"]}/$id/evaluate'>Bearbeiten</a>";
				print $comment['comment'].'</td></tr>';

				if (isset($user_id) && $comment['user_id']==$user_id)
					print '<tr class="eval own"><td colspan=2>';
				else
					print '<tr class="eval"><td colspan=2>';
				$critnames = array('Eleganz', 'Schwierigkeit', 'Wissen');
				$critcols = array('beauty', 'difficulty', 'knowledge_required');
				for ($crit=0; $crit<3; ++$crit) {
					print '<span class="eval">'.$critnames[$crit].'</span> ';
					for ($star=1; $star<=5; ++$star)
						print ($star<=$comment[$critcols[$crit]]) ?
							"<img class='star' src='{$_SERVER["PBROOT"]}/img/mandstar.png' alt='*'> " :
							"<img class='star' src='{$_SERVER["PBROOT"]}/img/mand.png' alt='o'> ";
				}
				print '</td></tr>';
			};
		?>
		</table>

		<div class="caption" id="solutions" style="margin-top:1.5em;">L&ouml;sungen
		<?php
			if (isset($user_id))
				print "<a class='button' style='float:right;' href='{$_SERVER["PBROOT"]}/$id/addsolution'>Hinzuf&uuml;gen</a>";
		?>
		</div>
		<?php
		$solutions = $pb->query("SELECT solutions.id, files.content AS solution, solutions.remarks, solutions.month, solutions.year FROM solutions "
			."LEFT JOIN files ON solutions.file_id=files.rowid "
			."WHERE problem_id=$id");
		while($solution = $solutions->fetchArray(SQLITE3_ASSOC)) {
			print '<div class="solution">';
			if (isset($user_id))
				print "<a class='button outer' style='top:0em;' href='{$_SERVER["PBROOT"]}/$id/{$solution['id']}'>Bearbeiten</a>";
			print '<div class="info">';
			printproposers($pb, "solution", $solution['id']);
			print '</div>';

			print '<div class="text" id="soln">';
			print htmlspecialchars($solution['solution']);
			print '</div></div>';
		};
		?>

		<?php $pb->close(); ?>
	</div>

	<script type="text/javascript">
		var Publ = new Trigger("publish");
	</script>
</body>
</html>
