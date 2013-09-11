<?php
	include '../../lib/master.php';
	$pb = load(LOAD_DB | INC_HEAD | INC_PROPOSERS | INC_TAGS | INC_TASKLIST | INC_SOLLIST);

	$month = $_GET['month']; $year = $_GET['year'];
	printhead("Heft $month/$year");

	// find problems
	$filter = new Filter();
	$hash = $filter->set_params(array("year" => $year, "month" => $month));
	$filter->construct_query($pb, array("number ASC"));
	$filter->filter(false);

	// generate list
	$tasklist = new TaskList($pb);
	$tasklist->set($filter->array);
	$tasklist->query(array("number ASC"));

	// generate solution list
	$sollist = new SolutionList($pb);
	$sollist->idstr = $pb->querysingle("SELECT group_concat(file_id) FROM solutions WHERE year=$year AND month=$month", false);
	$sollist->query($_SESSION['editor']);
?>
<body>
	<?php printheader(); ?>

	<div class="center">
	<div id="panel">
	<?php drawMenu("sidemenu");
		printcalendar($year, $month); ?>
	</div>

	<div class="content" id="tasklist">
		<h2 class="issue">Heft <?=$month?>/<?=$year?></h2>
		<h3 id="problems">Aufgaben
			<a class='button' style='float:right;' href='<?=WEBROOT?>/issues/<?=$year?>/<?=$month?>/problems'><i class='icon-cloud-download'></i> T<div class="tex">E</div>X</a>
		</h3>
		<?=$tasklist->print_html()?>

		<h3 id="solutions">L&ouml;sungen
			<a class='button' style='float:right;' href='<?=WEBROOT?>/issues/<?=$year?>/<?=$month?>/solutions'><i class='icon-cloud-download'></i> T<div class="tex">E</div>X</a>
		</h3>
		<?=$sollist->print_html(false, true)?>
	</div>
	</div>

	<?php $pb->close(); ?>
</body>
</html>
