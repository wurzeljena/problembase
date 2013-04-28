<?php
	session_start();
	$pb = new SQLite3('sqlite/problembase.sqlite');

	if (isset($_POST["logout"]))
		session_destroy();
	else {
		// look up user ID and password hash
		$user = $pb->querySingle("SELECT id, name, encr_pw, root, editor, strftime('%s','now')-strftime('%s',wait_till) AS wait "
			."FROM users WHERE email='{$pb->escapeString($_POST["email"])}'", true);

		// check if we don't have to wait and for correct password
		if ($user['wait'] >= 0 && ($user['encr_pw'] == "" || $user['encr_pw'] == crypt($_POST["password"], $user['encr_pw']))) {
			$_SESSION['user_id'] = $user['id'];
			$_SESSION['user_name'] = $user['name'];
			$_SESSION['email'] = $_POST["email"];
			$_SESSION['root'] = $user['root'];
			$_SESSION['editor'] = $user['editor'];

			$pb->exec("UPDATE users SET wait_till=null WHERE email='{$pb->escapeString($_POST["email"])}'");
			unset($_SESSION['wait']);
		}
		else if ($user['wait'] >= 0) {
			$pb->exec("UPDATE users SET wait_till=datetime('now', '+10 seconds') WHERE email='{$pb->escapeString($_POST["email"])}'");
			$_SESSION['wait'] = date(DATE_ATOM, strtotime("+10 seconds"));
		}
	}

	$pb->close();

	// redirect to referer
	header('Location: '.$_SERVER['HTTP_REFERER']);
?>
