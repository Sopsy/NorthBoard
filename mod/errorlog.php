<?php
// Northpole.fi
require_once("../inc/include.php");

if($cfg['user_class'] == 1) {

	$mod_pages = true;
	$title = T_("Errorlog") .', '. T_("Board administration"). $cfg['site_title'];
	include($cfg['srvdir'] ."/inc/header.php");
	echo '
		<div id="padded">';

	if(empty($_GET['a'])) {
			
		$q = mysql_query("SELECT * FROM `errorlog` ORDER BY `time` DESC LIMIT 100");
		echo '<h2>'. T_("100 latest errors logged") .'</h2>';
		
		echo '
		<table class="table">
			<tr>
				<th>'. T_("Info") .'</th>
				<th>'. T_("Error data") .'</th>
				<th>'. T_("Time") .'</th>
				<th>'. T_("User IP") .'</th>
			</tr>';
		
		$i = 0;
		while($b = mysql_fetch_assoc($q)) {
			echo '
			<tr>
				<td>'. $b['info'] .'</td>
				<td style="min-width: 70%;"><a id="errorinfolink_'. $b['id'] .'" href="javascript:void(showError(\''. $b['id'] .'\'));">'. T_("Click here to toggle saved data") .'</a><br /><pre id="errorinfo_'. $b['id'] .'" style="display: none;">'. $b['headers'] .'</pre></td>
				<td>'. date(T_("Y/m/d g:i:s A"), $b['time']) .'</td>
				<td>'. $b['ip'] .'</td>
			</tr>';
			$i++;
		}
		if($i == 0) echo '
			<tr><td colspan="9">'. T_("No errors logged") .'</td></tr>';
		
		echo '
		</table>';
	}
}
else header("Location: ". $cfg['htmldir']);

?>