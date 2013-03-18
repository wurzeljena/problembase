<?php
	if (isset($_SESSION['user_id']))
		$user_id = $_SESSION['user_id'];
?>
<div class="head"><div style="width:50em;">
	<div class="logo">
		<a href="index.php">&#x221A;<span style="text-decoration:overline">WURZEL</span></a>
	</div>
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
