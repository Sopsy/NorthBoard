<?php
// Northpole.fi
// Moderaattorifunktiot
// 17.2.2010

function check_permissions() {
	global $cfg;
	
	// 0 = user, 1 = admin, 2 = super mod, 3 = mod
	$mod['user_class'] = 0;
	$mod['id'] = 0;
	$mod['name'] = '';
	
	if(!empty($_COOKIE['mod'])) {
		$a = explode("|", $_COOKIE['mod']);
		
		$query = mysql_query("SELECT `user_class`, `id`, `name` FROM `admin_users` WHERE `name` = '". mysql_real_escape_string($a[0]) ."' AND `password` = '". mysql_real_escape_string($a[1]) ."' AND `last_ip` = '". mysql_real_escape_string(encrypt_ip(get_ip())) ."' LIMIT 1");
		if(mysql_num_rows($query) != 0) {
			$mod = mysql_fetch_assoc($query);
			$cfg['user_class'] = $mod['user_class'];
			$cfg['mod_id'] = $mod['id'];
			$cfg['mod_name'] = $mod['name'];
			mysql_query("UPDATE `admin_users` SET `last_active` = UNIX_TIMESTAMP() WHERE `id` = '". $mod['id'] ."' LIMIT 1");
		}
		else
			setcookie("mod", "", 1, '/');
	}
	
	return $mod;
}

function write_modlog($action) {
	global $cfg;
	
	if($cfg['user_class'] != 0)
	{
	
		$time = time() - $cfg['modlog_expire'];
		mysql_query("DELETE FROM `modlog` WHERE `time` <= '". $time ."'");
	
		if(is_numeric($action)) {
			$action = mysql_real_escape_string($action);
			$q = mysql_query("INSERT INTO `modlog`(`action`, `mod_id`, `time`) VALUES ('". $action ."', '". $cfg['mod_id'] ."', UNIX_TIMESTAMP())");
			if($q) return true;
			else return false;
		}
		else return false;
	}
	else
		error(T_("Trying to write to modlog as a non-privileged user"));
}

function get_modlog_string($action) {

	if(is_numeric($action)) {
		if($action == 1)
			$return = T_("Deleted a file");
		elseif($action == 2)
			$return = T_("Deleted a post");
		elseif($action == 3)
			$return = T_("Modposted");
			
		return $return;
	}
	else return false;
}

?>
