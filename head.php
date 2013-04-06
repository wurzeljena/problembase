<?php
	if (isset($_SESSION['user_id']))
		$user_id = $_SESSION['user_id'];

	function drawMenu($id) {?>
	<nav id="<?=$id?>">
	<ul>
		<li><a href="index.php"><i class="icon-th-list"></i> <span>&Uuml;bersicht</span></a>
</li><?php	if (isset($_SESSION['user_id'])): ?><li>
		<a href="problem.php"><i class="icon-plus"></i> <span>Neue Aufgabe</span></a>
</li><li>
		<a href="user.php"><i class="icon-group"></i> <span>Benutzerliste</span></a>
</li><li>
		<a href="tagpanel.php?standalone"><i class="icon-tags"></i> <span>Tag-Editor</span></a>
</li><?php endif; ?>
	</ul>
	</nav>
<?php } ?>
<div class="head"><div class="center">
	<span class="logo">
		<a href="index.php">&#x221A;<span style="text-decoration:overline">WURZEL</span></a>
	</span>
	<?php drawMenu("headermenu"); ?>
	<form class="login" action="logon.php" method="POST">
	<input type="hidden" name="referer" value="<?php print $_SERVER['REQUEST_URI']; ?>">
	<?php if (!isset($user_id)) { ?>
		<input type="email" style="width:15em;" name="email" placeholder="E-Mail">
		<input type="password" name="password" placeholder="Passwort">
		<input type="submit" value="Login">
	<?php } else { ?>
		Sie sind angemeldet als: <?php print $_SESSION['user_name']; ?>
		<input type="hidden" name="logout" value="1">
		<input type="submit" value="Logout">
	<?php } ?>
	</form>
	<div style="font-family:sans-serif; font-size:x-small;">Aufgabendatenbank <br /> &copy; 2012 <a href="http://www.wurzel.org/" target="_blank">Wurzel e.V.</a></div>
</div></div>
