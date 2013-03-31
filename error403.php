<?php
	if (session_status() != PHP_SESSION_ACTIVE)
		session_start();
	include 'head.php';
	header($_SERVER["SERVER_PROTOCOL"]." 403 Forbidden");
	printhead("Zugriff nicht gestattet", false);
?>
<body>
	<?php printheader(); ?>
	<div class="content">
	<h3>Zugriff nicht gestattet</h3>
	<p>Sie haben nicht die n&ouml;tigen Rechte, um die Seite anzusehen bzw. Operation auszuf&uuml;hren. Wenn Sie glauben, dass das ein Fehler ist, wenden Sie sich bitte an den
	<a href="<?php
			 print "mailto:{$_SERVER['SERVER_ADMIN']}";
			 print "?subject=Unerwarteter%20Fehler%20403";
			 if (isset($_SESSION['user_name']))
				 print "&body={$_SESSION['user_name']}%20({$_SESSION['email']})%20hat%20keinen%20Zugriff%20auf%20die%20Seite%20{$_SERVER['REQUEST_URI']}.";
		?>">Administrator</a>.</p>

	<p>Zur&uuml;ck zur <a href="javascript:history.back();">vorhergehenden</a> oder zur <a href="<?=$_SERVER["PBROOT"]?>/">Hauptseite</a>.</p>

	<hr/>
	<p class="info"><?=$_SERVER['SERVER_SOFTWARE']?></p>
	</div>
</body>
</html>
