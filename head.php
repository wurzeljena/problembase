<?php
	if (isset($_SESSION['user_id']))
		$user_id = $_SESSION['user_id'];
?>
<div class="head"><div style="width:50em;">
	<div class="logo">&#x221A;<span style="text-decoration:overline">WURZEL</span></div>
	<?php if (!isset($user_id)) { ?>
		<form class="login" action="logon.php" method="POST">
		<input type="text" style="width:15em;" name="email" placeholder="E-Mail">
		<input type="password" name="password" placeholder="Passwort">
		<input type="hidden" name="referer" value="<?php print $_SERVER['REQUEST_URI']; ?>">
		<input type="submit" value="Login">
		</form>
	<?php } else { ?>
		<form class="login" action="logon.php" method="POST">
		Sie sind angemeldet als: <?php print $_SESSION['user_name']; ?>
		<input type="hidden" name="logout" value="1">
		<input type="hidden" name="referer" value="<?php print $_SERVER['REQUEST_URI']; ?>">
		<input type="submit" value="Logout">
		</form>
	<?php } ?>
	<div style="font-family:sans-serif; font-size:x-small;">Aufgabendatenbank <br /> &copy; 2012 <a href="http://www.wurzel.org/" target="_blank">Wurzel e.V.</a></div>
</div></div>
