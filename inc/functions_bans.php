<?php
// Northpole.fi
// Bannifunktiot
// 15.2.2010

// Bannien tarkistusta
function check_bans() {
	global $cfg;

	$ban = false;
	$sessionid = $cfg['user']['uid'];
	$ip = mysql_real_escape_string(encrypt_ip(get_ip()));
	
	$get = mysql_query("SELECT * FROM `bans` WHERE (`ip` = '". $ip ."' OR `uid` = '". $sessionid ."') AND `is_old` = '0' ORDER BY `start_time` DESC LIMIT 1");
	if(mysql_num_rows($get) != 0) {
		$ban = mysql_fetch_assoc($get);
	}

	if(!empty($ban) AND $ban['is_old'] == 0) {
		if(strtolower(substr($_SERVER['PHP_SELF'], -10)) != "banned.php") {
			if($ban['can_read'] == 0 OR ($ban['start_time'] + $ban['length']) <= time() AND $ban['lenght'] != '0') {
				header("Location: ". $cfg['htmldir']."/banned/");
				die();
			}
		}
	}
		
	if(!empty($ban) AND $ban['is_old'] == 0) $banned = true;
	else $banned = false;
	
	return $banned;
}
?>