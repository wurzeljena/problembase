<?php
	include '../../lib/master.php';
	$pb = load(LOAD_DB | INC_HEAD | INC_TAGS | INC_PROPOSERS | INC_TASKLIST);

	$tag = new Tag;
	$name = str_replace("_", " ", $_GET["name"]);
	$tag->from_name($pb, $name);

	// if no such tag exists, throw a 404 error
	if (!$tag->is_valid())
		http_error(404, "Tag nicht gefunden");

	// find problems
	$filter = new Filter();
	$hash = $filter->set_params(array("tags" => $_GET["name"]));
	$filter->construct_query($pb, array("number ASC"));
	$filter->filter(true);
	$pages = ceil(count($filter->array)/10);
	$page = isset($_GET['page']) ? (int)($_GET['page']-1) : 0;
	if ($page < 0 || $page >= $pages)
		$page = 0;

	printhead(str_replace("private/", "", $name));
?>
<body>
	<?php printheader(); ?>

	<div class="center">
	<div id="panel">
		<?php
			drawMenu("sidemenu");
			if ($_SESSION["editor"]):
		?>
		<iframe src="<?=WEBROOT?>/tags?iframe" style="border:none;overflow:hidden" width="270" height="270"></iframe>
		<?php endif; ?>
	</div>

	<div class="content">
		<h3 id="tag"><span class="extra">Übersicht zu</span> </h3>
		<script>
			var tag_header = document.getElementById("tag");
			<?php print $tag->js("tag_header"); ?>
		</script>
		<?php
			// Proposer-Statistik
			$propstat = $tag->proposer_statistic($pb);
			$propstat->print_statistic();
		?>

		<h3 id="problems">Aufgaben</h3>
		<div id="tasklist"></div>
	</div>

	<div id="pager">
		<a href="javascript:pageLoader.setPage(0);" class="button"><i class="icon-double-angle-left"></i></a>
		<a href="javascript:pageLoader.setPage(pageLoader.getPage() - 1);" class="button"><i class="icon-angle-left"></i></a>
		Seite <span id="page"><?=($page+1)?></span>/<?=$pages?>
		<a href="javascript:pageLoader.setPage(pageLoader.getPage() + 1);" class="button"><i class="icon-angle-right"></i></a>
		<a href="javascript:pageLoader.setPage(<?=($pages-1)?>);" class="button"><i class="icon-double-angle-right"></i></a>
	</div>
	</div>

	<!-- Initialize page loader -->
	<script>
		pageLoader = new PageLoader("<?=$hash?>", <?=$pages?>, <?=$page?>);
	</script>
</body>
</html>
