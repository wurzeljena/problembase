<?php
	session_start();
	include 'proposers.php';
	include 'tasklist.php';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Aufgabendatenbank</title>
	<meta name="author" content="Wurzel e.V."/>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<link rel="stylesheet" type="text/css" href="pb.css"/>
	<link rel="icon" href="dw.ico"/>
	<script type="text/javascript" src="MathJax/MathJax.js?config=TeX-AMS_HTML"></script>
	<script type="text/javascript" src="fancy.js"></script>
	<script type="text/javascript" src="ajax.js"></script>
</head>
<body>
	<?php include 'head.php'; ?>

	<div class="content" id="tasklist">
		<?php
			$pb = new SQLite3('sqlite/problembase.sqlite', '0666');
			tasklist($pb, 0);
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
				<input type="checkbox" name="with_solution"/><span class="info">mit L&ouml;sung</span></td>
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
			<?php tag_select($pb, "filter"); ?>
			<input type="hidden" name="tags" value="<?php if (isset($_REQUEST['tags'])) print $_REQUEST['tags']; ?>"/>
			<div id="tags" style="margin:3px;">
				<?php if (isset($_REQUEST['tags'])) tags($pb, $_REQUEST['tags'], 'filter'); ?>
			</div>
		</div>
	</form>

	<div id="panel">
		<?php if (isset($user_id)) {
				print '<a href="problem.php" class="button">Neue Aufgabe</a>';
				print '<a href="user.php" class="button">Benutzerliste</a>';
			}
		?>
	</div>
	
	<div id="pager">
		<a href="javascript:incrPage(-5);" class="button">&laquo;</a>
		<a href="javascript:incrPage(-1);" class="button">&lt;</a>
		Seite <span id="page">1</span>, Aufgaben <span id="pagetasks">1&mdash;10</span>
		<input type="hidden" id="request" value="<?php print $_SERVER['QUERY_STRING']; ?>">
		<a href="javascript:incrPage(1);" class="button">&gt;</a>
		<a href="javascript:incrPage(5);" class="button">&raquo;</a>
	</div>

	<?php $pb->close(); ?>
</body>
</html>
