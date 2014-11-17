<?php
	function printhead($title = "", $ok = true) {
?>
<!DOCTYPE html>
<html lang="de">
<head>
	<title><?=$title.($title ? " &middot; " : "")?>Aufgabendatenbank</title>
	<meta charset="UTF-8">
	<meta name="author" content="Wurzel e.V."/>
	<meta name="viewport" content="user-scalable=no,width=device-width">
	<link rel="stylesheet" type="text/css" href="<?=WEBROOT?>/pb.css"/>
	<link rel="stylesheet" type="text/css" href="<?=WEBROOT?>/Font-Awesome/css/font-awesome.css"/>
	<link rel="icon" href="<?=WEBROOT?>/img/dw.ico"/>
<?php	if ($ok): ?>
	<script type="text/javascript"> var rootdir="<?=WEBROOT?>"; </script>
	<script type="text/javascript" src="<?=WEBROOT?>/MathJax/MathJax.js?config=TeX-AMS_HTML"></script>
	<script type="text/javascript" src="<?=WEBROOT?>/js/fancy.js"></script>
	<script type="text/javascript" src="<?=WEBROOT?>/js/ajax.js"></script>
<?php	else: ?>
	<style type="text/css">
		li.alt {color: Gray; font-style: italic;}
		li.alt a {color: Gray;}
	</style>
<?php	endif; ?>
</head>
<?php }

function drawMenu($id) { ?>
	<nav id="<?=$id?>">
	<ul>
		<li><a href="<?=WEBROOT?>/browse"><i class="icon-home"></i> <span>Übersicht</span></a>
</li><?php	if ($_SESSION['user_id'] != -1): ?><li>
		<a href="<?=WEBROOT?>/problem"><i class="icon-plus"></i> <span>Neue Aufgabe</span></a>
</li><?php	if ($_SESSION['editor'] || $_SESSION["root"]): ?><li>
		<a href="<?=WEBROOT?>/users"><i class="icon-group"></i> <span>Benutzerliste</span></a>
</li><?php	endif; ?><li>
		<a href="<?=WEBROOT?>/tags"><i class="icon-tags"></i> <span>Tag-Editor</span></a>
</li><?php
		endif;
		if ($id == "headermenu"): ?><li>
		<a href="javascript:Login.Show();"><i class="icon-signin"></i></a>
</li><?php endif; ?>
	</ul>
	</nav>
<?php }

	function printheader() { ?>
<div class="head"><div class="center">
	<?php drawMenu("headermenu"); ?>
	<a id="logo" style="letter-spacing:-2px;" href="<?=WEBROOT?>/"><span style="font-size:115%;">&#x221A;</span><span style="text-decoration:overline">WURZEL</span></a>
	<form id="login" action="<?=WEBROOT?>/logon" method="POST">
	<?php if ($_SESSION['user_id'] == -1): ?>
		<span id="wait"></span>
		<input type="email" style="width:15em;" name="email" placeholder="E-Mail">
		<input type="password" name="password" placeholder="Passwort">
		<input type="submit" value="Login">
	<?php else:
			print "<a class='username' href='".WEBROOT."/users/{$_SESSION['user_id']}'>{$_SESSION['user_name']}</a>";
		?>
		<input type="hidden" name="logout" value="1">
		<input type="submit" value="Logout">
	<?php endif; ?>
	</form>
	<script>
		var Login = new PopupTrigger("login");
<?php	if (isset($_SESSION['wait'])) { ?>
		var Wait = new WaitTimer("<?=$_SESSION['wait']?>"); <?php } ?>
	</script>
</div></div>
<?php }

	function printcalendar($year, $month) { ?>
	<div id="calendar">
		<div style="text-align:center;"><a class="dirbutton icon-circle-arrow-left" href="javascript:calendar.incr_decade(-1)"></a><div id="years"></div>
			<a class="dirbutton icon-circle-arrow-right" href="javascript:calendar.incr_decade(1)"></a>
		</div>
		<div><h4 class="icon-calendar"></h4>
			<div id="months"></div>
		</div>
	</div>
	<script> calendar.init(<?=$year?>, <?=$month?>); </script> <?php
	}
?>
