<?php
	if (session_status() != PHP_SESSION_ACTIVE)
		session_start();
	include 'head.php';
	header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
	if (!isset($error))
		$error = "Seite nicht gefunden";
	printhead($error, false);
	if (isset($_SERVER['HTTP_REFERER'])) {
		if (parse_url($_SERVER['HTTP_REFERER'], PHP_URL_HOST) == $_SERVER['SERVER_NAME'])
			$origin = 1;
		else $origin = -1;
	}
	else
		$origin = 0;
?>
<body>
	<?php printheader(); ?>

	<div class="center">
	<div id="panel">
	<?php drawMenu("sidemenu"); ?>
	</div>

	<div class="content">
	<h3><?=$error?></h3>
	<p>Sowas sollte eigentlich nicht passieren. Das tut uns leid.</p>

	<ul>
	<li <?= ($origin != -1) ? "class='alt'" : ""?>>Sind Sie durch einen Link von einer anderen Webseite hier gelandet? Dann kontaktieren sie bitte die Verantwortlichen dieser Seite und bitten sie, den Link zu korrigieren oder zu entfernen.</li>
	<li <?= ($origin != 0) ? "class='alt'" : ""?>>Haben Sie eine falsche Adresse eingegeben? Dann gehen Sie zur <a href="<?=$_SERVER["PBROOT"]?>/">Hauptseite</a> und suchen von dort die gew&uuml;nschte Seite.</li>
	<li <?= ($origin != 1) ? "class='alt'" : ""?>>Sind Sie durch einen internen Link hierher gekommen, schreiben Sie bitte dem
	<a href="<?php
		print "mailto:{$_SERVER['SERVER_ADMIN']}";
		print "?subject=Unerwarteter%20Fehler%20404";
		if (isset($_SERVER['HTTP_REFERER']))
			print "&body=Die%20Seite%20{$_SERVER['HTTP_REFERER']}%20verlinkt%20auf%20die%20nicht%20existierende%20Seite%20{$_SERVER['REQUEST_URI']}.";
		?>">Administrator</a>, wie es dazu kam.</li>
	</ul>

	<p>Zur&uuml;ck zur <a href="javascript:history.back();">vorhergehenden</a> oder zur <a href="<?=$_SERVER["PBROOT"]?>/">Hauptseite</a>.</p>

	<hr/>
	<p class="info"><?=$_SERVER['SERVER_SOFTWARE']?></p>
	</div>
	</div>
</body>
</html>
