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
	<meta name="viewport" content="user-scalable=no,width=device-width">
	<link rel="stylesheet" type="text/css" href="pb.css"/>
	<link rel="stylesheet" type="text/css" href="Font-Awesome/css/Font-Awesome.css"/>
	<link rel="icon" href="dw.ico"/>
	<script type="text/javascript" src="MathJax/MathJax.js?config=TeX-AMS_HTML"></script>
	<script type="text/javascript" src="fancy.js"></script>
	<script type="text/javascript" src="ajax.js"></script>
</head>
<body>
	<?php include 'head.php'; ?>
	<?php $pb = new SQLite3('sqlite/problembase.sqlite'); ?>

	<div class="center">
	<div id="panel">
	<?php drawMenu("sidemenu"); ?>
	<form class="filter" id="filter" title="Filter" action="index.php" method="GET">
		<div><input type="text" name="filter" placeholder="Suchbegriff"
			value="<?php if (isset($_REQUEST['filter'])) print $_REQUEST['filter']; ?>"/>
		<input type="submit" value="Filtern"></div>
		<div id="questions">
		<div>
			<label class="question" for="proposer">Wer?</label>
			<input type="text" name="proposer" id="proposer" placeholder="Autor" list="proposers"
				value="<?php if (isset($_REQUEST['proposer'])) print $_REQUEST['proposer']; ?>"/>
			<?php proposers_datalist($pb); ?>
		</div>
		<div>
			<label class="question" for="evaluation">Wie?</label>
			<span id="evaluation">{Bewertungsbereich}</span>
		</div>
		<div>
			<label class="question" for="number">Wann?</label>
			<input type="text" name="number" id="number" placeholder="MM/JJ" style="width:45px;"
				pattern="([1-9]|0[1-9]|1[0-2])/[0-9]{2}" value="<?php if (isset($_REQUEST['number'])) print $_REQUEST['number']; ?>"/>
			<input type="checkbox" name="with_solution" id="with_solution" <?php if (isset($_REQUEST['with_solution'])) print "checked"; ?>/>
				<label class="info" for="with_solution">mit L&ouml;sung</label>
		</div>
		<div>
			<label class="info" for="start">nach</label>
			<input type="date" name="start" id="start" placeholder="JJJJ-MM-TT" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])"
				value="<?php if (isset($_REQUEST['start'])) print $_REQUEST['start']; ?>"/>
		</div>
		<div>
			<label class="info" for="end">vor</label>
			<input type="date" name="end" id="end" placeholder="JJJJ-MM-TT" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])"
				value="<?php if (isset($_REQUEST['end'])) print $_REQUEST['end']; ?>"/>
		</div>
		</div>
		<div class="taglist">
			<label class="question" for="tag_select">Was?</label>
			<?php tag_select($pb, "filter"); ?>
			<input type="hidden" name="tags" value="<?php if (isset($_REQUEST['tags'])) print $_REQUEST['tags']; ?>"/>
			<div id="taglist" style="margin:3px;">
				<?php if (isset($_REQUEST['tags'])) tags($pb, $_REQUEST['tags'], 'filter'); ?>
			</div>
		</div>
	</form>
	</div>

	<div class="content" id="tasklist">
		<?php tasklist($pb, taskquery($pb, 0)); ?>
	</div>

	<div id="pager">
		<a href="javascript:incrPage(-5);" class="button">&laquo;</a>
		<a href="javascript:incrPage(-1);" class="button">&lsaquo;</a>
		Seite <span id="page">1</span>
		<input type="hidden" id="request" value="<?php print $_SERVER['QUERY_STRING']; ?>">
		<a href="javascript:incrPage(1);" class="button">&rsaquo;</a>
		<a href="javascript:incrPage(5);" class="button">&raquo;</a>
	</div>
	</div>

	<?php $pb->close(); ?>
</body>
</html>
