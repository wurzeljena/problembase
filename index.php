<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" >
<head>
    <title>Aufgabendatenbank</title>
	<meta name="author" content="Wurzel e.V."/>
	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
	<link rel="stylesheet" type="text/css" href="pb.css"/>
	<link rel="icon" href="dw.ico"/>
	<script type="text/javascript" src="MathJax/MathJax.js?config=TeX-AMS_HTML"></script>
	<script type="text/javascript" src="preview.js"></script>
</head>
<body>
	<div class="head"><div style="width:50em;">
		<div class="logo">&#x221A;<span style="text-decoration:overline">WURZEL</span></div>
		<div class="login">Login</div>
		<div style="font-family:sans-serif; font-size:x-small;">Aufgabendatenbank <br /> &copy; 2012 <a href="http://www.wurzel.org/" target="_blank">Wurzel e.V.</a></div>
	</div></div>

	<?php
		include 'tags.php';
		$pb = new SQLite3('sqlite/problembase.sqlite', '0666');
		$problems = $pb->query("SELECT * FROM problems, proposers WHERE problems.proposer_id=proposers.id");
	?>

	<div class="content">
		<form class="filter" title="Filter" action="">
		<input type="button" value="+ Erweitert" style="float:right;" onclick="Trigger.Trig();"/>
		<div class="caption">Filter</div>
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

		<div class="main">
		<a href="problem.htm" class="button" style="float:right;">Neue Aufgabe</a>
		<div class="caption">Aufgaben</div>
		<div class="problem_list">
			<?php
			while($problem = $problems->fetchArray(SQLITE3_ASSOC)) {
				// find out if published
				print '<div class="problem">';
				print '<span class="info proposer">'.htmlspecialchars($problem['name']).", ".htmlspecialchars($problem['location']);
				if ($problem['country'] != "") print " (".htmlspecialchars($problem['country']).")";
				print '</span>';

				$tag_list = $pb->query("SELECT tag_id FROM tag_list WHERE problem_id=".$problem['id']);
				$tags = array();
				while ($tag = $tag_list->fetchArray(SQLITE3_NUM)) $tags[] = $tag[0];
				tags($pb, $tags);
				print $problem['problem'];
				print '<table class="info"><tr>';
				print '<td style="width:15%; border:none;">'.$problem['proposed'].'</td>';

				$published = $pb->querySingle("SELECT * FROM published WHERE problem_id=".$problem['id'], true);
				if (count($published))
					print '<td style="width:40%;">Heft '.$published['month'].'/'.$published['year'].
						', Aufgabe $'.$published['letter'].$published['number'].'$</td>';
				else
					print '<td style="width:40%;">nicht publiziert</td>';

				$numsol = $pb->querySingle("SELECT COUNT(*) FROM solutions WHERE problem_id=".$problem['id']);
				$solstr = ($numsol <= 1) ? ($numsol ? "" : "k")."eine Lösung" : $numsol." Lösungen";
				$numcomm = $pb->querySingle("SELECT COUNT(*) FROM comments WHERE problem_id=".$problem['id']);
				$commstr = ($numcomm <= 1) ? ($numcomm ? "" : "k")."ein Kommentar" : $numcomm." Kommentare";
				print '<td style="width:40%;">'.$commstr.', '.$solstr.'</td>';
				print '</tr></table>';
				print '</div>';
			};
			?>
		</div>
		</div>
	</div>

	<form action="index.htm" class="taglist">
		<h3 class="caption" style="color:Gray;">[Tags]</h3>
		<input type="text" name="tag" placeholder="Tag hinzufügen"/>
		<input type="hidden" name="tags" value="Test"/> <br/>
		<div style="margin:3px; margin-bottom:2em;">
			<?php tags($pb, array()); ?>
		</div>
		<a class="button" href="tags.htm">Bearbeiten</a>
	</form>

	<?php $pb->close(); ?>

	<script type="text/javascript">
		Trigger.Init("hidden_filter");
	</script>
</body>
</html>
