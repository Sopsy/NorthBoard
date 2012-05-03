<?php
// Northpole.fi
// Messages
// 21.2.2010
require_once("../inc/include.php");

if($cfg['user_class'] >= 1 AND $cfg['user_class'] <= 3) {

	$mod_pages = true;
	$title = T_("Messages") .', '. T_("Board administration"). $cfg['site_title'];
	include($cfg['srvdir'] ."/inc/header.php");
	echo '
		<div id="padded">';

	if(empty($_GET['a'])) {
			
		$q = mysql_query("SELECT * FROM `files` ORDER BY `id` DESC LIMIT 100");
		echo '<h2>'. T_("100 newest files") .'</h2>';
		
		echo '
		<table class="table">
			<tr>
				<th>'. T_("ID") .'</th>
				<th>'. T_("Name") .'</th>
				<th>'. T_("Size") .'</th>
				<th>'. T_("Folder") .'</th>
				<th>'. T_("Remove") .'</th>
			</tr>';
		
		$i = 0;
		while($b = mysql_fetch_assoc($q)) {
			echo '
			<tr>
				<td><a href="'. $cfg['htmldir'] .'/scripts/file-redirect.php?id='. $b['id'] .'">'. $b['id'] .'</a></td>
				<td>'. $b['orig_name'] .'.'. $b['extension'] .'</td>
				<td>'. convert_filesize($b['size']) .'</td>
				<td>'. $b['folder'] .'</td>
				<td><a href="'. $cfg['htmldir'].'/mod/files/delete/'. $b['id'] .'">Poista</a></td>
			</tr>';
			$i++;
		}
		if($i == 0) echo '
			<tr><td colspan="9">'. T_("No files were found") .'</td></tr>';
		
		echo '
		</table>';
	}
	elseif($_GET['a'] == "delete") {
		if(!empty($_GET['b']) AND is_numeric($_GET['b'])) {
			$d = delete_file_single($_GET['b']);
			if($d) info(T_("File deleted"));
			else error(T_("File deletion failed!"));
		}
		else error(T_("Malformed file id!"));
	}
}
else header("Location: ". $cfg['htmldir']);

?>