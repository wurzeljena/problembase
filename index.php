<?php include 'tags.php'; include 'proposers.php'; ?>
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
	$problems = $pb->query("SELECT problems.id, problems.problem, problems.proposed, proposers.name, proposers.location, proposers.country"
		." FROM problems, proposers WHERE problems.proposer_id=proposers.id");
	?>

	<div class="content">
		<?php
		while($problem = $problems->fetchArray(SQLITE3_ASSOC)) {
			print '<a class="textbox" href="task.php?id='.$problem['id'].'">';
			print '<div class="task problem_list">';
			print '<div class="info">'.$problem['name'].", ".$problem['location'];
			if ($problem['country'] != "") print " (".$problem['country'].")";
			print '<div class="tags">';
			tags($pb, implode(",", get_tags($pb, $problem['id'])));
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
	</div>

	<form class="filter" id="filter" title="Filter" action="">
		<div><input type="text" name="filter" placeholder="Suchbegriff"/>
		<input type="submit" value="Filtern"></div>
		<table style="border-top:1px solid Gray; border-bottom:1px solid Gray;">
			<tr>
				<td><span class="question">Wer?</span></td>
				<td><input type="text" name="proposer" placeholder="Autor" list="proposers"/> </td>
				<?php proposers_datalist($pb); ?>
			</tr>
			<tr>
				<td><span class="question">Wie?</span></td>
				<td> {Bewertungsbereich} </td>
			</tr>
			<tr>
				<td><span class="question">Wann?</span></td>
				<td><input type="text" name="number" placeholder="MM/JJ" style="width:45px;"/><input type="checkbox" name="with_solution"/><span class="info">mit Lösung</span></td>
			</tr>
			<tr>
				<td><span class="info">älter als</span></td>
				<td><input type="date" name="start" placeholder="JJJJ-MM-TT"/></td>
			</tr>
			<tr>
				<td><span class="info">jünger als</span></td>
				<td><input type="date" name="end" placeholder="JJJJ-MM-TT"/></td>
			</tr>
		</table>
		<div class="taglist">
			<span class="question">Was?</span>
			<input type="text" name="tag" placeholder="Tag hinzufügen" list="tag_datalist"/>
			<input type="button" value="+" onclick="addTag('filter');">
			<?php tags_datalist($pb); ?> <br/>
			<div id="tags" style="margin:3px;">
				<input type="hidden" name="tags" value="<?php if (isset($_REQUEST['tags'])) print $_REQUEST['tags']; ?>"/>
				<?php if (isset($_REQUEST['tags'])) tags($pb, $_REQUEST['tags']); ?>
			</div>
		</div>
		<!--<a class="button" style="margin-top:2em;" href="tags.php?edit_tags=1">Tags bearbeiten</a>-->
	</form>

	<div class="panel">
		<a href="problem.php" class="button">Neue Aufgabe</a>
	</div>

	<?php $pb->close(); ?>
</body>
</html>
