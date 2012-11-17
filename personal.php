<?php
require_once("inc/include.php");

if(substr($_SERVER['REQUEST_URI'], -1) != "/") {
	header("Location: ". $_SERVER['REQUEST_URI'] ."/");
	die();
}

// Page to be opened
if( empty( $_GET['page'] ) ) $_GET['page'] = "-1";
if( !empty( $_GET['page'] ) ) $_GET['page'] = substr( $_GET['page'], 1 );
$page = $_GET['page'];
	
if( !is_numeric($page) OR $page < 1 OR substr( $_SERVER['REQUEST_URI'], -3)  == "-1/" )
{
	header( "Location: ". $cfg['htmldir'] . substr( $_SERVER['REQUEST_URI'], -3) .'/' );
	die();
}

$threads = false;
if( $_GET['do'] == 'followed' )
{
	foreach( $cfg['user']['follow'] AS $threadid )
	{
		if( $threads ) $threads .= " OR ";
		$threads .= "`id` = '". $threadid ."'";
	}
	
	// Let's cheat a little...
	$i['followed'] = true;
	
	$title = T_("Followed threads"); // Site title
	$i['url'] = 'personal/followed';
	$i['name'] = T_("Followed threads");
	$i['description'] = T_("The threads you are following are shown on this page.");

}
elseif( $_GET['do'] == 'mythreads' OR $_GET['do'] == 'repliedthreads' )
{
	$threads = false;
	
	if( $_GET['do'] == 'mythreads' )
	{
		$q = mysql_query("SELECT `id` FROM `posts` WHERE `deleted_time` = '0' AND `uid` = '". $cfg['user']['uid'] ."' AND `thread` = '0' ORDER BY `id` DESC");
		$title = T_("My threads");
		$i['url'] = 'personal/mythreads';
		$i['name'] = T_("My threads");
		$i['description'] = T_("The threads you have made are shown on this page.");
	}	
	elseif( $_GET['do'] == 'repliedthreads' )
	{
		$q = mysql_query("SELECT `thread` AS `id` FROM `posts` WHERE `deleted_time` = '0' AND `uid` = '". $cfg['user']['uid'] ."' AND `thread` != '0' GROUP BY `thread` ORDER BY `id` DESC");
		$title = T_("Replied threads");
		$i['url'] = 'personal/repliedthreads';
		$i['name'] = T_("Replied threads");
		$i['description'] = T_("The threads you have replied to are shown on this page.");
	}	
	
	if( mysql_num_rows( $q ) != 0 )
	{
		while( $thread = mysql_fetch_assoc( $q ) )
		{
			if($threads) $threads .= " OR ";
			$threads .= "`id` = '". $thread['id'] ."'";
		}
	}
}
else
	error( T_('Unknown function!') );


$title .= ' - '. $cfg['site_title']; // Site title
include("inc/header.php"); // Html-head

	echo common_top($i);
		
		if(!$threads) echo '
		<p>'. T_("You have no threads here.") .'</p>';
		else
		{
			echo '
			<form id="deleteform" method="post" action="'. $cfg['htmldir'] .'/delete/?url='. urlencode($_SERVER['REQUEST_URI']) .'">
			<fieldset>';
		
			$pagesq = mysql_query("SELECT COUNT(`id`) AS 'count' FROM `posts` WHERE ". $threads ."");

			$pages = ceil(mysql_result($pagesq, 0, "count") / $cfg['tpp']);
			if($pages < 1) $pages = 1;
			$a = ($page-1) * $cfg['tpp'];	
			$b = $cfg['tpp'];

			$i['followed_threads'] = true;
		
			$threads = mysql_query("SELECT * FROM `posts` WHERE `deleted_time` = '0' AND ". $threads ." ORDER BY `sticky`, `bump_time` DESC LIMIT ". $a .", ". $b);
			while($thread = mysql_fetch_assoc($threads)) {
				print_thread($thread, "board", $i);
			}
			echo '
		
			<div id="bottomnav">
				<div id="bottomleft">
					<p id="pages">';
						if($page != 1) echo '
						<a id="prev" class="button" href="'. $cfg['htmldir'] .'/'. $i['url'] .'-'. ($page-1) .'/">'. T_("Previous") .'</a>';
						for($a = 1; $a <= $pages; $a++) {
							if($page == $a) echo '
						['. $a .']';
							else echo '
						[<a href="'. $cfg['htmldir'] .'/'. $i['url'] . ($a != 1 ? '-'. $a : '') .'/">'. $a .'</a>]';

						}
					if($page < $pages) echo '
						<a id="next" class="button" href="'. $cfg['htmldir'] .'/'. $i['url'] .'-'. ($page+1) .'/">'. T_("Next") .'</a>';
					echo '
					</p>
					<p id="boards">
						', boardnav(), '
					</p>
				</div>
				', buttons_bottom(), '
			</div>
			</fieldset>
			</form>';
		}
		?>
	</div>

<?php
include("inc/footer.php"); // Html-footeri
?>
