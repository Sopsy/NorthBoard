<?php
include("inc/include.php");

$q = mysql_query("SELECT `url` FROM `boards`");
$boards = array();
while($l = mysql_fetch_assoc($q))
	$boards[] = $l['url'];

$board = mysql_real_escape_string($_GET['board']);
if(empty($board)) $board = 's';
if( !in_array($board, $boards) )
{
	header("Location: ". $cfg['htmldir']);
	die();
}

$thread = mysql_real_escape_string($_GET['id']);
if( empty($thread) )
{
	header("Location: ". $cfg['htmldir']);
	die();
}

$q = mysql_query("SELECT * FROM `boards` WHERE `url` = '". $board ."' LIMIT 1");
$i = mysql_fetch_assoc($q);

if($i['worksafe'] == 0 AND $cfg['user']['sfw'] == '1') {
	info(T_('The page you are trying to open contains material not appropriate for minors and according to your settings, you don\'t want to see such content.') .'</p><p>'. sprintf( T_('If you still want to continue to this page, you can disable the hiding of NSFW-content by %sclicking here%s.'), '<a href="'. $cfg['htmldir'] .'/scripts/sfw_redirect.php?sfw=false&goto='. urlencode($_SERVER['REQUEST_URI']) .'">', '</a>'), true, false, T_("Access prevented!"));
}

$aq = mysql_query("SELECT `posts`.`board`, `posts`.`subject`, `posts`.`deleted_time`, `boards`.`url` FROM `posts`, `boards` WHERE `boards`.`id` = `posts`.`board` AND `posts`.`id` = '". $thread ."' AND `posts`.`thread` = '0' LIMIT 1");
$a = mysql_fetch_assoc($aq);

if( mysql_num_rows($aq) == 0 OR (!empty($a['deleted_time']) AND $a['deleted_time'] != 0) )
{
	// When the thread recycle bin exists, we should change to error 410 if we know that there was a thread.
	if(!empty($a['deleted_time']) AND $a['deleted_time'] != 0)
	{
		header('HTTP/1.0 410 Gone');
		header("Location: ". $cfg['htmldir'] ."/410/");
	}
	else
	{
		header('HTTP/1.0 404 Not Found');
		header("Location: ". $cfg['htmldir'] ."/404/");
	}
	die();
}
elseif( $a['board'] != $i['id'] )
{
	header('HTTP/1.0 301 Moved Permanently');
	header('Location: '. $cfg['htmldir'] .'/'. $a['url'] .'/'. $thread .'/');
}

$pagesq = mysql_query("SELECT COUNT(`id`) AS 'count' FROM `posts` WHERE `thread` = '". $thread ."'");
$pages = ceil(mysql_result($pagesq, 0, "count") / $cfg['ppp']);

// Page
if(empty($_GET['page'])) $_GET['page'] = '-1';
$_GET['page'] = substr($_GET['page'], 1);
$page = $_GET['page'];

if(!is_numeric($page) OR $page < 1 OR $page > $pages AND $pages != 0 OR $_SERVER['REQUEST_URI'] == "/". $i['url'] ."/". $thread ."-1/") {
	header("Location: ". $cfg['htmldir'] ."/". $i['url'] ."/". $thread ."/");
	die();
}

if(!empty($a['subject'])) $title = addslashes($a['subject']) ." - ";
else $title = "";

$title = $title .'/'. $i['url'] .'/'. $thread .'-'. $page .'/ - '. $i['name'] . $cfg['site_title']; // page title
include("inc/header.php"); // Html-headi

	echo common_top($i);
	
		echo '
		<p><span class="button_wrap">[</span><a href="'. $cfg['htmldir'] .'/'. $board .'/" class="button">'. T_("Return to the board") .'</a><span class="button_wrap">]</span></p>';
		
		$lq = mysql_query("SELECT `posts`.*, `users`.`hide_region` FROM `posts` LEFT JOIN `users` USING (uid) WHERE `posts`.`deleted_time` = '0' AND `posts`.`id` = '". $thread ."' LIMIT 1");
		$l = mysql_fetch_assoc($lq);
		$l['pages'] = $pages;

		if($l['pages'] < 1) $l['pages'] = 1;
		if($page > $l['pages']) $page = $l['pages'];
		$l['limit_start'] = ($page-1) * $cfg['ppp'];
		$l['limit_end'] = $cfg['ppp'];
		$l['page'] = $page;
				
		if($l['locked'] == 1) {
		echo '
		<div class="infobar">'. T_("This thread is locked and you cannot reply to it.") .'</div>';
		}
		
		if(in_array($thread, $cfg['user']['hide']))
		{
		echo '
		<div class="infobar">'. T_("You have hidden this thread.") .'</div>';
		}
		
		if(!$l['locked'] OR $cfg['user_class'] >= 1 AND $cfg['user_class'] <= 3) {
			if($i['worksafe'] == 1) echo '
		<div class="infobar">'. T_("Please remember that this board is worksafe.") .'</div>';
			echo '
		<div class="infobar">'. sprintf(T_("Your message will be sent as a reply to the thread %s"), $thread) .'</div>',
			post_form($i, $thread, 0);
		}
		
		if($cfg['user']['hide_ads'] == 0) {
			if($i['category'] != "0")
				include("ads/ad_top.php");
		}
			
		echo '
		<form id="deleteform" method="post" action="'. $cfg['htmldir'] .'/delete/">
		<input type="hidden" name="open_thread" value="'. $thread .'" />
		<input type="hidden" name="open_board" value="'. $board .'" />
		<input type="hidden" name="url" value="'. urlencode($_SERVER['REQUEST_URI']) .'" />
		<fieldset>';
		
		print_thread($l, "thread", $i);
		
		echo '
		
		<div id="bottom">
			<div id="bottomleft">
				<div id="pages">';
					if($page != 1) echo '
					<a id="prev" class="button" href="'. $cfg['htmldir'] .'/'. $board .'/'. $thread .'-'. ($page-1) .'/">'. T_("Previous") .'</a>';
					for($i = 1; $i <= $l['pages']; $i++) {
						if($page == $i) echo '
					['. $i .']';
						else echo '
					[<a href="'. $cfg['htmldir'] .'/'. $board .'/'. $thread .'-'. $i .'/">'. $i .'</a>]';

					}
				if($page < $l['pages']) echo '
					<a id="next" class="button" href="'. $cfg['htmldir'] .'/'. $board .'/'. $thread .'-'. ($page+1) .'/">'. T_("Next") .'</a>';
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
include("inc/footer.php"); // Html-footeri
?>
