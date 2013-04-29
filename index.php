<?php
	session_start();
	include 'head.php';
	include 'proposers.php';
	include 'tasklist.php';

	printhead();
	$pb = new SQLite3('sqlite/problembase.sqlite');

	// initialize cache if necessary
	if (!isset($_SESSION['cache']))
		$_SESSION['cache'] = array();

	// do the filtering and save the result
	$hash = taskfilter($pb);
	$pages = ceil(count($_SESSION['cache'][$hash])/10);
?>
<body>
	<?php printheader(); ?>

	<div class="center">
	<div id="panel">
	<?php drawMenu("sidemenu"); ?>

	<form class="filter" id="filter" title="Filter" action="<?=$_SERVER["PBROOT"]?>/" method="GET">
		<div><input type="text" name="filter" placeholder="Suchbegriff"
			value="<?php if (isset($_GET['filter'])) print $_GET['filter']; ?>"/>
		<input type="submit" value="Suchen"></div>
		<div id="questions">
		<div>
			<label class="question" for="proposer">Wer?</label>
			<input type="text" name="proposer" id="proposer" placeholder="Autor" list="proposers"
				value="<?php if (isset($_GET['proposer'])) print $_GET['proposer']; ?>"/>
			<?php proposers_datalist($pb); ?>
		</div>
		<div>
			<label class="question" for="evaluation">Wie?</label>
			<span id="evaluation">{Bewertungsbereich}</span>
		</div>
		<div>
			<label class="question" for="number">Wann?</label>
			<input type="text" name="number" id="number" placeholder="MM/JJ" style="width:45px;"
				pattern="([1-9]|0[1-9]|1[0-2])/[0-9]{2}" value="<?php if (isset($_GET['number'])) print $_GET['number']; ?>"/>
			<input type="checkbox" name="with_solution" id="with_solution" <?php if (isset($_GET['with_solution'])) print "checked"; ?>/>
				<label class="info" for="with_solution">mit L&ouml;sung</label>
		</div>
		<div>
			<label class="info" for="start">nach</label>
			<input type="date" name="start" id="start" placeholder="JJJJ-MM-TT" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])"
				value="<?php if (isset($_GET['start'])) print $_GET['start']; ?>"/>
		</div>
		<div>
			<label class="info" for="end">vor</label>
			<input type="date" name="end" id="end" placeholder="JJJJ-MM-TT" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])"
				value="<?php if (isset($_GET['end'])) print $_GET['end']; ?>"/>
		</div>
		</div>
		<div class="taglist">
			<label class="question" for="tag_select">Was?</label>
			<?php
				if (isset($_GET['tags']))
					$tags = $_GET['tags'];
				else
					$tags = "";
				tag_form($pb, "filter", $tags);
			?>
		</div>
	</form>
	</div>

	<div class="content" id="tasklist"></div>

	<div id="pager">
		<a href="javascript:pageLoader.incrPage(-5);" class="button">&laquo;</a>
		<a href="javascript:pageLoader.incrPage(-1);" class="button">&lsaquo;</a>
		Seite <span id="page">1</span>/<?=$pages?>
		<a href="javascript:pageLoader.incrPage(1);" class="button">&rsaquo;</a>
		<a href="javascript:pageLoader.incrPage(5);" class="button">&raquo;</a>
	</div>
	</div>

	<!-- show first page of content -->
	<script>
		pageLoader.set("<?=$hash?>", <?=$pages?>);
		pageLoader.loadPage();
	</script>

	<?php $pb->close(); ?>
</body>
</html>
