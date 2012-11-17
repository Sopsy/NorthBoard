<?php
// Northpole.fi
// 15.2.2010

$nostatsupdate = true;
require_once("../../inc/include.php");

if(!empty($_GET['id']) AND is_numeric($_GET['id'])) {
	// Mouseover message preview
	$id = mysql_real_escape_string($_GET['id']);

	$bq = mysql_query("SELECT `posts`.*, `boards`.`name` AS `boardname`, `boards`.`url` FROM `posts`, `boards` WHERE `posts`.`deleted_time` = '0' AND `posts`.`id` = '". $id ."' AND `boards`.`id` = `posts`.`board` LIMIT 1");
	if(mysql_num_rows($bq) != 0) {
		$post = mysql_fetch_assoc($bq);
		$post['on_page'] = '1';
		print_post($post, "thread", 'msgprev');
	}
	else die(sprintf(T_("Message %s does not exist"), $id));
}
elseif($_GET['id'] == "embedhelp") {
	// Mouseover help for embedding videos
	echo '<h4>'. T_("Copy this part of the address to the embed -field.") .'</h4>
<p>';

	$q = mysql_query("SELECT `name`, `help` FROM `embed_sources` ORDER BY `name` DESC");
	while($row = mysql_fetch_assoc($q)) {

		if(empty($row['name']) OR empty($row['help'])) continue;
		
		echo '
	<b>'. $row['name'] .':</b> '. $row['help'] .'<br />';
	}
	
	echo '
</p>';
}
else die(T_("Malformed message ID"));

?>
