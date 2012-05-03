<?php
// Northpole.fi
// 12.4.2011
$nostatsupdate = true;
require_once("../../inc/include.php");

if(empty($_GET['id']) OR !is_numeric($_GET['id']))
	die( T_("Thread ID is missing or is malformed!") );
	
$follow = mysql_real_escape_string($_GET['id']);
	
if($_GET['do'] == 'add')
{
	if(in_array($_GET['id'], $cfg['user']['follow']))
		die( T_("This thread is already being followed!") );
		
	if(!isThread($_GET['id']))
		die( T_("The thread you tried to follow does not exist!") );
	
	$q = mysql_query("INSERT INTO `follow`(`uid`, `thread`) VALUES ('". $cfg['user']['uid'] ."', '". $follow ."')");
	if($q)
		echo "OK";
	else
		die(T_("Error in SQL query!"));
}
elseif($_GET['do'] == 'remove')
{
	$q = mysql_query("DELETE FROM `follow` WHERE `uid` = '". $cfg['user']['uid'] ."' AND `thread` = '". $follow ."'");
	if($q)
		echo "OK";
	else
		die(T_("Error in SQL query!"));

}

?>
