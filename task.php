<?php
	session_start();
	include 'tags.php';
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title>Aufgabendatenbank - Aufgabe</title>
	<meta name="author" content="Wurzel e.V."/>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
	<link rel="stylesheet" type="text/css" href="pb.css"/>
	<link rel="icon" href="dw.ico"/>
	<script type="text/javascript" src="MathJax/MathJax.js?config=TeX-AMS_HTML"></script>
	<script type="text/javascript" src="fancy.js"></script>
</head>
<body>
	<?php include 'head.php'; ?>

	<?php
	$id = (int)$_REQUEST['id'];
	$pb = new SQLite3('sqlite/problembase.sqlite', '0666');
	$problem = $pb->querySingle("SELECT * FROM problems WHERE id=$id", true);
	$proposer = $pb->querySingle("SELECT * FROM proposers WHERE id=".$problem['proposer_id'], true);
	?>
	<div class="content">
		<div class="task">
		<?php
			if (isset($user_id)) {
				print '<a class="button outer" style="top:0em;" href="problem.php?id='.$id.'">Bearbeiten</a>';
				print '<a class="button danger outer" style="top:2em;" href="submit_problem.php?id='.$id.'&delete=1">Löschen</a>';
			}
		?>
			<div class="info">
			<?php
				print $proposer['name'].", ".$proposer['location'];
				if ($proposer['country'] != "") print " (".$proposer['country'].")";
			?>
			<div class="tags">
				<?php tags($pb, get_tags($pb, $id)); ?>
			</div></div>
			<div class="text" id="prob"><?php print $problem['problem']?></div>
			<div class="published">
			<?php
				$pub = $pb->querySingle("SELECT * FROM published WHERE problem_id=".$id, true);
				if (count($pub)) {
					$volume = $pub['month']."/".($pub['year']%100);
					$letter = $pub['letter'];
					$number = $pub['number'];
					print "Veröffentlicht als $$letter$number$ im Heft $volume.";
					if (isset($user_id) && $_SESSION['editor'])
						print "<a class='button danger' style='float:right' href='javascript:Publ.Trig();'>Ändern</a>";
				}
				else {
					$date = getdate();	++$date['mon'];
					if ($date['mon'] > 12) {
						$date['mon'] -= 12;
						++$date['year'];
					}
					$volume = $date['mon']."/".($date['year']%100);
					$number = $letter = "";
					print "Noch nicht veröffentlicht.";
					if (isset($user_id) && $_SESSION['editor'])
						print "<a class='button' style='float:right' href='javascript:Publ.Trig();'>Veröffentlichen</a>";
				}
			?>
			<form id="publish" style="visibility:hidden; position:absolute;" action="publish.php" method="POST">
				<input type="hidden" name="id" value="<?php print $id; ?>">
				Ausgabe: <input type="text" name="volume" placeholder="MM/JJ"
					style="width:40px;" value="<?php print $volume; ?>">
				Nummer: <input type="text" name="letter" placeholder="xxx"
					style="width:50px;" value="<?php print $letter; ?>">
					<input type="text" name="number" placeholder="NN"
					style="width:20px;" value="<?php print $number; ?>">
				<input type="submit" value="Speichern">
			</form>
			</div>
		</div>

		<div class="caption" id="comments" style="margin-top:1.5em;">Kommentare
		<?php
		if(isset($user_id) && !$pb->querySingle("SELECT * FROM comments WHERE user_id=$user_id AND problem_id=$id", false))
			print '<a class="button" style="float:right;" href="eval.php?id='.$id.'">Schreiben</a>';
		?>
		</div>
		<table class="comments">
		<?php
			$comments = $pb->query("SELECT * FROM comments, users WHERE comments.user_id=users.id AND problem_id=$id");
			while($comment=$comments->fetchArray(SQLITE3_ASSOC)) {
				if (isset($user_id) && $comment['user_id']==$user_id)
					print '<tr class="own">';
				else
					print '<tr>';
				print '<td class="author"><a href="user.php#'.$comment['user_id'].'">'.$comment['name'].'</a></td>';
				print '<td class="comment">';
				if (isset($user_id) && $comment['user_id']==$user_id) {
					print '<a class="button danger" style="float:right;" href="submit_eval.php?id='.$id.'&delete=1">Löschen</a>';
					print '<a class="button" style="float:right;" href="eval.php?id='.$id.'">Bearbeiten</a>';
				}
				print '{Bewertungsbereich} <br/>';
				print $comment['comment'];
				print '</td></tr>';
			};
		?>
		</table>

		<div class="caption" id="solutions" style="margin-top:1.5em;">Lösungen
		<?php
			if (isset($user_id))
				print '<a class="button" style="float:right;" href="solution.php?problem_id='.$id.'">Hinzufügen</a>';
		?>
		</div>
		<?php
		$solutions = $pb->query("SELECT solutions.id, solutions.solution, solutions.remarks, solutions.month, solutions.year, "
			."proposers.name, proposers.location, proposers.country FROM solutions, proposers WHERE "
			."solutions.proposer_id=proposers.id AND problem_id=$id");
		while($solution = $solutions->fetchArray(SQLITE3_ASSOC)) {
			print '<div class="solution">';
			if (isset($user_id)) {
				print '<a class="button outer" style="top:0em;" href="solution.php?id='.$solution['id'].'">Bearbeiten</a>';
				print '<a class="button danger outer" style="top:2em;" href="submit_solution.php?id='.$solution['id'].'&delete=1">Löschen</a>';
			}
			print '<div class="info">'.$solution['name'].", ".$solution['location'];
			if ($solution['country'] != "") print " (".$solution['country'].")";
			print '</div>';

			print '<div class="text" id="soln">';
			print $solution['solution'];
			print '</div></div>';
		};
		?>

		<?php $pb->close(); ?>
	</div>

	<script type="text/javascript">
		text = document.getElementById("prob");
		MathJax.Hub.Queue(["Typeset", MathJax.Hub, prob]);
		var Publ = new Trigger("publish");
	</script>
</body>
</html>
