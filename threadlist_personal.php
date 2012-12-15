<?php
require_once("inc/include.php");

$do = mysql_real_escape_string($_GET['do']);

$threads = null;
if ($do == 'followed') {
    if(count($cfg['user']['follow']) > 0) {
        foreach ($cfg['user']['follow'] AS $threadid) {
            if($threads != null) $threads .= " OR ";
            $threads .= "`posts`.`id` = '" . $threadid . "'";
        }
    } else {
        $threads = 'false';
    }

    $title = T_("Followed threads"); // Site title
    $i['url'] = 'personal/followed';
    $i['name'] = T_("Followed threads");
    $i['description'] = T_("The threads you are following are shown on this page.");
} elseif ($do == 'mythreads') {
    $q = mysql_query("SELECT `id` FROM `posts` WHERE `deleted_time` = '0' AND `uid` = '". $cfg['user']['uid'] ."' AND `thread` = '0' ORDER BY `id` DESC");
    
    $title = T_("My threads");
    $i['url'] = 'personal/mythreads';
    $i['name'] = T_("My threads");
    $i['description'] = T_("The threads you have made are shown on this page.");
    
    if( mysql_num_rows( $q ) != 0 )
	{
		while( $thread = mysql_fetch_assoc( $q ) )
		{
			if($threads != null) $threads .= " OR ";
			$threads .= "`posts`.`id` = '". $thread['id'] ."'";
		}
	} else {
        $threads = 'false';
    }
        
} elseif ($do == 'repliedthreads') {
    $q = mysql_query("SELECT `thread` AS `id` FROM `posts` WHERE `deleted_time` = '0' AND `uid` = '". $cfg['user']['uid'] ."' AND `thread` != '0' GROUP BY `thread` ORDER BY `id` DESC");
    
    if (!$q) {
    $message  = 'Invalid query: ' . mysql_error() . "\n";
    die($message);
}
    $title = T_("Replied threads");
    $i['url'] = 'personal/repliedthreads';
    $i['name'] = T_("Replied threads");
    $i['description'] = T_("The threads you have replied to are shown on this page.");
    
        if( mysql_num_rows( $q ) != 0 )
	{
		while( $thread = mysql_fetch_assoc( $q ) )
		{
			if($threads != null) $threads .= " OR ";
			$threads .= "`posts`.`id` = '". $thread['id'] ."'";
		}
	} else {
            $threads = 'false';
        }
        
} else {
    header("Location: " . $cfg['htmldir']);
    die();
}

include("inc/header.php");
echo common_top($i);

echo '
<div class="infobar">' . sprintf(T_("Thread list of the board /%s/"), $do) . '.</div>
<p><a href="' . $cfg['htmldir'] . '/' . $i['url'] . '/" class="button">' . T_("Return to the board") . '</a></p>';


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
/*
$lq = mysql_query("
			SELECT `posts`.`id`, `posts`.`board`, `posts`.`subject`, `posts`.`message`, `posts`.`time`, `boards`.`url`, `post_files`.`fileid`, `files`.`folder`, `files`.`extension`, `files`.`name`, `files`.`thumb_ext`
			FROM `boards`, `posts`
				LEFT JOIN `post_files` ON `posts`.`id` = `post_files`.`postid`
				LEFT JOIN `files` ON `files`.`id` = `post_files`.`fileid`
			WHERE `posts`.`board` = `boards`.`id` AND `posts`.`thread` = '0' AND `posts`.`deleted_time` = '0'
			GROUP BY `posts`.`id`
			ORDER BY `posts`.`sticky`, `posts`.`bump_time` DESC
			LIMIT 150 
		");
// */

