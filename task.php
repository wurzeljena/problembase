<?php include 'tags.php'; ?>
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
	<div class="head"><div style="width:50em;">
		<div class="logo">&#x221A;<span style="text-decoration:overline">WURZEL</span></div>
		<div class="login">Login</div>
		<div style="font-family:sans-serif; font-size:x-small;">Aufgabendatenbank <br /> &copy; 2012 <a href="http://www.wurzel.org/" target="_blank">Wurzel e.V.</a></div>
	</div></div>

	<?php
	$id = (int)$_REQUEST['id'];
	$pb = new SQLite3('sqlite/problembase.sqlite', '0666');
	$problem = $pb->querySingle("SELECT * FROM problems WHERE id=$id", true);
	$proposer = $pb->querySingle("SELECT * FROM proposers WHERE id=".$problem['proposer_id'], true);
	?>
	<div class="content">
		<div class="task">
		<?php
			print '<a class="button outer" style="top:0em;" href="problem.php?id='.$id.'">Bearbeiten</a>';
			print '<a class="button danger outer" style="top:2em;" href="submit_problem.php?id='.$id.'&delete=1">L�schen</a>';
		?>
			<div class="info">
			<?php
				print $proposer['name'].", ".$proposer['location'];
				if ($proposer['country'] != "") print " (".$proposer['country'].")";
			?>
			<div class="tags">
				<?php tags($pb, implode(",", get_tags($pb, $id))); ?>
			</div></div>
			<div class="text" id="prob"><?php print $problem['problem']?></div>
		</div>

		<div class="caption" id="comments" style="margin-top:1.5em;">Kommentare
		<?php
		if(!$pb->querySingle("SELECT * FROM comments WHERE user_id=1 AND problem_id=$id", false))
			print '<a class="button" style="float:right;" href="eval.php?id='.$id.'">Schreiben</a>';
		?>
		</div>
		<table class="comments">
		<?php
			$comments = $pb->query("SELECT * FROM comments, users WHERE comments.user_id=users.id AND problem_id=$id");
			while($comment=$comments->fetchArray(SQLITE3_ASSOC)) {
				if ($comment['user_id']==1)
					print '<tr class="own">';
				else
					print '<tr>';
				print '<td class="author">'.$comment['name'].'</td>';
				print '<td class="comment">';
				if ($comment['user_id']==1) {
					print '<a class="button danger" style="float:right;" href="submit_eval.php?id='.$id.'&delete=1">L�schen</a>';
					print '<a class="button" style="float:right;" href="eval.php?id='.$id.'">Bearbeiten</a>';
				}
				print '{Bewertungsbereich} <br/>';
				print $comment['comment'];
				print '</td></tr>';
			};
		?>
		</table>

		<div class="caption" id="solutions" style="margin-top:1.5em;">L�sungen
		<?php print '<a class="button" style="float:right;" href="solution.php?problem_id='.$id.'">Hinzuf�gen</a>'; ?>
		</div>
		<?php
		$solutions = $pb->query("SELECT solutions.id, solutions.solution, solutions.remarks, solutions.month, solutions.year, "
			."proposers.name, proposers.location, proposers.country FROM solutions, proposers WHERE "
			."solutions.proposer_id=proposers.id AND problem_id=$id");
		while($solution = $solutions->fetchArray(SQLITE3_ASSOC)) {
			print '<div class="solution">';
			print '<a class="button outer" style="top:0em;" href="solution.php?id='.$solution['id'].'">Bearbeiten</a>';
			print '<a class="button danger outer" style="top:2em;" href="submit_solution.php?id='.$solution['id'].'&delete=1">L�schen</a>';
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
	</script>
</body>
</html>
