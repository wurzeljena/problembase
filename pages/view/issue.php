<?php
	include '../../lib/master.php';
	$pb = load(LOAD_DB | INC_HEAD | INC_PROBLEMS | INC_SOLUTIONS);

	$month = $_GET['month']; $year = $_GET['year'];
	printhead("Heft $month/$year");

	// find problems
	$filter = new Filter();
	$hash = $filter->set_params(array("year" => $year, "month" => $month));
	$filter->construct_query($pb, array("number ASC"));
	$filter->filter(false);

	// generate list
	$tasklist = new ProblemList($pb, $filter->array);

	// generate solution list
	$sollist = new SolutionList($pb, array("year=$year", "month=$month"));
?>
<body>
	<?php printheader(); ?>

	<div class="center">
	<aside id="panel">
	<?php drawMenu("sidemenu");
		printcalendar($year, $month); ?>
	</aside>

	<div class="content" id="tasklist">
		<h2 class="issue">Heft <?=$month?>/<?=$year?></h2>
		<h3 id="problems">Aufgaben
			<a class='button' style='float:right;' href='<?=WEBROOT?>/issues/<?=$year?>/<?=$month?>/problems'><i class='fa fa-cloud-download'></i> T<div class="tex">E</div>X</a>
		</h3>
		<?=$tasklist->print_html()?>

		<h3 id="solutions">Lösungen
			<a class='button' style='float:right;' href='<?=WEBROOT?>/issues/<?=$year?>/<?=$month?>/solutions'><i class='fa fa-cloud-download'></i> T<div class="tex">E</div>X</a>
		</h3>
		<?=$sollist->print_html(false, true)?>
	</div>
	</div>

	<?php $pb->close(); ?>
</body>
</html>
