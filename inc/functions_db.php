<?php
// Northpole.fi
// Tietokanta
// 6.1.2010

function dbconnect($onlydb = false)
{
	global $cfg;

	if($onlydb)
		include("config.php");

	$dbconnection = false;
	$link = @mysql_pconnect($cfg['dbhost'], $cfg['dbuser'], $cfg['dbpass']);
	if (!$link)
	{
		if( !$onlydb )
			die('<h1>Database error!</h1><h3>Database is down (1)! Dispatching repair droids...</h3>');
		else
			die("Database error!");
	}
	else {
		$db_select = mysql_select_db($cfg['dbname']);
		if(!$db_select)
		{
			if( !$onlydb )
				die('<h1>Database error!</h1><h3>Database is down (2)! Dispatching repair droids...</h3>');
			else
				die("Database error!");
		}
		else
			$dbconnection = true; // Connected successfully
	}

	if($dbconnection) {
		mysql_query("SET NAMES 'utf8'");
		return true;
	}
	else
		return false;

}

?>
