<?php include 'tags.php'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title>Aufgabendatenbank</title>
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
	$pb = new SQLite3('sqlite/problembase.sqlite', '0666');
	$problems = $pb->query("SELECT * FROM problems, proposers WHERE problems.proposer_id=proposers.id");
	?>

	<div class="content">
		<form class="filter" title="Filter" action="">
		<input type="button" value="+ Erweitert" style="float:right;" onclick="Filter.Trig();"/>
		<div class="caption">FILTER</div>
		<input type="text" name="simple_filter" placeholder="Suchbegriff" style="width:330px;"/>
		<div id="hidden_filter" style="visibility:hidden; position:absolute;">
			<div class="info">Erweiterte Suche</div>
			<span class="question">Wer?</span> <input type="text" name="proposer" placeholder="Autor" style="width:200px;"/>
			<span class="question">Was?</span>
			<input type="text" name="tags" placeholder="Tags" style="width:160px;"/>
			<input type="checkbox" name="with_solution"/><span class="info">mit Lösung</span> <br/>
			<span class="question">Wie?</span> {Bewertungsbereich} <br/>
			<span class="question">Wann?</span> <input type="text" name="number" placeholder="No." style="width:40px;"/>
			<input type="checkbox" name="if_start"/> <span class="info">jünger als</span> <input type="date" name="start" style="width:80px;"/>
			<input type="checkbox" name="if_end"/> <span class=info>älter als</span>
			<input type="date" name="end" style="width:80px;"/>
		</div>
		</form>

		<div class="caption" style="margin-top:1.5em;">AUFGABEN
		<a href="problem.php" class="button" style="float:right;">Neue Aufgabe</a></div>
		<?php
		while($problem = $problems->fetchArray(SQLITE3_ASSOC)) {
			print '<a class="problem" href="task.php?id='.$problem['id'].'">';
			print '<div class="task problem_list">';
			print '<div class="info">'.$problem['name'].", ".$problem['location'];
			if ($problem['country'] != "") print " (".$problem['country'].")";
			print '<div class="tags">';
			tags($pb, get_tags($pb, $problem['id']));
			print '</div></div>';

			print '<div class="text" id="prob">';
			print $problem['problem'];
			print '<table class="info" style="margin-top:1em;"><tr>';
			print '<td style="width:70px; border:none;">'.$problem['proposed'].'</td>';

			// find out if published
			$published = $pb->querySingle("SELECT * FROM published WHERE problem_id=".$problem['id'], true);
			if (count($published))
				print '<td style="width:200px;">Heft '.$published['month'].'/'.$published['year'].
					', Aufgabe $'.$published['letter'].$published['number'].'$</td>';
			else
				print '<td style="width:200px;">nicht publiziert</td>';

			$numsol = $pb->querySingle("SELECT COUNT(*) FROM solutions WHERE problem_id=".$problem['id']);
			$solstr = ($numsol <= 1) ? ($numsol ? "" : "k")."eine Lösung" : $numsol." Lösungen";
			$numcomm = $pb->querySingle("SELECT COUNT(*) FROM comments WHERE problem_id=".$problem['id']);
			$commstr = ($numcomm <= 1) ? ($numcomm ? "" : "k")."ein Kommentar" : $numcomm." Kommentare";
			print '<td style="width:200px;">'.$commstr.', '.$solstr.'</td>';
			print '</tr></table>';
			print '</div></div></a>';
		};
		?>

	<form action="index.php" class="taglist">
		<h3 class="caption" style="color:Gray;">[TAGS]</h3>
		<input type="text" name="tag" placeholder="Tag hinzufügen"/>
		<input type="hidden" name="tags" value="Test"/> <br/>
		<div style="margin:3px; margin-bottom:2em;">
			<?php tags($pb, array()); ?>
		</div>
		<a class="button" href="tags.php?edit_tags=1">Bearbeiten</a>
	</form>

	<?php $pb->close(); ?>

	<script type="text/javascript">
		var Filter = new Trigger("hidden_filter");
	</script>
</body>
</html>
