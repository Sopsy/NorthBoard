<?php
// Northpole.fi
// Stats
// 11.8.2011
require_once("../inc/include.php");

if($cfg['user_class'] >= 1 AND $cfg['user_class'] <= 3) {

	$mod_pages = true;
	$title = T_("Statistics") .', '. T_("Board administration"). $cfg['site_title'];
	include($cfg['srvdir'] ."/inc/header.php");
	echo '
		<div id="padded">';

	if($_GET['a'] == 'postcount')
	{
		$q = mysql_query("SELECT * FROM `files` ORDER BY `id` DESC LIMIT 100");
		echo '<h2>'. T_("Post count by board") .'</h2>';
	
		$countq = mysql_query("
			SELECT SUM( `posts`.`time` >= UNIX_TIMESTAMP() - 3600 ) AS `count_hour`, SUM( `posts`.`time` >= UNIX_TIMESTAMP() - 86400 ) AS `count_day`, COUNT(`posts`.`id`) AS `count_all`, `boards`.`url`, `boards`.`name`
			FROM `posts`, `boards`
			WHERE `boards`.`id` = `posts`.`board`
			GROUP BY `posts`.`board`
			ORDER BY `count_day` DESC
		");

		echo '
		<table class="table">
			<tr>
				<th>'. T_("Board") .'</th>
				<th>'. T_("Posts last hour") .'</th>
				<th>'. T_("Posts last day") .'</th>
				<th>'. T_("Total posts") .'</th>
			</tr>';
		
		$totals = array("hour" => 0, "day" => 0, "all" => 0);
		while($b = mysql_fetch_assoc($countq))
		{
			$totals['hour'] += $b['count_hour'];
			$totals['day'] += $b['count_day'];
			$totals['all'] += $b['count_all'];
			echo '
			<tr>
				<td><strong>'. $b['name'] .'</strong></td>
				<td>'. $b['count_hour'] .'</td>
				<td>'. $b['count_day'] .'</td>
				<td>'. $b['count_all'] .'</td>
			</tr>';
		}
		echo '
			<tr>
				<td><strong>'. T_('Total') .'</strong></td>
				<td>'. $totals['hour'] .'</td>
				<td>'. $totals['day'] .'</td>
				<td>'. $totals['all'] .'</td>
			</tr>';
		
		echo '
		</table>';
	}
}
else header("Location: ". $cfg['htmldir']);

?>
