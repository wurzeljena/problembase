<?php
	session_start();
	include 'tags.php';
	include 'proposers.php';
?>
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
	<?php include 'head.php'; ?>

	<?php
		$pb = new SQLite3('sqlite/problembase.sqlite', '0666');
		$query = "SELECT problems.id, problems.problem, problems.proposed, proposers.name, "
			."proposers.location, proposers.country, letter, number, month, year,"
			."(SELECT COUNT(solutions.id) FROM solutions WHERE problems.id=solutions.problem_id) AS numsol, "
			."(SELECT COUNT(comments.user_id) FROM comments WHERE problems.id=comments.problem_id) AS numcomm, "
			."(SELECT group_concat(tag_id) FROM tag_list WHERE problems.id=tag_list.problem_id) AS tags "
			."FROM problems LEFT JOIN proposers ON problems.proposer_id=proposers.id "
			."LEFT JOIN published ON problems.id=published.problem_id";

		// add filter constraints
		if (isset($_REQUEST['filter'])) {
			$filter = array();

			$words = array_filter(explode(' ', $_REQUEST['filter']));
			foreach ($words as $word)
				$filter[] = "problems.problem LIKE '%$word%'";

			if ($_REQUEST['proposer'] != "")
				$filter[] = "proposers.name LIKE '%".$_REQUEST['proposer']."%'";
			
			if ($_REQUEST['number'] != "") {
				list($month, $year) = explode("/", $_REQUEST['number']);
				if ($year > 50)		// translate YY to 19JJ/20JJ
					$year += 1900;
				if ($year <= 50)
					$year += 2000;
				$filter[] = "month = $month AND year = $year";
			}
			
			if ($_REQUEST['start'] != "")
				$filter[] = "proposed > '".$_REQUEST['start']."'";
			if ($_REQUEST['end'] != "")
				$filter[] = "proposed < '".$_REQUEST['end']."'";

			$tags = array_filter(explode(',', $_REQUEST['tags']));
			foreach ($tags as $tag)
				$filter[] = "$tag IN (SELECT tag_id FROM tag_list WHERE problems.id=tag_list.problem_id)";
			
			if (count($filter))
				$query .= " WHERE ".implode(" AND ", $filter);
		}

		$problems = $pb->query($query);
	?>

	<div class="content">
		<?php
		while($problem = $problems->fetchArray(SQLITE3_ASSOC)) {
			print '<a class="textbox" href="task.php?id='.$problem['id'].'">';
			print '<div class="task problem_list">';
			print '<div class="info">'.$problem['name'].", ".$problem['location'];
			if ($problem['country'] != "") print " (".$problem['country'].")";
			print '<div class="tags">';
			tags($pb, $problem['tags']);
			print '</div></div>';

			print '<div class="text" id="prob">';
			print $problem['problem'];
			print '<table class="info" style="margin-top:1em;"><tr>';
			print '<td style="width:70px; border:none;">'.$problem['proposed'].'</td>';

			// find out if published
			if (isset($problem['year']))
				print '<td style="width:200px;">Heft '.$problem['month'].'/'.$problem['year'].
					', Aufgabe $'.$problem['letter'].$problem['number'].'$</td>';
			else
				print '<td style="width:200px;">nicht publiziert</td>';

			$solstr = ($problem['numsol'] <= 1) ? ($problem['numsol'] ? "" : "k")."eine Lösung" : $problem['numsol']." Lösungen";
			$commstr = ($problem['numcomm'] <= 1) ? ($problem['numcomm'] ? "" : "k")."ein Kommentar" : $problem['numcomm']." Kommentare";
			print '<td style="width:200px;">'.$commstr.', '.$solstr.'</td>';
			print '</tr></table>';
			print '</div></div></a>';
		};
		?>
	</div>

	<form class="filter" id="filter" title="Filter" action="index.php" method="GET">
		<div><input type="text" name="filter" placeholder="Suchbegriff"
			value="<?php if (isset($_REQUEST['filter'])) print $_REQUEST['filter']; ?>"/>
		<input type="submit" value="Filtern"></div>
		<table style="border-top:1px solid Gray; border-bottom:1px solid Gray;">
			<tr>
				<td><span class="question">Wer?</span></td>
				<td><input type="text" name="proposer" placeholder="Autor" list="proposers"
					value="<?php if (isset($_REQUEST['proposer'])) print $_REQUEST['proposer']; ?>"/> </td>
				<?php proposers_datalist($pb); ?>
			</tr>
			<tr>
				<td><span class="question">Wie?</span></td>
				<td> {Bewertungsbereich} </td>
			</tr>
			<tr>
				<td><span class="question">Wann?</span></td>
				<td><input type="text" name="number" placeholder="MM/JJ" style="width:45px;"
					value="<?php if (isset($_REQUEST['number'])) print $_REQUEST['number']; ?>"/>
				<input type="checkbox" name="with_solution"/><span class="info">mit Lösung</span></td>
			</tr>
			<tr>
				<td><span class="info">nach</span></td>
				<td><input type="date" name="start" placeholder="JJJJ-MM-TT"
					value="<?php if (isset($_REQUEST['start'])) print $_REQUEST['start']; ?>"/></td>
			</tr>
			<tr>
				<td><span class="info">vor</span></td>
				<td><input type="date" name="end" placeholder="JJJJ-MM-TT"
					value="<?php if (isset($_REQUEST['end'])) print $_REQUEST['end']; ?>"/></td>
			</tr>
		</table>
		<div class="taglist">
			<span class="question">Was?</span>
			<input type="text" name="tag" placeholder="Tag hinzufügen" list="tag_datalist"/>
			<input type="button" value="+" onclick="addTag('filter');">
			<?php tags_datalist($pb); ?> <br/>
			<div id="tags" style="margin:3px;">
				<input type="hidden" name="tags" value="<?php if (isset($_REQUEST['tags'])) print $_REQUEST['tags']; ?>"/>
				<?php if (isset($_REQUEST['tags'])) tags($pb, $_REQUEST['tags'], 'filter'); ?>
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
