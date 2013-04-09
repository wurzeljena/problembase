<?php
	session_start();
	include 'head.php';
	include 'proposers.php';
	include 'tasklist.php';

	printhead();
	$pb = new SQLite3('sqlite/problembase.sqlite');
?>
<body>
	<?php printheader(); ?>

	<div class="center">
	<div id="panel">
	<?php drawMenu("sidemenu"); ?>

	<form class="filter" id="filter" title="Filter" action="<?=$_SERVER["PBROOT"]?>/" method="GET">
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
			<?php
				if (isset($_REQUEST['tags']))
					$tags = $_REQUEST['tags'];
				else
					$tags = "";
				tag_form($pb, "filter", $tags);
			?>
		</div>
	</form>
	</div>

	<div class="content" id="tasklist"></div>

	<div id="pager">
		<a href="javascript:incrPage(-5);" class="button">&laquo;</a>
		<a href="javascript:incrPage(-1);" class="button">&lsaquo;</a>
		Seite <span id="page">1</span>
		<input type="hidden" id="request" value="<?php print $_SERVER['QUERY_STRING']; ?>">
		<a href="javascript:incrPage(1);" class="button">&rsaquo;</a>
		<a href="javascript:incrPage(5);" class="button">&raquo;</a>
	</div>
	</div>

	<!-- show first page of content -->
	<script>incrPage(0);</script>

	<?php $pb->close(); ?>
</body>
</html>
