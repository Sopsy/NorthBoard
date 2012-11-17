<?php
// Northpole.fi
// Ohjaus oikealle sivulle ref-linkistä
// 16.2.2010

require_once("../inc/include.php");

if(!empty($_GET['id'])) {
	$id = mysql_real_escape_string($_GET['id']);
	if(!is_numeric($id)) {
		error(T_("Malformed message ID!"));
	}
}
else die();

$query = mysql_query("SELECT `thread`, `board` FROM `posts` WHERE `id` = '". $id ."' LIMIT 1");
if(mysql_num_rows($query) != 0) {
	$thread = mysql_result($query, 0, "thread");
	if($thread == 0) $thread = $id;
	$boardid = mysql_result($query, 0, "board");
	$query = mysql_query("SELECT `url` FROM `boards` WHERE `id` = '". $boardid ."' LIMIT 1");
	
	$sq = mysql_query("SELECT COUNT(`id`) AS `count` FROM `posts` WHERE `id` < '". $id ."' AND `thread` = '". $thread ."' LIMIT 1");
	$msgc_before = mysql_result($sq, 0, "count");
	$page = ceil($msgc_before / ($cfg['ppp'] - 1));
	
	if(mysql_num_rows($query) != 0) {
		$board = mysql_result($query, 0, "url");
		header("Location: ". $cfg['htmldir'] ."/". $board ."/". $thread ."-". $page ."/#hl_". $id);
	}
	else error(T_("The board where the requested message is does not exist!"));
}
else error(sprintf(T_("The requested message (%s) does not exist!"), $id), true, false, false);

?>