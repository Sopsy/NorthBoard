<?php
// Northpole.fi
// 30.7.2011
$nostatsupdate = true;
require_once("../../inc/include.php");

if(!empty($_GET['id']) AND is_numeric($_GET['id'])) {
	// Expanding post text
	
	$id = mysql_real_escape_string($_GET['id']);
	$bq = mysql_query("SELECT `message` FROM `posts` WHERE `id` = '". $id ."' LIMIT 1");
	
	if(mysql_num_rows($bq) != 0) {
		$message = mysql_result($bq, 0, 'message');
	}
	else die(sprintf(T_("Message %s does not exist"), $id));
	
	$message = "<p>". nl2br_pre($message) ."</p>";
	if($cfg['https'])
		$message = str_replace("http://". $cfg['htmldir_plain'], "https://". $cfg['htmldir_plain'], $message);
		
	echo $message;
}
else die(T_("Malformed message ID"));

?>
