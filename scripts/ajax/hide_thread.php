<?php
// Northpole.fi
// 12.4.2011
$nostatsupdate = true;
require_once("../../inc/include.php");

if(empty($_GET['id']) OR !is_numeric($_GET['id']) AND $_GET['id'] != "all")
	die( T_("Thread ID is missing or is malformed!") );
	
$hide = mysql_real_escape_string($_GET['id']);

if($_GET['do'] == "hide" AND $hide != "all")
{
	if(in_array($_GET['id'], $cfg['user']['hide']))
		die( T_("This thread is already hidden!") );
	
	if(!isThread($_GET['id']))
		die( T_("The thread you tried to hide does not exist!") );
	

	$q = mysql_query("INSERT INTO `hide`(`uid`, `time`, `thread`) VALUES ('". $cfg['user']['uid'] ."', UNIX_TIMESTAMP(), '". $hide ."')");
	if($q)
		echo "OK";
	else
		die(T_("Error in SQL query!"));
}
elseif($_GET['do'] == "restore")
{

	if($hide != "all")
		$thread = " AND `thread` = '". $hide ."'";
	else
		$thread = '';

	$q = mysql_query("DELETE FROM `hide` WHERE `uid` = '". $cfg['user']['uid'] ."'". $thread);
	if($q)
		echo "OK";
	else
		die(T_("Error in SQL query!"));

}

?>
