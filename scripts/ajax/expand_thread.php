<?php
// Northpole.fi
// 30.7.2011
$nostatsupdate = true;
require_once("../../inc/include.php");

if(!empty($_GET['id']) AND is_numeric($_GET['id']))
{
	$thread = mysql_real_escape_string($_GET['id']);

	if(empty($_GET['do']) OR $_GET['do'] == 'expand')
		$max = $cfg['expand_limit'];
	else
		$max = $cfg['rpt'];

	// Get the count of posts in the thread and calculate limits
	$countq = mysql_query("SELECT COUNT(`id`) AS `count` FROM `posts` WHERE `thread` = '". $thread ."' AND `deleted_time` = '0'");
	$count = mysql_result($countq, 0, "count");
		
	if($count > $max)
	{
		$start = $count - $max;
	}
	else
	{
		$max = $count;
		$start = 0;
	}
	
	if($start < 0)
		$start = 0;
	if($max < 0)
		$max = 0;
	
	$bq = mysql_query("SELECT `board` FROM `posts` WHERE `thread` = '". $thread ."' LIMIT 1");
	if(mysql_num_rows($bq) != 0)
	{
		$boardid = mysql_result($bq, 0, "board");
		$boardq = mysql_query("SELECT * FROM `boards` WHERE `id` = '". $boardid ."' LIMIT 1");
		$board = mysql_fetch_assoc($boardq);
		
		$aq = mysql_query("SELECT * FROM `posts` WHERE `thread` = '". $thread ."' AND `deleted_time` = '0' ORDER BY `id` ASC LIMIT ". $start .", ". $max);
		
		if( mysql_num_rows( $aq ) != 0 )
		{
			if($_GET['do'] == 'expand')
			{
				echo '<p class="expand_images"><a href="javascript:void(expand_images(\''. $thread .'\'));">'. T_("Expand all images") .'</a></p>';
		
				if($start != 0)
				{
					echo '<p class="omitted">'. sprintf( T_("Only latest %s messages are shown."), $cfg['expand_limit'] ) .' '. sprintf( T_("%s messages omitted"), $start ) .'.</p>';
				}
			}
			$tpagesq = mysql_query("SELECT COUNT(`id`) AS 'count' FROM `posts` WHERE `deleted_time` = '0' AND `thread` = '". $thread ."'");

			$amount = $max;
			$tmsg_page = array();
			for($i = 0; $i != $amount; $i++) {
				$tmp = ceil((mysql_result($tpagesq, 0, "count") - $i) / $cfg['ppp']);
				if($tmp < 1) $tmp = 1;
				$tmsg_page[] = $tmp;
			}
			$tmsg_page = array_reverse($tmsg_page);
			$i = 0;
			
			while($reply = mysql_fetch_assoc($aq))
			{
				$reply['on_page'] = $tmsg_page[$i];
				$i++;
				print_post($reply, false, $board);
			}
		}
		else
		{
			echo '<p class="omitted">'. T_("No omitted posts to display") .'</p>';
		}
	}
	else
	{
		echo '<p class="omitted">'. T_("No omitted posts to display") .'</p>';
	}
}
?>
