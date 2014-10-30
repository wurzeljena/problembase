<?php
	include '../../lib/master.php';
	$pb = load(LOAD_DB | INC_HEAD | INC_PROBLEMS | INC_SOLUTIONS);

	// get name and location, if set
	$data = array();
	$data["name"] = str_replace("_", " ", $_GET["name"]);
	if (isset($_GET["location"]))
		$data["location"] = str_replace("_", " ", $_GET["location"]);
	$desc = $data["name"].(isset($data["location"]) ? ", ".$data["location"] : "");

	// get proposer(s)
	$proposers = new ProposerList;
	$proposers->get($pb, array("id", "name", "location", "country"),
		$data["name"], isset($data["location"]) ? $data["location"] : null);

	// if no such proposer exists, throw a 404 error
	if (!$proposers->count())
		http_error(404, "Autor nicht gefunden");

	// find problems
	$filter = new Filter();
	$hash = $filter->set_params($data);
	$filter->construct_query($pb, array("number ASC"));
	$filter->filter(true);
	$pages = ceil(count($filter->array)/10);
	$page = isset($_GET['page']) ? (int)($_GET['page']-1) : 0;
	if ($page < 0 || $page >= $pages)
		$page = 0;

	// generate solution list
	/*$cond = array("name='{$data["name"]}'");
	if (isset($data["location"]))
		$cond[] = "location='{$data["location"]}'";
	$sollist = new SolutionList($pb, $cond);*/

	printhead($desc);
?>
<body>	<?php printheader(); ?>

	<div class="center">
	<div id="panel">
	<?php drawMenu("sidemenu"); ?>
	</div>

	<div class="content">
		<h3 id="proposer"><span class="extra">Übersicht zu</span>
			<?php print $desc; ?></h3>
		<?php
			if (!isset($data["location"])) {
				// print all locations as links
			}

			// Tag-Statistik
			$tagstat = $proposers->tag_statistic($pb);
			$tagstat->print_statistic();
		?>

		<h3 id="problems">Aufgaben</h3>
		<div id="tasklist"></div>

		<?php /*<h3 id="solutions">Lösungen</h3>*/ ?>
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
