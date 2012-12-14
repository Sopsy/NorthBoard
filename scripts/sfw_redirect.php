<?php
// Swf-toggle-redirect for users who don't have javascript enabled
// 27.7.2011

require_once("../inc/include.php");
if(!empty($_GET['sfw'])) {
	if($_GET['sfw'] == 'false')
		$bool = '0';
	else
		$bool = '1';
	
	if($bool == '0' OR $bool == '1') {
		$q = update_user($cfg['user']['uid'], 'sfw', $bool);
		if(!$q)
			die(T_("Error in SQL query!"));
			
	}
}
header("Location: ". $cfg['htmldir'] . (!empty($_GET['goto']) ? urldecode($_GET['goto']) : '' ) );

?>
