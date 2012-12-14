<?php
// Northpole.fi
// Moderator login
// 17.2.2010

require_once("../inc/include.php");

if($cfg['user_class'] == 0) {
	if(!isset($_POST['login_submit'])) {
		$mod_pages = true;
		$title = T_("Login"). $cfg['site_title'];
		include($cfg['srvdir'] ."/inc/header.php");
		
		echo '
		<h1>'. T_("Login") .'</h1>
		<div id="loginbox">
			<form id="login" name="login" action="'. $cfg['htmldir'] .'/mod/login/" method="post">
				<ul>
					<li>
						<label for="username">'. T_("Username") .'</label>
						<input type="text" name="username" id="username" />
					</li>
					<li>
						<label for="password">'. T_("Password") .'</label>
						<input type="password" name="password" id="password" />
					</li>
					<li>
						<label for="remember">'. T_("Remember me") .'</label>
						<input type="checkbox" name="remember" id="remember" />
					</li>
					<li>
						<input type="submit" value="'. T_("Login") .'" name="login_submit" id="login_submit" />
					</li>
				</ul>
			</form>
		</div>
		';
		
		include($cfg['srvdir'] ."/inc/footer.php");
	}
	else {
		// Do login
		
		$username = mysql_real_escape_string($_POST['username']);
		$password = encrypt_password($_POST['password']);
		$query = mysql_query("SELECT `id` FROM `admin_users` WHERE `name` = '". $username ."' AND `password` = '". $password ."' LIMIT 1");
		if(mysql_num_rows($query) != 0) {
			$id = mysql_result($query, 0, "id");
			$ip = mysql_real_escape_string(encrypt_ip(get_ip()));
		
			if(!empty($_POST['remember']) AND $_POST['remember'] == "on") $time = time() + (60 * 60 * 24 * 365);
			else $time = 0;
			setcookie("mod", $username ."|". $password, $time, '/');
			mysql_query("UPDATE `admin_users` SET `last_login` = '". time() ."', `last_ip` = '". $ip ."' WHERE `id` = '". $id ."' LIMIT 1");
			header("Location: ". $cfg['htmldir'] ."/mod/index/");
		
		}
		else error(T_("Wrong username or password!"));
	}
}
else {
	header("Location: ". $cfg['htmldir'] ."/mod/index/");
}
?>
