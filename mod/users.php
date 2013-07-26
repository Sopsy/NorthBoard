<?php
// Northpole.fi
// Admin users
// 17.7.2010
require_once("../inc/include.php");


if($cfg['user_class'] == 0)
	header("Location: ". $cfg['htmldir'] ."/mod/login/");
else {
	$mod_pages = true;
	$title = T_("Board administration"). $cfg['site_title'];
	include($cfg['srvdir'] ."/inc/header.php");

	echo '
		<div id="padded">
			';
			
			if(empty($_GET['a'])) {
				
				echo '
				<h2>'. T_("Board administrators") .'</h2>
				<table class="table">
					<tr>
						<th>'. T_("User ID") .'</th>
						<th>'. T_("Username") .'</th>
						<th>'. T_("E-mail") .'</th>
						<th>'. T_("User class") .'</th>
						<th>'. T_("Last login") .'</th>
						<th>'. T_("Last active") .'</th>
						<th>'. T_("Created by") .'</th>
						<th>'. T_("Added") .'</th>
						<th>'. T_("Edit") .'</th>
					</tr>';
				
				$users_q = mysql_query("SELECT * FROM `admin_users` ORDER BY `id` ASC");
				while($b = mysql_fetch_assoc($users_q)) {
					
					if($b['user_class'] == 1) $b['user_class'] = T_("Admin");
					elseif($b['user_class'] == 2) $b['user_class'] = T_("SMod");
					elseif($b['user_class'] == 3) $b['user_class'] = T_("Mod");
				
					echo '
					<tr>
						<td>'. $b['id'] .'</td>
						<td>'. $b['name'] .'</td>
						<td>'. $b['email'] .'</td>
						<td>'. $b['user_class'] .'</td>
						<td>'. date(T_("Y/m/d g:i:s A"), $b['last_login']) .'</td>
						<td>'. date(T_("Y/m/d g:i:s A"), $b['last_active']) .'</td>
						<td>'. $b['added_by'] .'</td>
						<td>'. date(T_("Y/m/d g:i:s A"), $b['added_time']) .'</td>
						<td><a href="edit/'. $b['id'] .'/">'. T_("Edit") .'</td>
					</tr>';
				}
				
				echo '
				</table>';
			}
			elseif($_GET['a'] == "create") {
				if(empty($_POST)) {
					echo '
					<h2>'. T_("Create an account") .'</h2>
					<form action="'. $cfg['htmldir'] .'/mod/users/create/" method="post" id="adminform">
					<fieldset>
					
						<legend>'. T_("User details") .'</legend>
						<label for="uname">'. T_("Username") .':</label>
						<input type="text" name="uname" id="uname" />
						
						<label for="passwd">'. T_("Password") .':</label>
						<input type="text" name="passwd" id="passwd" />
						
						<label for="email">'. T_("E-mail") .':</label>
						<input type="text" name="email" id="email" />
						
						<label for="class">'. T_("User class") .':</label>
						<select name="class" id="class">
							<option value="1">'. T_("Admin") .'</option>
							<option value="2">'. T_("SMod") .'</option>
							<option value="3">'. T_("Mod") .'</option>
						</select>
						
						<legend>'. T_("You need to type in your password to prevent automated account creations") .'</legend>
						
						<label for="mypasswd">'. T_("Your password") .':</label>
						<input type="password" name="mypasswd" id="mypasswd" />
						
						<input type="submit" value="'. T_("Create") .'" name="add" id="add" />

					</fieldset>
					</form>';
				}
				else {
					if(!empty($_POST['uname'])) {
						$uname = mysql_real_escape_string($_POST['uname']);
					}
					else error(T_("Username is missing."), false);
					
					$minpwlen = 6;
					if(!empty($_POST['passwd']) AND strlen($_POST['passwd']) > $minpwlen) {
						$passwd = encrypt_password($_POST['passwd']);
					}
					else error(sprintf(T_("Password is missing or is too short (min. %s characters)."), $minpwlen), false);
					
					$email = mysql_real_escape_string($_POST['email']);
					
					if(empty($_POST['class']) OR !is_numeric($_POST['class']))
						error(T_("User class is missing or is malformed."), false);
					else
						$class = $_POST['class'];
						
					$pwq = mysql_query("SELECT `password` FROM `admin_users` WHERE `id` = '". $cfg['mod_id'] ."' LIMIT 1");
					$pw = mysql_result($pwq, 0, "password");
					if($pw != encrypt_password($_POST['mypasswd']))
						error(T_("Your password was wrong."), false);

					$q = mysql_query("SELECT * FROM `admin_users` WHERE `name` LIKE '". $uname ."' LIMIT 1");
					if(mysql_num_rows($q) != 0)
						error(T_("The given username is already in use!"), false);
					
					$q = mysql_query("INSERT INTO `admin_users`(`name`, `password`, `email`, `user_class`, `added_by`, `added_time`) VALUES ('". $uname ."', '". $passwd ."', '". $email ."', '". $class ."', '". $cfg['mod_id'] ."', UNIX_TIMESTAMP())");
					if($q)
						info(T_("User added successfully!"), false);
					else
						error(T_("Adding the user failed!"), false);
				}
			}
			elseif($_GET['a'] == "edit") {
				if(!empty($_GET['b']) OR !is_numeric($_GET['b'])) {
					if(empty($_POST)) {
						$q = mysql_query("SELECT * FROM `admin_users` WHERE `id` = '". mysql_real_escape_string($_GET['b']) ."' LIMIT 1");
						$u = mysql_fetch_assoc($q);
						echo '
						<h2>'. sprintf(T_("Edit account for %s"), $u['name']) .'</h2>
						<form action="'. $cfg['htmldir'] .'/mod/users/edit/'. $u['id'] .'" method="post" id="adminform">
						<fieldset>
						
							<legend>'. T_("User details") .'</legend>
							<label for="uname">'. T_("Username") .':</label>
							<input type="text" name="uname" id="uname" value="'. $u['name'] .'" />
							
							<label for="passwd">'. T_("Password") .':</label>
							<input type="text" name="passwd" id="passwd" />
							
							<label for="email">'. T_("E-mail") .':</label>
							<input type="text" name="email" id="email" value="'. $u['email'] .'" />
							
							<label for="class">'. T_("User class") .':</label>
							<select name="class" id="class">
								<option value="1"'. ($u['user_class'] == 1 ? ' selected="selected"' : '') .'>'. T_("Admin") .'</option>
								<option value="2"'. ($u['user_class'] == 2 ? ' selected="selected"' : '') .'>'. T_("SMod") .'</option>
								<option value="3"'. ($u['user_class'] == 3 ? ' selected="selected"' : '') .'>'. T_("Mod") .'</option>
							</select>
							
							<legend>'. T_("You need to type in your password to prevent automated account creations") .'</legend>
							
							<label for="mypasswd">'. T_("Your password") .':</label>
							<input type="password" name="mypasswd" id="mypasswd" />
							
							<input type="submit" value="'. T_("Create") .'" name="add" id="add" />

						</fieldset>
						</form>';
					}
					else {
						if(!empty($_POST['uname'])) {
							$uname = mysql_real_escape_string($_POST['uname']);
						}
						else error(T_("Username is missing."), false);
						
						$minpwlen = 6;
						if(!empty($_POST['passwd']) AND strlen($_POST['passwd']) > $minpwlen) {
							$passwd = encrypt_password($_POST['passwd']);
						}
						else
							$passwd = '';
						
						$email = mysql_real_escape_string($_POST['email']);
						
						if(empty($_POST['class']) OR !is_numeric($_POST['class']))
							error(T_("User class is missing or is malformed."), false);
						else
							$class = $_POST['class'];
							
						$pwq = mysql_query("SELECT `password` FROM `admin_users` WHERE `id` = '". $cfg['mod_id'] ."' LIMIT 1");
						$pw = mysql_result($pwq, 0, "password");
						if($pw != $passwd)
							error(T_("Your password was wrong."), false);

						$q = mysql_query("SELECT * FROM `admin_users` WHERE `name` LIKE '". $uname ."' LIMIT 1");
						if(mysql_num_rows($q) != 0)
							error(T_("The given username is already in use!"), false);
						
						
						die("NA");
						$q = mysql_query("UPDATE `admin_users`(`name`, `password`, `email`, `user_class`, `added_by`, `added_time`) VALUES ('". $uname ."', '". $passwd ."', '". $email ."', '". $class ."', '". $cfg['mod_id'] ."', UNIX_TIMESTAMP())");
						if($q)
							info(T_("User added successfully!"), false);
						else
							error(T_("Adding the user failed!"), false);
					}
				}
				else error(T_("User ID is missing or is not numeric!"));
			}
			elseif($_GET['a'] == "log") {
				$q = mysql_query("SELECT * FROM `modlog` ORDER BY `time` DESC");
				
				while($row = mysql_fetch_assoc($q))
				{
					print_r($row);
					echo '<br />';
				}
			}
			
			echo '
		</div>
	</div>
	';

	include($cfg['srvdir'] ."/inc/footer.php");
}

?>
