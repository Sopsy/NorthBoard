<?php
// Make sure that there is a slash after the url
if(substr($_SERVER['REQUEST_URI'], -1) != "/") {
	header("Location: ". $_SERVER['REQUEST_URI'] ."/");
	die();
}

require_once("inc/include.php");

// Check that there is an url for the board given
if(!empty($_GET['id'])) $url = mysql_real_escape_string($_GET['id']);
else error(T_("Board url is missing!"));

// Are we on an overboard?
$overboard = false;
if( $cfg['use_overboard'] )
{
	if( $cfg['overboard_url'] == $url )
	{
		$overboard = true;
	}
}

// Get the board information
if(!$overboard)
{
	$q = mysql_query("SELECT * FROM `boards` WHERE `url` = '". $url ."' LIMIT 1");
	if(mysql_num_rows($q) != 1) {
		// Requested board does not exist --> 404
		header('Location: '. $cfg['htmldir'] .'/404/');
		die();
	}
	$board = mysql_fetch_assoc($q);

	if($board['worksafe'] == 0 AND $cfg['user']['sfw'] == '1') {
		info(T_('The page you are trying to open contains material not appropriate for minors and according to your settings, you don\'t want to see such content.') .'</p><p>'. sprintf( T_('If you still want to continue to this page, you can disable the hiding of NSFW-content by %sclicking here%s.'), '<a href="'. $cfg['htmldir'] .'/scripts/sfw_redirect.php?sfw=false&goto='. urlencode($_SERVER['REQUEST_URI']) .'">', '</a>'), true, false, T_("Access prevented!"));
	}
}
else
{
	$board = array(
		'url' => $cfg['overboard_url'],
		'name' => $cfg['overboard_name'],
		'description' => $cfg['overboard_desc'],
		'pages' => $cfg['overboard_pages'],
		'namefield' => $cfg['overboard_namefield'],
		'ad_category' => 1,
		'worksafe' => $cfg['user']['sfw']
	);
}

//Avattava sivu
if(empty($_GET['page'])) $_GET['page'] = "-1";
if(!empty($_GET['page'])) $_GET['page'] = substr($_GET['page'], 1);
$page = $_GET['page'];

if(!is_numeric($page) OR $page < 1 OR $page > $board['pages'] OR $_SERVER['REQUEST_URI'] == "/". $board['url'] ."-1/") {
	header("Location: ". $cfg['htmldir'] ."/". $board['url'] ."/");
	die();
}

// Poistetaan sivujen yli menevät langat
if(!$overboard) {
	$start = ($board['pages'] * $cfg['tpp'] + 1);
	$tsearch = mysql_query("SELECT `id` FROM `posts` WHERE `deleted_time` = '0' AND `thread` = '0' AND `board` = '". $board['id'] ."' ORDER BY `sticky`, `bump_time` DESC LIMIT ". $start .", 2");
	while($dthread = mysql_fetch_assoc($tsearch)) {
		delete_post($dthread['id'], '', false, true);
	}
}

purgePostBin();

$title = '/'. $board['url'] .'/ - '. $board['name'] . $cfg['site_title']; // Sivun otsikko
include("inc/header.php"); // Html-head

	echo common_top($board);
		
		if(!empty($board['worksafe']) AND $board['worksafe'] == 1 AND !$overboard AND $board['id'] != '28') echo '
		<div class="infobar">'. T_("Please remember that this board is worksafe.") .'</div>';

		echo post_form($board, 0, $overboard);
			
		echo '
		<form id="deleteform" method="post" action="'. $cfg['htmldir'] .'/delete/">
		<input type="hidden" name="url" value="'. urlencode($_SERVER['REQUEST_URI']) .'" />
		<input type="hidden" name="open_board" value="'. $board['url'] .'" />
		<fieldset>';
		
		if($overboard)
		{
			$boardid = '';
						
			if($cfg['user']['sfw']) $sfw = " OR `worksafe` = '0'";
			else $sfw = '';
		
			$hidden_q = mysql_query("SELECT `id` FROM `boards` WHERE `category` = '0'". $sfw);
			while($hidden = mysql_fetch_assoc($hidden_q)) {
				$boardid .= "`posts`.`board` != '". $hidden['id'] ."' AND ";
			}
			
			foreach( $cfg['user']['hide_boards'] AS $hidden_board )
			{
				$boardid .= "`posts`.`board` != '". $hidden_board ."' AND ";
			}
		}
		else $boardid = "`posts`.`board` = '". mysql_real_escape_string($board['id']) ."' AND ";

		$hidden = '';
		foreach($cfg['user']['hide'] AS $thread) {
			if(is_numeric($thread)) {
				$hidden .= " AND `posts`.`id` != '". mysql_real_escape_string($thread) ."'";
			}
		}
	
		// Count pages
		$pagesq = mysql_query("SELECT COUNT(`id`) AS 'count' FROM `posts` WHERE ". $boardid ."`thread` = '0' AND `deleted_time` = '0'". $hidden);
		$pages = ceil(mysql_result($pagesq, 0, "count") / $cfg['tpp']);
		if($pages < 1) $pages = 1;
		$a = ($page-1) * $cfg['tpp'];	
		$b = $cfg['tpp'];
		if($pages > $board['pages']) $pages = $board['pages'];
		
		// Show threads
		if( !$overboard )
			$thread_order = "ORDER BY `posts`.`sticky`, `posts`.`bump_time` DESC";
		else
			$thread_order = "ORDER BY `posts`.`bump_time` DESC";
		
		$threads = mysql_query("SELECT `posts`.*, `users`.`hide_region` FROM `posts` LEFT JOIN `users` USING (uid) WHERE `posts`.`deleted_time` = '0' AND ". $boardid ."`posts`.`thread` = '0'". $hidden ." ". $thread_order ." LIMIT ". $a .", ". $b);
		while($thread = mysql_fetch_assoc($threads)) {
			print_thread($thread, "board", $board, $overboard);
		}
			
		echo '
		<div id="bottomnav">
			<div id="bottomleft">
				<div id="pages">';
					if($page != 1) echo '
					<a id="prev" class="button" href="'. $cfg['htmldir'] .'/'. $board['url'] . (($page-1) != 1 ? '-'. ($page-1) : '') .'/">'. T_("Previous") .'</a>';
					for($i = 1; $i <= $pages; $i++) {
						if($page == $i) echo '
					['. $i .']';
						else echo '
					[<a href="'. $cfg['htmldir'] .'/'. $board['url'] . ($i != 1 ? '-'. $i : '') .'/">'. $i .'</a>]';

					}
				if($page < $pages) echo '
					<a id="next" class="button" href="'. $cfg['htmldir'] .'/'. $board['url'] .'-'. ($page+1) .'/">'. T_("Next") .'</a>';
				echo '
				</div>
				<div class="boardnav">
					', boardnav(), '
				</div>
			</div>
			', buttons_bottom(), '
		</div>
		</fieldset>
		</form>';
		?>
	</div>

<?php
include("inc/footer.php"); // Html-footer
?>