$lq = mysql_query("SELECT `posts`.`id`, 
        `posts`.`board`, 
        `posts`.`subject`, 
        `posts`.`message`, 
        `posts`.`time`, 
        `boards`.`url`, 
        `post_files`.`fileid`, 
        `files`.`folder`, 
        `files`.`extension`, 
        `files`.`name`, 
        `files`.`thumb_ext` 
    FROM `boards`, `posts`
        LEFT JOIN `post_files` ON `posts`.`id` = `post_files`.`postid`
        LEFT JOIN `files` ON `files`.`id` = `post_files`.`fileid`
    WHERE `posts`.`board` = `boards`.`id` 
    AND `posts`.`thread` = '0' 
    AND `deleted_time` = '0' 
    AND (". $threads .")
    ORDER BY `bump_time` DESC 
    LIMIT 0,150");

if (!$lq) {
    $message  = 'Invalid query: ' . mysql_error() . "\n";
    die($message);
}


$b = 0;
while ($l = mysql_fetch_assoc($lq)) {
    $b++;

    if (!empty($l['fileid'])) {

        if (in_array($l['extension'], $cfg['thumbnail_filetypes'])) {
            $sauce_end = '/files/' . $l['folder'] . '/thumb/' . $l['name'] . '.' . $l['thumb_ext'];
            $sauce = $cfg['srvdir'] . $sauce_end;
            if (is_file($sauce)) {
                $sauce = $cfg['htmldir'] . $sauce_end;
            } else {
                $sauce = getfileicon($l['extension']);
                $sauce = $sauce[1];
            }
        } else {
            $sauce = getfileicon($l['extension']);
            $sauce = $sauce[1];
        }
    }
    else
        $file = false;


    $countq = mysql_query("
				SELECT 
					(SELECT COUNT(`id`) FROM `posts` WHERE `thread` = '" . $l['id'] . "' LIMIT 1) AS `postcount`,
					(SELECT COUNT(*) FROM `hide` WHERE `thread` = '" . $l['id'] . "' LIMIT 1) AS `hidecount`,
					(SELECT COUNT(*) FROM `follow` WHERE `thread` = '" . $l['id'] . "' LIMIT 1) AS `followcount`
				LIMIT 1");
    $replies = mysql_result($countq, 0, "postcount");
    $hides = mysql_result($countq, 0, "hidecount");
    $follows = mysql_result($countq, 0, "followcount");

    echo '
			<tr>
				<td>
					<p>
						' . T_("No.") . ' <a href="' . $cfg['htmldir'] . '/' . $l['url'] . '/' . $l['id'] . '/">' . $l['id'] . '</a> - <b>' . (!empty($l['subject']) ? stripslashes($l['subject']) : T_("No subject")) . '</b><br />
						' . date(T_("Y/m/d g:i:s A"), $l['time']) . '
					</p>
				</td>
				<td>
					<p>
						<a href="' . $cfg['htmldir'] . '/' . $l['url'] . '/' . $l['id'] . '/">';

    if ($l['fileid'])
        echo '<img src="' . $sauce . '" class="mini_img" alt="' . T_("Image") . '" />';
    else
        echo T_("No image");
    echo '</a>
					</p>
				</td>
				<td>
					<blockquote>
						' . truncate(stripslashes($l['message']), false, 512, false, false) . '
					</blockquote>
				</td>
				<td>
					<p>' . $replies . '&nbsp;' . ($replies == 1 ? T_('reply') : T_('replies')) . '</p>
					<p>' . $hides . '&nbsp;' . ($hides == 1 ? T_('hide') : T_('hides')) . '</p>
					<p>' . $follows . '&nbsp;' . ($follows == 1 ? T_('follower') : T_('followers')) . '</p>
				</td>
			</tr>';
}
echo '
		</table>
		<div class="infobar bottom_border">' . sprintf(T_("%s threads shown"), $b) . '</div>';

echo '<div class="boardnav">		[ <a href="' . $cfg['htmldir'] . '/personal/followed/">' . T_("Followed") . '</a> |
		<a href="' . $cfg['htmldir'] . '/personal/mythreads/">' . T_("My threads") . '</a> |
		<a href="' . $cfg['htmldir'] . '/personal/repliedthreads/">' . T_("Replied") . '</a> ]
		<div style="float: right;">
			[ <a href="' . $cfg['htmldir'] . '/">' . T_('Front page') . '</a> |
			<a href="' . $cfg['htmldir'] . '/search/">' . T_('Message search') . '</a> |
			<a href="' . $cfg['htmldir'] . '/settings/">' . T_("Site personalization") . '</a> ]
		</div>
		<br />' . boardnav() . '</div>';
?>
</div>

<?php
include("inc/footer.php"); // Html-footeri
?>
