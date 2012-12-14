<?php
require_once("inc/include.php");

$q = mysql_query("SELECT `url` FROM `boards`");
$boards = array();
while($l = mysql_fetch_assoc($q))
	$boards[] = $l['url'];

$board = mysql_real_escape_string($_GET['id']);

if($board == $cfg['overboard_url'])
	$overboard = true;
else
	$overboard = false;

if(empty($board)) $board = 'b';
if(!in_array($board, $boards) AND !$overboard) header("Location: ". $cfg['htmldir']);

if(!$overboard)
{
	$q = mysql_query("SELECT * FROM `boards` WHERE `url` = '". $board ."' LIMIT 1");
	$i = mysql_fetch_assoc($q);
}
else
{
	$i = array(
		'url' => $cfg['overboard_url'],
		'name' => $cfg['overboard_name'],
		'description' => $cfg['overboard_desc'],
		'pages' => $cfg['overboard_pages'],
	);
}

$title = sprintf(T_("Thread list of the board /%s/"), $i['url']) . $cfg['site_title']; // Sivun otsikko
include("inc/header.php"); // Html-headi

	echo common_top($i);
		
		echo '
		<div class="infobar">'. sprintf(T_("Thread list of the board /%s/"), $i['url']) .'.</div>
		<p><a href="'. $cfg['htmldir'] .'/'. $i['url'].'/" class="button">'. T_("Return to the board") .'</a></p>';
		
		if($overboard) echo '
		<div class="infobar">'. T_('Only 150 latest threads will be shown in the thread list of an overboard.') .'</div>';
		
		$boardid = '';
		if($overboard) {
			// Don't show hidden boards in thread list of the overboard
			$hidden_q = mysql_query("SELECT `id` FROM `boards` WHERE `category` = '0'");
			while($hidden = mysql_fetch_assoc($hidden_q)) {
				$boardid .= "`posts`.`board` != '". $hidden['id'] ."' AND ";
			}
			
			foreach( $cfg['user']['hide_boards'] AS $hidden_board )
			{
				$boardid .= "`posts`.`board` != '". $hidden_board ."' AND ";
			}
		}
		else $boardid = "`posts`.`board` = '". mysql_real_escape_string($i['id']) ."' AND ";

		echo '
			<style type="text/css">
				table#list {
					width: 100%;
					text-align: left;
				}
				table#list tr td {
					border: 1px solid #666;
				}
			</style>
		<table id="list">';
				
		$lq = mysql_query("
			SELECT `posts`.`id`, `posts`.`board`, `posts`.`subject`, `posts`.`message`, `posts`.`time`, `boards`.`url`, `post_files`.`fileid`, `files`.`folder`, `files`.`extension`, `files`.`name`, `files`.`thumb_ext`
			FROM `boards`, `posts`
				LEFT JOIN `post_files` ON `posts`.`id` = `post_files`.`postid`
				LEFT JOIN `files` ON `files`.`id` = `post_files`.`fileid`
			WHERE ". $boardid ."`posts`.`board` = `boards`.`id` AND `posts`.`thread` = '0' AND `posts`.`deleted_time` = '0'
			GROUP BY `posts`.`id`
			ORDER BY `posts`.`sticky`, `posts`.`bump_time` DESC
			LIMIT 150 
		");
		
		$b = 0;
		while($l = mysql_fetch_assoc($lq))
		{
			$b++;
						
			if(!empty($l['fileid']))
			{
			
				if( in_array( $l['extension'], $cfg['thumbnail_filetypes'] ) )
				{
					$sauce_end = '/files/'. $l['folder'] .'/thumb/'. $l['name'] .'.'. $l['thumb_ext'];
					$sauce = $cfg['srvdir'] . $sauce_end;
					if(is_file($sauce))
					{
						$sauce = $cfg['htmldir'] . $sauce_end;
					}
					else
					{
						$sauce = getfileicon($l['extension']);
						$sauce = $sauce[1];
					}
				}
				else {
					$sauce = getfileicon($l['extension']);
					$sauce = $sauce[1];
				}
			}
			else $file = false;
			
			
			$countq = mysql_query("
				SELECT 
					(SELECT COUNT(`id`) FROM `posts` WHERE `thread` = '". $l['id'] ."' LIMIT 1) AS `postcount`,
					(SELECT COUNT(*) FROM `hide` WHERE `thread` = '". $l['id'] ."' LIMIT 1) AS `hidecount`,
					(SELECT COUNT(*) FROM `follow` WHERE `thread` = '". $l['id'] ."' LIMIT 1) AS `followcount`
				LIMIT 1");
			$replies = mysql_result($countq, 0, "postcount");
			$hides = mysql_result($countq, 0, "hidecount");
			$follows = mysql_result($countq, 0, "followcount");
			
			echo '
			<tr>
				<td>
					<p>
						'. T_("No.") .' <a href="'. $cfg['htmldir'] .'/'. $l['url'].'/'. $l['id'] .'/">'. $l['id'] .'</a> - <b>'. (!empty($l['subject']) ? stripslashes($l['subject']) : T_("No subject")) .'</b><br />
						'. date(T_("Y/m/d g:i:s A"), $l['time']) .'
					</p>
				</td>
				<td>
					<p>
						<a href="'. $cfg['htmldir'] .'/'. $l['url'].'/'. $l['id'] .'/">';

					if($l['fileid']) echo '<img src="'. $sauce .'" class="mini_img" alt="'. T_("Image") .'" />';
					else echo T_("No image");
					echo '</a>
					</p>
				</td>
				<td>
					<blockquote>
						'. truncate(stripslashes($l['message']), false, 512, false, false) .'
					</blockquote>
				</td>
				<td>
					<p>'. $replies .'&nbsp;'. ($replies == 1 ? T_('reply') : T_('replies')) .'</p>
					<p>'. $hides .'&nbsp;'. ($hides == 1 ? T_('hide') : T_('hides')) .'</p>
					<p>'. $follows .'&nbsp;'. ($follows == 1 ? T_('follower') : T_('followers')) .'</p>
				</td>
			</tr>';
			
		}
		echo '
		</table>
		<div class="infobar bottom_border">'. sprintf(T_("%s threads shown"), $b) .'</div>';

		echo '<div class="boardnav">		[ <a href="'. $cfg['htmldir'] .'/personal/followed/">'. T_("Followed") .'</a> |
		<a href="'. $cfg['htmldir'] .'/personal/mythreads/">'. T_("My threads") .'</a> |
		<a href="'. $cfg['htmldir'] .'/personal/repliedthreads/">'. T_("Replied") .'</a> ]
		<div style="float: right;">
			[ <a href="'. $cfg['htmldir'] .'/">'. T_('Front page') .'</a> |
			<a href="'. $cfg['htmldir'] .'/search/">'. T_('Message search') .'</a> |
			<a href="'. $cfg['htmldir'] .'/settings/">'. T_("Site personalization") .'</a> ]
		</div>
		<br />'. boardnav() .'</div>';
		?>
	</div>

<?php
include("inc/footer.php"); // Html-footeri
?>
