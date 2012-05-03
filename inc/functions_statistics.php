<?php
// Northpole.fi
// Tilastofunktiot

function update_online_log($uid) {
	global $cfg;
	$ip = encrypt_ip(get_ip());
	
	// Add or update user
	// This also creates the user profile
	if(!empty($_COOKIE['uid']))
	{
		mysql_query("
			INSERT INTO `users`(
				`uid`, `ip`, `last_load`, `last_page`, `online`
			)
			VALUES (
				'". mysql_real_escape_string($uid) ."',
				'". mysql_real_escape_string($ip) ."',
				UNIX_TIMESTAMP(),
				'". mysql_real_escape_string($_SERVER['REQUEST_URI']) ."',
				'1')
			ON DUPLICATE KEY UPDATE
				`last_load` = UNIX_TIMESTAMP(),
				`last_page` = '". mysql_real_escape_string($_SERVER['REQUEST_URI']) ."',
				`online` = '1'
		");
	}
	else {
		// Update online counter to add users without cookies enabled with their IP as a main id.
		// This means that such a value is inserted every time a new user without the cookies yet enters the site,
		// so after their second pageload, if they have cookies then, we should update the user row to have their UID as a main id.
		//`uid` = `ip` maybe? Then in update when we have cookies, update the `uid` with WHERE `uid` = IP
	}
	
	// Delete old users
	$time = time() - $cfg['onlinetime'];
	$deltime = time() - ($cfg['save_guest_info_time'] * 24 * 60 * 60);
	mysql_query("UPDATE `users` SET `online` = '0' WHERE `last_load` < '". mysql_real_escape_string($time) ."' AND `online` != '0' LIMIT 2");
	$q = mysql_query("SELECT `uid` FROM `users` WHERE `last_load` < '". mysql_real_escape_string($deltime) ."'");
	
	while($row = mysql_fetch_assoc($q))
	{
		deleteUser($row['uid']);
	}

}

function get_online_count()
{
	global $cfg;

	$get_cached = mysql_query("SELECT `time`, `content` FROM `cache_other` WHERE `name` = 'online_users' LIMIT 1");
	
	$regen = false;
	if( mysql_num_rows( $get_cached ) == 1 )
	{
		$time = mysql_result( $get_cached, 0, 'time' );
		if( $time > time() - $cfg['onlinetime_cache_ttl'] )
		{
			$count = mysql_result( $get_cached, 0, 'content' );
		}
		else $regen = true;
	}
	else $regen = true;
	
	if( $regen )
	{
		mysql_query("DELETE FROM `cache_other` WHERE `name` = 'online_users' LIMIT 1");
		mysql_query("
			INSERT INTO `cache_other`(`name`, `time`, `content`) VALUES
			(
				'online_users',
				UNIX_TIMESTAMP(),
				(SELECT COUNT(`uid`) FROM `users` WHERE `online` = '1')
			);
		");
		$get = mysql_query("SELECT `content` FROM `cache_other` WHERE `name` = 'online_users' LIMIT 1");
		$count = mysql_result( $get, 0, "content" );
	}
	return $count;
}

function deleteUser($uid)
{	
	$q = mysql_query("DELETE FROM `users` WHERE `uid` = '". $uid ."'");
	if($q) {
		mysql_query("DELETE FROM `hide` WHERE `uid` = '". $uid ."'");
		mysql_query("DELETE FROM `follow` WHERE `uid` = '". $uid ."'");
		mysql_query("DELETE FROM `hide_boards` WHERE `uid` = '". $uid ."'");
		return true;
	}
	else return false;

}

function getServerLoad($server, $snmpdomain)
{
	global $cfg;
	
	$get_cached = mysql_query("SELECT `time`, `content` FROM `cache_other` WHERE `name` = 'load_". mysql_real_escape_string( $server ) ."' LIMIT 1");
	
	$regen = false;
	if( mysql_num_rows( $get_cached ) == 1 )
	{
		$time = mysql_result( $get_cached, 0, 'time' );
		if( $time > time() - $cfg['snmp_cache_ttl'] )
		{
			$load = mysql_result( $get_cached, 0, 'content' );
		}
		else $regen = true;
	}
	else $regen = true;
	
	if( $regen )
	{
		mysql_query("DELETE FROM `cache_other` WHERE `name` = 'load_". mysql_real_escape_string( $server ) ."' LIMIT 1");
		if( $server == 'localhost' )
		{
			$load = sys_getloadavg();
			$load = str_replace( ",", ".", $load[0] );
			$load = number_format( $load, 2 );
		}
		else
		{	
			$load = snmpget( $server, $snmpdomain, "UCD-SNMP-MIB::laLoad.1", 1000000 );
			if(!$load) $load = '<span style="color: red !important; font-weight: bold;">'. T_('Not responding!') .'</span>';
			$load = str_replace( "STRING: ", "", $load );
		}
		mysql_query("INSERT INTO `cache_other`(`name`, `time`, `content`) VALUES ('load_". mysql_real_escape_string( $server ) ."', UNIX_TIMESTAMP(), '". mysql_real_escape_string( $load ) ."')");
	}
	
	return $load;
}

?>
