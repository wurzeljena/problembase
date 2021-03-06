<?php
	include '../lib/master.php';
	$pb = load(LOAD_DB | INC_HEAD | INC_PROBLEMS);

	printhead();

	// initialize cache if necessary
	if (!isset($_SESSION['cache']))
		$_SESSION['cache'] = array();

	// do the filtering and save the result
	$filter = new Filter();
	$hash = $filter->set_params($_GET);
	$filter->construct_query($pb, array("proposed DESC", "year DESC", "month DESC"));
	$filter->filter(true);
	$pages = ceil(count($filter->array)/10);
	$page = isset($_GET['page']) ? (int)($_GET['page']-1) : 0;
	if ($page < 0 || $page >= $pages)
		$page = 0;
?>
<body>
	<?php printheader(); ?>

	<div class="center">
	<aside id="panel">
	<?php drawMenu("sidemenu"); ?>

	<form class="filter" id="filter" title="Filter" action="<?=WEBROOT?>/browse" method="GET">
		<div><input type="text" name="filter" placeholder="Suchbegriff"
			value="<?php if (isset($_GET['filter'])) print $_GET['filter']; ?>"/>
		<input type="submit" value="Suchen"></div>
		<div style="margin-bottom:5px;">
			<input type="checkbox" name="not_published" id="not_published" <?php if (isset($_GET['not_published'])) print "checked"; ?>/>
				<label class="info" for="not_published">unveröffentlicht</label>
			<input type="checkbox" name="with_solution" id="with_solution" <?php if (isset($_GET['with_solution'])) print "checked"; ?>/>
				<label class="info" for="with_solution">mit Lösung</label>
		</div>
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
				$tags = new TagList;
				if (isset($_GET['tags']))
					$tags->from_list($pb, str_replace("_", " ", $_GET['tags']));
				tag_form($pb, "filter", $tags);
			?>
		</div>
	</form>

	<?php $date = getdate(); printcalendar($date['year'], -1); ?>
	</aside>

	<div class="content" id="tasklist"></div>

	<div id="pager">
		<a href="javascript:pageLoader.setPage(0);" class="button"><i class="fa fa-angle-double-left"></i></a>
		<a href="javascript:pageLoader.setPage(pageLoader.getPage() - 1);" class="button"><i class="fa fa-angle-left"></i></a>
		Seite <span id="page"><?=($page+1)?></span>/<?=$pages?>
		<a href="javascript:pageLoader.setPage(pageLoader.getPage() + 1);" class="button"><i class="fa fa-angle-right"></i></a>
		<a href="javascript:pageLoader.setPage(<?=($pages-1)?>);" class="button"><i class="fa fa-angle-double-right"></i></a>
	</div>
	</div>

	<!-- show first page of content -->
	<script>
		pageLoader = new PageLoader("<?=$hash?>", <?=$pages?>, <?=$page?>);
	</script>

	<?php $pb->close(); ?>
</body>
</html>
