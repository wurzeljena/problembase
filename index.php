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

	<div class="content" id="tasklist"></div>

	<form class="filter" id="filter" title="Filter" action="<?=$_SERVER["PBROOT"]?>/" method="GET">
		<div><input type="text" name="filter" placeholder="Suchbegriff"
			value="<?php if (isset($_REQUEST['filter'])) print $_REQUEST['filter']; ?>"/>
		<input type="submit" value="Filtern"></div>
		<table style="border-top:1px solid Gray; border-bottom:1px solid Gray;">
			<tr>
				<td><label class="question" for="proposer">Wer?</label></td>
				<td><input type="text" name="proposer" id="proposer" placeholder="Autor" list="proposers"
					value="<?php if (isset($_REQUEST['proposer'])) print $_REQUEST['proposer']; ?>"/> </td>
				<?php proposers_datalist($pb); ?>
			</tr>
			<tr>
				<td><label class="question" for="evaluation">Wie?</label></td>
				<td><span id="evaluation">{Bewertungsbereich}</span></td>
			</tr>
			<tr>
				<td><label class="question" for="number">Wann?</label></td>
				<td><input type="text" name="number" id="number" placeholder="MM/JJ" style="width:45px;" pattern="([1-9]|0[1-9]|1[0-2])/[0-9]{2}"
					value="<?php if (isset($_REQUEST['number'])) print $_REQUEST['number']; ?>"/>
				<input type="checkbox" name="with_solution" id="with_solution" <?php if (isset($_REQUEST['with_solution'])) print "checked"; ?>/><label class="info" for="with_solution">mit L&ouml;sung</label></td>
			</tr>
			<tr>
				<td><label class="info" for="start">nach</label></td>
				<td><input type="date" name="start" id="start" placeholder="JJJJ-MM-TT" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])"
					value="<?php if (isset($_REQUEST['start'])) print $_REQUEST['start']; ?>"/></td>
			</tr>
			<tr>
				<td><label class="info" for="end">vor</label></td>
				<td><input type="date" name="end" id="end" placeholder="JJJJ-MM-TT" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])"
					value="<?php if (isset($_REQUEST['end'])) print $_REQUEST['end']; ?>"/></td>
			</tr>
		</table>
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

	<div id="panel">
		<?php if (isset($user_id)) {
				print "<a href='{$_SERVER["PBROOT"]}/new' class='button'>Neue Aufgabe</a>";
				print "<a href='{$_SERVER["PBROOT"]}/users' class='button'>Benutzerliste</a>";
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

	<!-- show first page of content -->
	<script>incrPage(0);</script>

	<?php $pb->close(); ?>
</body>
</html>
