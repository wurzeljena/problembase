<?php
	session_start();
	$pb = new SQLite3('sqlite/problembase.sqlite');

	if (isset($_POST["logout"]))
		session_destroy();
	else {
		// look up user ID and password hash
		$user = $pb->querySingle("SELECT * FROM users WHERE email='{$pb->escapeString($_POST["email"])}'", true);

		// check for correct password.
		if ($user['encr_pw'] == "" || $user['encr_pw'] == hash("sha256", $_POST["password"])) {
			$_SESSION['user_id'] = $user['id'];
			$_SESSION['user_name'] = $user['name'];
			$_SESSION['email'] = $email;
			$_SESSION['root'] = $user['root'];
			$_SESSION['editor'] = $user['editor'];
		}
	}

	$pb->close();

	// redirect to referer
	header('Location: '.$_SERVER['HTTP_REFERER']);
?>
