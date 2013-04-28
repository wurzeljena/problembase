<?php
	if (isset($_SESSION['user_id']))
		$user_id = $_SESSION['user_id'];

	function printhead($title = "", $ok = true) {
?>
<!DOCTYPE html>
<html lang="de">
<head>
	<title><?=$title.($title ? " &middot; " : "")?>Aufgabendatenbank</title>
	<meta charset="UTF-8">
	<meta name="author" content="Wurzel e.V."/>
	<meta name="viewport" content="user-scalable=no,width=device-width">
	<link rel="stylesheet" type="text/css" href="<?=$_SERVER["PBROOT"]?>/pb.css"/>
	<link rel="stylesheet" type="text/css" href="<?=$_SERVER["PBROOT"]?>/Font-Awesome/css/font-awesome.css"/>
	<link rel="icon" href="<?=$_SERVER["PBROOT"]?>/dw.ico"/>
<?php	if ($ok): ?>
	<script type="text/javascript"> var rootdir="<?=$_SERVER["PBROOT"]?>"; </script>
	<script type="text/javascript" src="<?=$_SERVER["PBROOT"]?>/MathJax/MathJax.js?config=TeX-AMS_HTML"></script>
	<script type="text/javascript" src="<?=$_SERVER["PBROOT"]?>/fancy.js"></script>
	<script type="text/javascript" src="<?=$_SERVER["PBROOT"]?>/ajax.js"></script>
<?php	else: ?>
	<style type="text/css">
		li.alt {color: Gray;}
		li.alt:before {content: "[";}
		li.alt:after {content: "]";}
		li.alt a {color: Gray;}
	</style>
<?php	endif; ?>
</head>
<?php }

function drawMenu($id) { ?>
	<nav id="<?=$id?>">
	<ul>
		<li><a href="<?=$_SERVER["PBROOT"]?>/"><i class="icon-th-list"></i> <span>&Uuml;bersicht</span></a>
</li><?php	if (isset($_SESSION['user_id'])): ?><li>
		<a href="<?=$_SERVER["PBROOT"]?>/new"><i class="icon-plus"></i> <span>Neue Aufgabe</span></a>
</li><li>
		<a href="<?=$_SERVER["PBROOT"]?>/users/"><i class="icon-group"></i> <span>Benutzerliste</span></a>
</li><li>
		<a href="<?=$_SERVER["PBROOT"]?>/tags/"><i class="icon-tags"></i> <span>Tag-Editor</span></a>
</li><?php	endif;
		if ($id == "headermenu"): ?><li>
		<a href="javascript:Login.Show();"><i class="icon-signin"></i></a>
</li><?php endif; ?>
	</ul>
	</nav>
<?php }

	function printheader() {
		global $user_id; ?>
<div class="head"><div class="center">
	<a id="logo" href="<?=$_SERVER["PBROOT"]?>/">&#x221A;<span style="text-decoration:overline">WURZEL</span></a>
	<?php drawMenu("headermenu"); ?>
	<form id="login" action="<?=$_SERVER["PBROOT"]?>/logon.php" method="POST">
	<?php if (!isset($user_id)) { ?>
		<span id="wait"></span>
		<input type="email" style="width:15em;" name="email" placeholder="E-Mail">
		<input type="password" name="password" placeholder="Passwort">
		<input type="submit" value="Login">
	<?php } else {
			print "<span id='username'>{$_SESSION['user_name']}</span>";
		?>
		<input type="hidden" name="logout" value="1">
		<input type="submit" value="Logout">
	<?php } ?>
	</form>
	<script>
		var Login = new PopupTrigger("login");
<?php	if (isset($_SESSION['wait'])) { ?>
		var Wait = new WaitTimer("<?=$_SESSION['wait']?>"); <?php } ?>
	</script>
</div></div>
<?php } ?>
