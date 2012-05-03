<?php
require_once("inc/include.php");


$title = T_("Message search") .' - '. $cfg['fp_title']; // Site title
include("inc/header.php"); // Html-header

echo '
		<div id="padded">
			<h2>'. T_("Message search") .'</h2>
			
			<form action="'. $cfg['htmldir'] .'/search/" method="post">
				<fieldset>
				
					<label for="term">'. T_("Search term") .'</label>
					<input class="wide" type="text" name="term" id="term"'. (!empty($_POST['term']) ? ' value="'. $_POST['term'] .'"' : '') .' />
					
					<input type="submit" value="'. T_("Search") .'" />
				
				</fieldset>
			</form>';
			
			
if( !empty( $_POST['term'] ) AND strlen($_POST['term']) >= 4 )
{

	$maxcount = 10;

	$term = mysql_real_escape_string($_POST['term']);
	$cq = mysql_query("SELECT COUNT(`id`) AS `count` FROM `posts` WHERE MATCH(`subject`, `message`) AGAINST ('\"". $term ."\"' IN BOOLEAN MODE) AND `deleted_time` = '0' LIMIT 1");
	
	$count = mysql_result($cq, 0, "count");
	if($count >= 1)
	{
	
		echo '
			<p><strong>'. sprintf( T_("%s %s found"), $count, ($count == 1 ? T_("result") : T_("results"))) .'</strong></p>';
	
		if($count > $maxcount)
			echo '
			<p><strong>'. sprintf( T_("However, only the newest %s posts will be shown."), $maxcount) .'</strong></p>';
	
		echo '
			<hr class="line" />';
	
		$q = mysql_query("SELECT * FROM `posts` WHERE  MATCH(`subject`, `message`) AGAINST ('\"". $term ."\"' IN BOOLEAN MODE) AND `deleted_time` = '0' ORDER BY `id` DESC LIMIT ". $maxcount);

		while($res = mysql_fetch_assoc($q))
		{
			$board = mysql_query("SELECT * FROM `boards` WHERE `id` = '". $res['board'] ."' LIMIT 1");
			$board = mysql_fetch_assoc($board);
			
			print_post($res, 'thread', $board);
			echo '
			<hr class="line" />';
		}
	}
	else
		echo '<p>'. T_("No matching posts found.") .'</p>';
}
else
	echo '<p>'. sprintf( T_("The search term has to be over or exactly %d characters long."), 4 ) .'</p>';

echo '
		</div>
	</div>
';

include("inc/footer.php"); // Html-footer
?>
