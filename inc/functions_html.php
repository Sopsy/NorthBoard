<?php
// Northpole.fi
// HTML:ää tulostavat toiminnot
// 14.2.2010

function mediaplayer($file, $id, $width = 320, $height = 24, $yt = false) {
	global $cfg;
	
	$mediaid = $id.rand(0,999999);
	
	if(!$yt) {
	
		if(!is_numeric($file) AND !$yt) return false;
		
		$file = mysql_real_escape_string($file);
		
		$q = mysql_query("SELECT * FROM `files` WHERE `id` = '". $file ."' LIMIT 1");
		$f = mysql_fetch_assoc($q);
		$file = $cfg['htmldir'] ."/files/". $f['folder'] ."/orig/". $f['name'] .'.'. $f['extension'];
	
	}
	
	if( $f['extension'] == 'mp3' )
	{
		$player = 'niftyplayer';
		$ext = 'flash';
	}
	elseif( in_array( $f['extension'], array('flv', 'mp4') ) )
	{
		$player = 'jwplayer';
		$ext = 'flash';
	}
	elseif( in_array( $f['extension'], array('mod', 'xm', 's3m', 'it') ) )
	{
		$player = "javamod";
		$ext = 'java';
	}
	else
	{
		$player = 'jwplayer';
		$ext = 'flash';
	}
	
	
	$loadMediaScript = 'loadMedia(\''. $player .'\', \'media_'. $mediaid .'\', \''. $file .'\', \''. $width .'\', \''. $height .'\')';
	
	$return = '<div id="media_'. $mediaid .'"'. ($width == 320 ? ' style="margin: 5px 0;"' : '') .'>';
	
	if( !$cfg['user']['autoload_media'] OR $player == 'javamod' )
	{
		$return .= '</div><a id="loadmedia_'. $mediaid .'" class="load'. ($ext == 'java' ? 'java' : '')  .'media" href="javascript:void('. $loadMediaScript .');">'. T_("Open media player") .' ('. ($ext == 'java' ? T_('Java') : T_('Flash')) .')</a>';
	}
	else
	{
		$return .= '</div><script type="text/javascript">'. $loadMediaScript .'</script>';
	}
	

	return $return;
}

function flag($msg, $international = 0)
{
	global $cfg;
	
	if($cfg['post_countries'])
	{
		//if($msg['proxy'] != 0 OR $international == 1 OR strtolower($msg['geoip_country_code']) != $cfg['local_country_code'] AND $msg['geoip_country_code'] != "unk")
		if($msg['proxy'] != 0 OR $international == 1 AND $msg['geoip_country_code'] != "unk")
		{
			if(!is_file($cfg['srvdir'] ."/css/img/flags/". strtolower($msg['geoip_country_code']) .".png"))
				$country = "unk";
		
			if(empty($msg['hide_region']) OR $msg['hide_region'] === 0)
				$alt_text = $msg['geoip_country_name'] . (!empty($msg['geoip_region_name']) ? ', '. $msg['geoip_region_name'] : '') . (!empty($msg['geoip_city']) ? ', '. $msg['geoip_city'] : '');
			else
				$alt_text = $msg['geoip_country_name'];
		
			$return = '<img src="'. $cfg['htmldir'] .'/css/img/flags/'. strtolower($msg['geoip_country_code']) .'.png" alt="'. $alt_text .'" title="'. $alt_text .'" /> ';
			if($msg['proxy'] == 1)
			{
				$alt_text = T_('Proxy server');	
				$return .= '<img src="'. $cfg['htmldir'] .'/css/img/flags/prx.png" alt="'. $alt_text .'" title="'. $alt_text .'" /> ';
			}
			elseif($msg['proxy'] == 2)
			{
				$alt_text = T_('TOR Exit node');	
				$return .= '<img src="'. $cfg['htmldir'] .'/css/img/flags/tor.png" alt="'. $alt_text .'" title="'. $alt_text .'" /> ';
			}
			return $return;
		}
		else return '';
	}
	else return '';
}

function getfileicon($ext) {
	global $cfg;

	$geticon = mysql_query("SELECT `image` FROM `filetypes` WHERE `extension` = '". $ext ."' LIMIT 1");
	if(mysql_num_rows($geticon) != 0) {
		$gen_icon = '/css/img/fileicons/'. $cfg['gen_fileicon'];
		
		$image = mysql_result($geticon, 0, "image");
		if(!empty($image)) {
			$sauce = '/css/img/fileicons/'. $image;
			if(!is_file($cfg['srvdir'] .'/css/img/fileicons/'. $image)) $sauce = $gen_icon;
		}
		else $sauce = $gen_icon;
	}
	else $sauce = $gen_icon;
	
	$ret[0] = $cfg['srvdir'] . $sauce;
	$ret[1] = $cfg['htmldir'] . $sauce;
	
	return $ret;
}

function generate_embed_code($id, $source) {
	global $cfg;
	$query = mysql_query("SELECT * FROM `embed_sources` WHERE `id` = '". mysql_real_escape_string($source) ."' LIMIT 1");
	if(mysql_num_rows($query) != 0) {
		$t = mysql_fetch_assoc($query);
		

		$code = $t['code'];
		$replace = array('[HEIGHT]', '[WIDTH]', '[EMBEDID]');
		$trueval = array($t['height'], $t['width'], $id);

		$code = str_replace($replace, $trueval, $code);
		
		// Dirty fix to prevent youtube from autoplaying
		$code = str_replace("&autoplay=1", "", $code);

		return $code;
	}
	else return false;
}

function get_omitted_count($threadid, $posts_to_show, $start = false, $light = false) {

	$cq = mysql_query("SELECT COUNT(`id`) AS 'count' FROM `posts` WHERE `deleted_time` = '0' AND `thread` = '". mysql_real_escape_string($threadid) ."'");
	$c = mysql_result($cq, 0, "count");
	
	if($start === false) {
		$start = $c - $posts_to_show;
		if($start < 0) $start = 0;
	}
	
	$filecount = false;
	if(!$light) {
		if($start > 0) {
			
			//------
			// This version of MySQL doesn't yet support 'LIMIT & IN/ALL/ANY/SOME subquery'
			// :(
			$fq = array();
			$fq_a = mysql_query("SELECT `id` FROM `posts` WHERE `deleted_time` = '0' AND `thread` = '". $threadid ."' ORDER BY `time` ASC");
			for($i = 0; $i < mysql_num_rows($fq_a); $i++)
			{
				$fq[] = mysql_result($fq_a, $i, "id");
			}
			if(!empty($fq))
				$foo = implode(",", $fq);
			if(empty($foo))
				$foo = 0;
			//------
			
			if(count($foo) > 3)
				array_pop(array_pop(array_pop($foo)));
			else
			{
				for($i = 0; count($foo) != 1; ++$i)
				{
					array_pop($foo);
				}
			}
			
			$fq_b = mysql_query("
				SELECT COUNT(`fileid`) AS `count`
				FROM `post_files`
				WHERE `postid` IN (". $foo .")
			");

			$filecount = mysql_result($fq_b, 0, "count");
		}
	}
			echo mysql_error();
	
	$return = array("start" => $start, "c" => $c, "filecount" => $filecount);
	
	return $return;
}

function print_thread($thread, $location = "board", $open_board = false, $overboard = false) {
	global $cfg;

	if(!empty($thread)) {
		$boardq = mysql_query("SELECT * FROM `boards` WHERE `id` = '". $thread['board'] ."' LIMIT 1");
		$board = mysql_fetch_assoc($boardq);
		
		echo '
		<div class="thread" id="thread_'. $thread['id'] .'">';

		$thread['on_page'] = 1;
		print_post($thread, $location, $board, $open_board, $overboard);
				
		echo '
		<div id="answers_'. $thread['id'] .'" class="answers">';
		
		if($location == "board") {
		
			$fc = get_omitted_count($thread['id'], 3, false, $cfg['light_omit_count']);
			if($fc['start'] != 0) echo '
			<p class="omitted" id="omitted_'. $thread['id'] .'">'.
			$fc['start'] .' '. ($fc['start'] == 1 ? T_("message") : T_("messages"))
			.($fc['filecount'] != 0 ? ' '. T_("and") .' '. $fc['filecount'] .' '. ($fc['filecount'] == 1 ? T_("file") : T_("files")) : '')
			.' '. T_("omitted.") .'</p>';
			
			$aq = mysql_query("SELECT `posts`.*, `users`.`hide_region` FROM `posts` LEFT JOIN `users` USING (uid) WHERE `posts`.`deleted_time` = '0' AND `posts`.`thread` = '". mysql_real_escape_string($thread['id']) ."' ORDER BY `posts`.`id` ASC LIMIT ". $fc['start'] .", ". $fc['c'] ."");				
		}
		else {
			$fc = get_omitted_count($thread['id'], $cfg['ppp']*$thread['page'], false, $cfg['light_omit_count']);
			$fcb = get_omitted_count($thread['id'], $cfg['ppp']*$thread['page'], $cfg['ppp']*($thread['page']-1), $cfg['light_omit_count']);

			if($fcb['start'] == 0 AND $fc['start'] == 0) echo '
			<p class="expand_images"><a href="javascript:void(expand_images(\''. $thread['id'] .'\'));">'. T_("Expand all images") .'</a></p>
			<p class="omitted">'. T_("No omitted posts.") .'</p>';
			else echo '
			<p class="omitted"><strong>'.
			sprintf(T_("Page %s:"), $thread['page'])
			.'</strong> '. $fcb['start'] .' '
			.($fcb['start'] == 1 ? T_("message") : T_("messages"))
			.($fcb['filecount'] != 0 ? ' '. T_("and") .' '. $fcb['filecount'] .' '. ($fcb['filecount'] == 1 ? T_("file") : T_("files")) : '')
			.' '. T_("from older posts and also") .' '
			.$fc['start'] .' '. ($fc['start'] == 1 ? T_("message") : T_("messages"))
			.($fc['filecount'] != 0 ? ' '. T_("and") .' '. $fc['filecount'] .' '. ($fc['filecount'] == 1 ? T_("file") : T_("files")) : '')
			.' '. T_("from newer posts") .' '. T_("omitted.") .'</p>';
			
			$aq = mysql_query("SELECT `posts`.*, `users`.`hide_region` FROM `posts` LEFT JOIN `users` USING (uid) WHERE `posts`.`deleted_time` = '0' AND `posts`.`thread` = '". mysql_real_escape_string($thread['id']) ."' ORDER BY `posts`.`id` ASC LIMIT ". $thread['limit_start'] .", ". $thread['limit_end']);
		}
		
		echo '
			<div id="expand_container_'. $thread['id'] .'">';
			
		$tpagesq = mysql_query("SELECT COUNT(`id`) AS 'count' FROM `posts` WHERE `deleted_time` = '0' AND `thread` = '". $thread['id'] ."'");

		if($location == "board") {
			$amount = $cfg['rpt'];
			$tmsg_page = array();
			for($i = 0; $i != $amount; $i++) {
				$tmp = ceil((mysql_result($tpagesq, 0, "count") - $i) / $cfg['ppp']);
				if($tmp < 1) $tmp = 1;
				$tmsg_page[] = $tmp;
			}
			$tmsg_page = array_reverse($tmsg_page);
			$i = 0;
		}
		while($reply = mysql_fetch_assoc($aq)) {
			if($location == "board") {
				$reply['on_page'] = $tmsg_page[$i];
				$i++;
			}
			elseif($location == 'thread')
			{
				$reply['on_page'] = $thread['page'];
			}
			print_post($reply, $location, $board);
		}
		echo '
			</div>
			</div>
			<div class="clear"></div>
		</div>
		<hr class="line" id="line_'. $thread['id'] .'" />
		';
	}
	else return false;
}

function print_post($msg, $location, $board, $open_board = false, $overboard = false) {
	global $cfg;
	
	if(in_array($msg['id'], $cfg['user']['follow'])) {
		$followed = true;
	}
	else {
		$followed = false;
	}
	
	if($board == 'msgprev')
	{
		$msgprev_bq = mysql_query("SELECT * FROM `boards` WHERE `id` = '". $msg['board'] ."' LIMIT 1");
		$board = mysql_fetch_assoc($msgprev_bq);
		$msgprev = true;
	}
	else
		$msgprev = false;
			
	if($msg['thread'] == 0 AND !$msgprev) {
		// This is the starting message of a thread and is not previewed
		$borderless = '';
		$answer = '';
		$isAnswer = false;
	}
	else {
		// This is a reply to a thread or is previewed
		$borderless = ' class="borderless"';
		$answer = ' class="answer"';
		$isAnswer = true;
	}
	
	if(!$msgprev) {
		$burl = $board['url'];
		$bname = $board['name'];
		$divid = ' id="no'. $msg['id'] .'"';
	}
	else {
		$burl = $msg['url'];
		$bname = $msg['boardname'];
		$divid = '';
	}
	
	if($isAnswer)
		echo '
		<div class="gtgt">&gt;&gt;</div>';
	
	echo '
		<div'. $divid . $answer .'>';
	
	// Count the files
	$fileq = mysql_query("
		SELECT `post_files`.*, `files`.*
		FROM `files`, `post_files` WHERE `post_files`.`postid` = '". $msg['id'] ."' AND `files`.`id` = `post_files`.`fileid` AND `files`.`id` IN (
			SELECT `fileid`
			FROM `post_files`
			WHERE `postid` = '". $msg['id'] ."'
		)
		ORDER BY `post_files`.`order` ASC
	");
	
	$filecountb = mysql_num_rows($fileq);
	
	if(!$isAnswer AND $filecountb == '1')
		// Print files
		$filecount = print_files($msg, 'ap', $filecountb, $fileq);

	echo '
			<div class="postinfo">
				'. ($answer == '' ? '<div id="followinfo_'. $msg['id'] .'"><p>'. ($followed ? T_('This thread is being followed') : '') .'</p></div>' : '');
			
			if($overboard AND $msg['thread'] == 0 OR $msgprev) echo '
			<p class="location'. ($msgprev ? ' borderless' : '') .'">'. T_("Thread location:") .' <a href="'. $cfg['htmldir'] .'/'. $burl .'/">/'. $burl .'/ - '. $bname .'</a></p>
			';
			
			
			echo '
			<p'. $borderless .'>';
			if(!empty($msg['subject'])) echo '
				<span class="post_subject">'. $msg['subject'] .'</span>';
			echo '
				<input type="checkbox" name="delete'. $msg['id'] .'" class="checkbox_post" />';
			
				
				$post_extra = "";
				if( $msg['sage'] == 1 )
				{
					$post_extra .= '<span class="sage">'. T_('Sage') .'</span>';
				}
				if( $msg['rage'] == 1 AND $msg['love'] == 0 )
				{
					$post_extra .= ' <span class="rage">'. T_('RAGE!') .'</span>';
				}
				if( $msg['love'] == 1 AND $msg['rage'] == 0 )
				{
					$post_extra .= ' <span class="love">'. T_('Love') .'</span>';
				}
				if( $msg['love'] == 1 AND $msg['rage'] == 1 )
				{
					$post_extra .= ' <span class="tsundere">'. T_('Tsundere!') .'</span>';
				}
				
				if(!$cfg['user']['hide_names'])
				{
					if(empty($msg['name']) AND $msg['posted_by_op'] == 0 AND $board['show_empty_names'] == '1')
						$postername = $board['default_name'];
					elseif(empty($msg['name']) AND $msg['posted_by_op'] == 1)
						$postername = T_("OP");
					elseif(empty($msg['name']) AND $msg['posted_by_op'] == 2 AND $board['show_empty_names'] == '1')
						$postername = $board['default_name'];
					elseif(empty($msg['name']) AND $board['show_empty_names'] == '0')
						$postername = '';
					else
						$postername = $msg['name'];
				}
				elseif($msg['posted_by_op'] == '1')
				{
					$postername = T_("OP");
					$tripcode = '';
				}
				else
				{
					$postername = '';
					$tripcode = '';
				}
				
				echo '
				<span class="postername">'. flag($msg, $board['international']) . $postername .'</span>';
				if(!empty($msg['tripcode'])) echo '<span class="tripcode">'. $msg['tripcode'] .'</span>';

				echo '
				<span class="post_time">'. date(T_("Y/m/d g:i:s A"), $msg['time']) .'</span>
				<span class="post_number">
					<a href="'. $cfg['htmldir'] .'/'. $board['url'] .'/'. ($msg['thread'] == 0 ? $msg['id'] : $msg['thread']) . ($msg['on_page'] != 1 ? '-'. $msg['on_page'] : '') .'/#hl_'. $msg['id'] .'">'. T_("No.") .'</a>
					<a href="'. $cfg['htmldir'] .'/'. $board['url'] .'/'. ($msg['thread'] == 0 ? $msg['id'] : $msg['thread']) . ($msg['on_page'] != 1 ? '-'. $msg['on_page'] : '') .'/#q_'. $msg['id'] .'" onclick="quote(\''. $msg['id'] .'\', \'msg\', \''. $board['url'] .'\', \''. ($msg['thread'] == 0 ? $msg['id'] : $msg['thread']) .'\'); return false;">'. $msg['id'] .'</a>';
					if($msg['locked'] == 1) echo '
					<img src="'. $cfg['htmldir'] .'/css/img/icons/lock.png" alt="'. T_("Locked") .'" title="'. T_("Locked") .'" />';
					if($msg['sticky'] == 1) echo '
					<img src="'. $cfg['htmldir'] .'/css/img/icons/attach.png" alt="'. T_("Stickied") .'" title="'. T_("Stickied") .'" />';
				echo '
				</span>
				'. $post_extra ;
				if($msg['thread'] == 0) {
				
				
				echo '
				<span class="moar_buttans">
					<a href="javascript:void(hide_thread(\''. $msg['id'] .'\', \'hide\'));"
						><img src="'. $cfg['htmldir'] .'/css/img/icons/comments_delete.png" alt="'. T_("Hide thread") .'" title="'. T_("Hide thread") .'"
					/></a>
					<a id="followlink_'. $msg['id'] .'" href="javascript:void(follow_thread(\''. $msg['id'] .'\',\''. (!$followed ? 'add' : 'remove') .'\'));"
						><img id="followlink_img_'. $msg['id'] .'" src="'. $cfg['htmldir'] .'/css/img/icons/folder_'. (!$followed ? 'add' : 'delete') .'.png" alt="'. (!$followed ? T_("Follow thread") : T_("Remove from followed threads.")) .'" title="'.(!$followed ? T_("Follow thread") : T_("Remove from followed threads.")) .'"
					/></a>';
					
					// These links are shown only if we don't have the thread open.
					if($location == "board")
					{
						echo '
					<a id="expandlink_'. $msg['id'] .'" href="javascript:void(expand_thread(\''. $msg['id'] .'\',\'expand\'));"
						><img id="expandlink_img_'. $msg['id'] .'" src="'. $cfg['htmldir'] .'/css/img/icons/arrow_down.png" alt="'. T_("Expand thread") .'" title="'. T_("Expand thread") .'"
					/></a>
					
					<a id="quickreplylink_'. $msg['id'] .'" href="javascript:void(quickReply(false, \''. $msg['id'] .'\',\''. $msg['board'] .'\', \''. $board['url'] .'\'));"
						><img id="quickreplylink_img_'. $msg['id'] .'" src="'. $cfg['htmldir'] .'/css/img/icons/comment_add.png" alt="'. T_("Quick reply") .'" title="'. T_("Quick reply") .'"
					/></a>
					';
					}
					
				echo '
				</span>';
				
					$pagesq = mysql_query("SELECT COUNT(`id`) AS 'count' FROM `posts` WHERE `deleted_time` = '0' AND `thread` = '". $msg['id'] ."'");
					$pages = ceil(mysql_result($pagesq, 0, "count") / $cfg['ppp']);
					
					if($pages < 1) $pages = 1;
					
					if($pages == 1) $pages = '';
					else $pages = '-'. $pages;
						
					if($location == "board")
						echo '
				<span class="answer_link"><span class="button_wrap">[</span><a href="'. $cfg['htmldir'] .'/'. $board['url'] .'/'. $msg['id'] . $pages .'/" class="button">'. T_("Reply") .'</a><span class="button_wrap">]</span></span>';
				}
				
				if($cfg['user_class'] >= 1) {
				
					echo '
				<span class="adminmenu">
					<span class="button_wrap">[</span><a href="'. $cfg['htmldir'] .'/mod/bans/add/'. $msg['ip'] .'/'. $msg['id'] .'" class="button">'. T_("Ban") .'</a><span class="button_wrap">]</span>
					<span class="button_wrap">[</span><a href="'. $cfg['htmldir'] .'/mod/messages/delete/'. $msg['id'] .'" onclick="if(!confirm(\'O rly?\')){return false;}" class="button">'. T_("Delete") .'</a><span class="button_wrap">]</span>
					<span class="button_wrap">[</span><a href="'. $cfg['htmldir'] .'/mod/messages/deleteallbyiphash/'. $msg['ip'] .'" onclick="if(!confirm(\'O rly?\')){return false;}" class="button">'. T_("Delete all") .'</a><span class="button_wrap">]</span>';
					if($msg['thread'] == 0) {
						echo '
						<span class="button_wrap">[</span><a href="'. $cfg['htmldir'] .'/mod/messages/merge/'. $msg['id'] .'" class="button">'. T_("Merge") .'</a><span class="button_wrap">]</span>
						<span class="button_wrap">[</span><a href="'. $cfg['htmldir'] .'/mod/messages/move/'. $msg['id'] .'" class="button">'. T_("Move") .'</a><span class="button_wrap">]</span>';
						
						if($msg['locked'] == 1) echo '
						<span class="button_wrap">[</span><a href="'. $cfg['htmldir'] .'/mod/messages/unlock/'. $msg['id'] .'" class="button">'. T_("Unlock") .'</a><span class="button_wrap">]</span>';
						else echo '
						<span class="button_wrap">[</span><a href="'. $cfg['htmldir'] .'/mod/messages/lock/'. $msg['id'] .'" class="button">'. T_("Lock") .'</a><span class="button_wrap">]</span>';

						if($cfg['user_class'] == 1 OR $cfg['user_class'] == 2) {
						if($msg['sticky'] == 1) echo '
						<span class="button_wrap">[</span><a href="'. $cfg['htmldir'] .'/mod/messages/unstick/'. $msg['id'] .'" class="button">'. T_("Unstick") .'</a><span class="button_wrap">]</span>';
						else echo '
						<span class="button_wrap">[</span><a href="'. $cfg['htmldir'] .'/mod/messages/stick/'. $msg['id'] .'" class="button">'. T_("Stick") .'</a><span class="button_wrap">]</span>';
						}
					}
					echo '
				</span>';
				}
				echo '
			</p>
			</div>';
		
	if($isAnswer OR $filecountb != 1)
		// Print files
		$filecount = print_files($msg, 'ap', $filecountb, $fileq);
	
	// If thread is 0, the thread is this post.
	if($msg['thread'] == 0) $msg['thread'] = $msg['id'];
	
	$message = $msg['message'];
	if($location == "board") $message = truncate($msg['message'], $msg['id'], $cfg['msg_prevlength'], $cfg['htmldir'] .'/'. $board['url'] .'/'. $msg['thread'] .'-'. $msg['on_page'] .'/#hl_'. $msg['id']);
	$message = nl2br_pre($message);

	
	$embed = generate_embed_code($msg['embed_code'], $msg['embed_source']);
	
	if(!empty($msg['embed_code']) AND $msg['embed_source'] != 0 AND $cfg['allow_embeds']) {
		if($cfg['user']['autoload_media'])
			echo '
					<div class="embed">
						'. $embed .'
					</div>';
		else
			echo '
					<div class="embed">
						<div id="embed_'. $msg['id'] .'" style="display: none;">'. $embed .'</div>
						<p id="loadembed_'. $msg['id'] .'"><a href="javascript:void(loadEmbed(\''. $msg['id'] .'\'));">'. T_("Open embedded media") .'</a></p>
					</div>';
	}
	
	if(!empty($message))
	echo '
					<div class="post'. ($filecount > 2 ? ' files_many' : '') .'" id="post_'. $msg['id'] .'">
						<p>
						'. ($cfg['https'] ? str_replace("http://". $cfg['htmldir_plain'], "https://". $cfg['htmldir_plain'], $message) : $message) .'
						</p>
					</div>';
		
		if($isAnswer)
			echo '
			<div class="clear"></div>';
			
		if($msg['modpost'] != 0) {
				echo '
			<div style="border-top: 1px solid #444; background-color: #222; padding: 5px; color: #FF22A2;"';
				if($msg['modpost'] == 1) echo '>'. T_("This message was posted by an admin");
				elseif($msg['modpost'] == 2) echo '>'. T_("This message was posted by a super moderator");
				elseif($msg['modpost'] == 3) echo '>'. T_("This message was posted by a moderator");
				echo '</div>';
		}
		if(!$msgprev)
			echo '
		</div>';
}

function print_files($post, $location = "ap", $filecount, $fileq) {
	global $cfg;
	
	// Are there any files on the post?
	if($filecount > 0) {
	
		if( $filecount > 1 )
			echo '
			<div class="'. ($post['thread'] == 0 ? 'op_filecontainer' : 'filecontainer') .'">';
		
		// Output the files
		$i = 1;
		while( $file = mysql_fetch_assoc( $fileq ) )
		{
		
			$dl_sauce = $cfg['static_htmldir'] .'/download/'. $file['name'] .'/'. rawurlencode($file['orig_name']) .'.'. $file['extension'];
			$local_sauce = $cfg['srvdir'] .'/files/'. $file['folder'] .'/orig/'. $file['name'] .'.'. $file['extension'];
			
			$limitsize = false;
			
			if( in_array( $file['extension'], array('jpg', 'jpeg', 'png', 'gif') ) )
			{
			
				$sauceb = $cfg['static_htmldir'] .'/files/'. $file['folder'] .'/thumb/'. $file['name'] .'.'. $file['thumb_ext'];
				if($file['extension'] == 'gif' AND ($cfg['user']['autoplay_gifs'] == '0' OR !($cfg['anim_thumbs'] OR $cfg['anim_thumbs_small'] AND filesize($sauceb) < $cfg['anim_thumbs_small_size']) ) )
					$sauceb = $cfg['static_htmldir'] .'/files/'. $file['folder'] .'/thumb/noanim-'. $file['name'] .'.'. $file['thumb_ext'];
				$sauce = $cfg['static_htmldir'] .'/files/'. $file['folder'] .'/orig/'. $file['name'] .'.'. $file['extension'];
				
				$imgfile = true;
				$videofile = false;
			}
			elseif( in_array( $file['extension'], array('mp4', 'flv') ))
			{
				$videofile = true;
				$imgfile = false;
			}
			else {
				$imgfile = false;
				$videofile = false;
				if($file['id3_image'] == 0 AND !in_array( $file['extension'], $cfg['thumbnail_filetypes'] ) )
				{
					$sauce = getfileicon($file['extension']);
					$sauce = $sauce[1];
				}
				else {
					$sauce = '/files/'. $file['folder'] .'/thumb/'. $file['name'] .'.'. $file['thumb_ext'];
					if( !is_file( $cfg['srvdir'] . $sauce ) )
					{
						$sauce = getfileicon($file['extension']);
						$sauce = $sauce[1];
					}
					else
						$sauce = $cfg['htmldir'] . $sauce;
					if( in_array( $file['extension'], array('mp3', 'ogg', 'flac') ) )
						$limitsize = true;
				}
			}
			
			if($filecount > 1)
				$shorten = 20;
			else
				$shorten = 60;
			
			if(strlen($file['orig_name']) > $shorten) {
				$file['orig_name'] = mb_substr($file['orig_name'], 0, $shorten) ."[..]";
			}
			$shortened = htmlspecialchars($file['orig_name'] .'.'. $file['extension']);
			
			if($imgfile)
				$link = '<a href="'. $dl_sauce .'" onclick="window.open(this.href); return false;">'. $shortened .'</a>';
			else {
				$link = '<a href="'. $dl_sauce .'">'. $shortened .'</a>';
				if(!empty($file['id3_artist']) OR !empty($file['id3_name']) OR !empty($file['id3_length']) OR !empty($file['id3_bitrate']))
				{
					$id3info = htmlspecialchars($file['id3_artist']) . (strlen($file['id3_artist']) != 0 ? ' - ' : '') . htmlspecialchars($file['id3_name']) .' ('. htmlspecialchars($file['id3_length']) .', '. htmlspecialchars($file['id3_bitrate']) .')';

					if($filecount == 1)
						$id3info = ', '. $id3info;
				}
				else
					$id3info = "";
			}
			
			$information = '<p class="fileinfo'. ($filecount == 1 ? '_single">' : '_many">').''. $link .''. ($filecount == 1 ? ' ' : '<br />') .'('. convert_filesize($file['size']) . ($imgfile ? ', '. htmlspecialchars($file['information']) : ($filecount == 1 ? ''. $id3info : ($id3info != '' ? ', <abbr title="tooltip;'. htmlspecialchars($id3info) .'">'. T_("Details") .'</abbr>' : ''))) .')</p>';
				
			if($filecount == 1) echo $information;
			if($filecount == 1) {
				$width = 320;
				if($file['extension'] == "flv" OR $file['extension'] == "mp4") $height = (240+24);
				else $height = 24;
			}
			else {
				$width = 240;
				if($file['extension'] == "flv" OR $file['extension'] == "mp4") $height = (180+24);
				else $height = 24;
			}
			
			if( in_array( $file['extension'], array( 'flv', 'mp4', 'mp3', 'mod', 's3m', 'xm', 'it' ) ) ) $media = true;
			else $media = false;
			
			if($location != "ref" AND $media)
				$mediaplayer = mediaplayer($file['id'], $file['id'] . $i, $width, $height);
			else
				$mediaplayer = "";
	
			if($imgfile) {
				list($width, $height, $type, $attr) = getimagesize($local_sauce);
			}
			
			echo '
				<div class="file_'. ($filecount == 1 ? 'single' : 'many') .'" id="file_'. $file['id'] . $i .'">';
			if($filecount > 1) echo $information;

			if($media AND $filecount == 1) echo $mediaplayer;
			
			if(!$videofile) {
				if($imgfile) echo '
					<a class="contractimage" id="expandlink_'. $post['id'] . $file['id'] . $i .'" href="'. $dl_sauce .'" onclick="if(!event.ctrlKey && !event.altKey){expandimage(\''. $post['id'] .'\', \''. $file['id'] . $i .'\', \''. $sauce .'\', event); return false;}"><img id="imgfile_'. $post['id'] . $file['id'] . $i .'" src="'. $cfg['htmldir'] .'/css/img/loading.gif" alt="'. T_('Loading...') .'" style="display: none;" /></a>
					<a href="'. $dl_sauce .'" class="expandimage" id="expandlink_thumb_'. $post['id'] . $file['id'] . $i .'" onclick="if(!event.ctrlKey && !event.altKey){expandimage(\''. $post['id'] .'\', \''. $file['id'] . $i .'\', \''. $sauce .'\', event); return false;}"><img width="'. $file['thumb_width'] .'" height="'. $file['thumb_height'] .'" id="thumb_'. $post['id'] . $file['id'] . $i .'" src="'. $sauceb .'" alt="'. T_("Image") .'" /></a>
				';
				else echo '
				<div id="fileimg_'. $post['id'] . $file['id'] . $i .'">
				<a href="'. $dl_sauce .'" onclick="'. ($file['extension'] == "swf" ? 'toggle_flash(\''. $post['id'] . $file['id'] . $i .'\', \''. $cfg['htmldir'] .'/files/'. $file['folder'] .'/orig/'. $file['name'] .'.'. $file['extension'] .'\');' : 'window.open(this.href);') .' return false;">
					<img'. ($limitsize ? ' style="max-height:128px;"' : '') .' id="imgfile_'. $post['id'] . $file['id'] . $i .'" src="'. $sauce .'" alt="'. T_("Image") .'" />
				</a>
				</div>';
				if($file['extension'] == "swf") echo '
				<div id="flashcontainer_'. $post['id'] . $file['id'] . $i .'" style="display: none;"></div>
				<a id="flash_stop_'. $post['id'] . $file['id'] . $i .'" style="display: none;" href="javascript:toggle_flash(\''. $post['id'] . $file['id'] . $i .'\', false);">'. T_("Hide flash") .'</a>';
			}
			if($media AND $filecount != 1) echo $mediaplayer;
			echo '
			</div>';
			$i++;
		}
		if( $filecount > 1 )
			echo '
			</div>';
	}
	
	return $filecount;
}

function common_top($board) {
	global $cfg;

	$return = '';
		//$title = '/'. $board['url'] .'/ - '. $board['name'];
		$title = $board['name'];
		$desc = $board['description'];
		
		$return .= '
		
		<p class="title">'. $title .'</p>
		<p class="title_sub">'. $desc .'</p>
                ' . (isset($board['threadlist']) ? '<p class="title_sub">' . $board['threadlist'] . '</p>' : '') . '
		<hr class="line" />
		<div id="hide_postform">';
	
		if($cfg['user']['show_postform'] == '1')
			$return .= '
			<a class="hide_link_vertical" title="'. T_("Hide postform") .'" href="javascript:void(hide_element(\'postform\', \'hide\'));"></a>';
		else
			$return .= '
			<a class="show_link_vertical" title="'. T_("Show postform") .'" href="javascript:void(hide_element(\'postform\', \'show\'));"></a>
			<style>#hidepost { display: none; }</style>';
	
		$return .= '
		</div>';
		
	return $return;
}

function common_bottom() {
	global $cfg;
	
}

function buttons_bottom() {
	global $cfg;

	$return = '
			<div id="buttons_bottom">
				<p><a href="javascript: select_posts();">'. T_("Toggle all posts") .'</a></p>
				<p class="trow">'. T_("Download files") .'&nbsp;<span class="tcell">'. sprintf(T_("(Max size %s)"), convert_filesize($cfg['max_dl_filesize'])) .'</span></p>
				<label class="tcell" for="filename">'. T_("Archive name") .'</label><input type="text" class="tcell" name="filename" id="filename" />&nbsp;<input name="download" value="'. T_("Download") .'" type="submit" />

				<p class="trow">'. T_("Report message") .'</p>
				<label class="tcell" for="reason">'. T_("Reason") .'</label><input type="text" class="tcell" name="reason" id="reason"/>&nbsp;<input name="report" value="'. T_("Report") .'" type="submit" />
			
				<p class="trow">'. T_("Delete message") .'&nbsp;<span class="tcell"><input type="checkbox" name="onlyfile" id="onlyfile" value="on" /><label for="onlyfile">'. T_("File only") .'</label></span></p>
				<label class="tcell" for="passwd">'. T_("Password") .'</label><input type="password" class="tcell" name="passwd" id="passwd"';
		
	
	if(!empty($cfg['user']['post_password']))
		$return .= ' value="'. $cfg['user']['post_password'] .'"';
				
	$return .= ' />&nbsp;<input name="delete" value="'. T_("Delete") .'" type="submit" />
			</div>
	';
	
	return $return;
}

function boardnav($menu = false, $postform = false) {
	global $cfg;

	if(!defined('BOARDNAV') OR !defined('BOARDS_LEFT') OR !defined('BOARDS_POSTFORM')) {
				
		$return_a = '';
		$return_b = '<ul>';
		$return_c = '';
		
		if($cfg['use_overboard'])
		{
			if(!empty($_GET['board']) AND $_GET['board'] == $cfg['overboard_url'] OR !empty($_GET['id']) AND $_GET['id'] == $cfg['overboard_url'])
			{
				$cur = ' class="active"';
			}
			else
			{
				$cur = '';
			}
			$return_a .= '[ <a href="'. $cfg['htmldir'] .'/'. $cfg['overboard_url'] .'/"'. $cur .' title="tooltip;'. $cfg['overboard_name'] .'">'. $cfg['overboard_url'] .'</a> ] ';
			$return_b .= '
			<li'. $cur .'><a href="'. $cfg['htmldir'] .'/'. $cfg['overboard_url'] .'/">/'. $cfg['overboard_url'] .'/ - '. $cfg['overboard_name'] .'</a></li>';

		}
		
		$a = mysql_query("SELECT `id`, `name` FROM `categories` ORDER BY `order` ASC");
		while($b = mysql_fetch_assoc($a)) {
		
			$q = mysql_query("SELECT `id`, `url`, `name`, `worksafe` FROM `boards` WHERE `category` = '". $b['id'] ."' ORDER BY `order`, `url` ASC");
			if(mysql_num_rows($q) != 0) {

			$return_a .= '[';
			$return_b .= '
			
			<li><strong>'. $b['name'] .'</strong></li>';
			
			$i = 0;
			while($r = mysql_fetch_assoc($q)) {
				if($r['worksafe'] == '0' AND $cfg['user']['sfw'] == '1')
					continue;
					
				if(!empty($_GET['board']) AND $_GET['board'] == $r['url'] OR !empty($_GET['id']) AND $_GET['id'] == $r['url']) {
					$cur = ' class="active"';
				}
				else {
					$cur = '';
				}
				
				
				$class = '';
				
				if($i != 0) $return_a .= ' |';
				$return_a .= ' <a'. $cur .' href="'. $cfg['htmldir'] .'/'. $r['url'] .'/" title="tooltip;'. $r['name'] .'">'. $r['url'] .'</a> ';
				
				$return_b .= '
			<li'. $cur .'><a href="'. $cfg['htmldir'] .'/'. $r['url'] .'/">/'. $r['url'] .'/ - '. $r['name'] .'</a></li>';
				
				$return_c .= '
				<option value="'. $r['id'] .'">/'. $r['url'] .'/ - '. $r['name'] .'</option>';
				$i++;
			}
			$return_a .= '] ';
			}
		}
		$return_b .= '
		</ul>';
		define('BOARDNAV', $return_a);
		define('BOARDS_LEFT', $return_b);
		define('BOARDS_POSTFORM', $return_c);
	}
	else {
		$return_a = BOARDNAV;
		$return_b = BOARDS_LEFT;
		$return_c = BOARDS_POSTFORM;
	}
	
	if(!$menu AND !$postform) return $return_a;
	elseif($postform) return $return_c;
	else return $return_b;
}

function post_form($board, $thread = 0, $overboard) {
	global $cfg;
	
	$uniqid = uniqid(md5(rand()), true);
		
	if(!empty($_COOKIE['postername']))
		$postername = $_COOKIE['postername'];
	else
		$postername = '';
	
	$return = '
		<div id="hidepost">
		<div class="infobar" id="replyto_info"></div>
		<form id="post" action="'. $cfg['htmldir'] .'/post/" method="post" onsubmit="startbar(\''. $uniqid .'\'); return true;" enctype="multipart/form-data">
			<fieldset>';
			
			$return .= '
			<input type="hidden" name="overboard" value="'. (!$overboard ? 'false' : 'true') .'" />
			<input id="replyto_board" type="hidden" name="board"'. (!$overboard ? ' value="'. $board['id'] .'"' : '') .' />
			<input id="replyto_board_bak" type="hidden" value="" />';
			
			$return .= '
			<input id="replyto_thread" type="hidden" name="thread" value="'. $thread .'" />
			<input type="hidden" name="APC_UPLOAD_PROGRESS" value="'. $uniqid .'"/>
			
			<input type="text" name="email" value="" style="position:absolute;left:-9999px;" />
			
			
			<table id="postform">
				<tr>
					<td class="label"><label for="'. ($board['namefield'] == 1 ? 'name' : 'op') .'">';
					if($board['namefield'] == 1) $return .= T_('Name');
					elseif($board['namefield'] == 2) $return .= '<abbr title="tooltip;'. T_("Sets your name to OP if you are the OP of this thread") .'">'. T_('Name') .': '. T_("OP") .'</abbr>';
					$return .= '</label></td>
					<td>';
				if($board['namefield'] == 1) $return .= '<input type="text" name="name" id="name" value="'. stripslashes($postername) .'" />';
				elseif($board['namefield'] == 2) $return .= '<input type="checkbox" name="op" id="op" />';
				if(!empty($_COOKIE['noko']) AND $_COOKIE['noko'] == "on") $noko = true;
				else $noko = false;
				$return .= '</td>
				</tr>
				<tr>
					<td class="label">'. T_("Options") .'</td>
					<td>
						<div class="box first">
							<input type="checkbox" name="sage" id="sage"
							/><label for="sage"><abbr title="tooltip;'. T_("Don't bump the thread") .'">'. T_("Sage") .'</abbr></label>
						</div>
						<div class="box">
							<input type="checkbox" name="rage" id="rage"
							/><label for="rage"><abbr title="tooltip;'. T_("Show your feelings!") .'">'. T_("RAGE!") .'</abbr></label>
						</div>
						<div class="box">
							<input type="checkbox" name="love" id="love"
							/><label for="love"><abbr title="tooltip;'. T_("Show your feelings!") .'">'. T_("Love") .'</abbr></label>
						</div>
						<div class="box last">
							<input type="checkbox" name="noko" id="noko"'. ($noko ? ' checked="checked"' : '') .'
							/><label for="noko"><abbr title="tooltip;'. T_("After posting, return to the thread instead of the board") .'">'. T_("Noko") .'</abbr></label>
						</div>
					</td>
				</tr>';
				
				if($overboard) { $return .= '
				<tr>
					<td class="label"><label for="board">'. T_("Board") .'</label></td>
					<td>
						<select name="board" id="board">';
						$boards = boardnav(false, true);
						$return .= $boards;
						$return = str_replace('option value="28"', 'option value="28" selected="selected"', $return);
						$return .= '
						</select>
					</td>
				</tr>';
				}
				$return .= '
				<tr>
					<td class="label"><label for="subject">'. T_("Subject") .'</label></td>
					<td>
						<input type="text" name="subject" id="subject" maxlength="'. $cfg['subj_maxlength'] .'" />
						<input class="notsowide" type="submit" value="'. T_("Submit") .'" name="submit" id="submit" />
					</td>
				</tr>
				<tr>
					<td class="label"><label for="msg">'. T_("Message") .'</label></td>
					<td>
					<textarea name="msg" id="msg" rows="3" cols="15"></textarea><a id="expandfield" href="javascript:void(expand_msgfield());"><img src="'. $cfg['htmldir'] .'/css/img/icons/arrow.png" alt="'. T_("Widen text field") .'" title="'. T_("Widen text field") .'" /></a>
					<div class="postboxinfo" id="charsremaining"><span id="charcounter">8000</span> '. T_("characters remaining") .'</div>
					</td>
				</tr>
				<tr>
					<td class="label"><label for="files-1">'. T_("Files") .'</label></td>
					<td>
						<div id="fileinputs"><input size="52" class="fileinput" type="file" multiple="multiple" name="files[]" id="files-1" onchange="filesChanged(this);" /></div>
						<p class="postboxinfo"><abbr title="tooltip;'. T_("CTRL/Shift + click in file browser to select multiple files") .'">'. sprintf( T_("Max. %s files"), $cfg['max_files'] ) .'</abbr></p>
					</td>
				</tr>
				<tr>
					<td class="label"><label for="clearexif"><abbr title="tooltip;'. T_("Clear the EXIF-data from JPG-images.") .'">'. T_("Clear JPG EXIF") .'</abbr></label></td>
					<td><input type="checkbox" name="clearexif" id="clearexif" /></td>
				</tr>
				';
				if($cfg['allow_embeds']) {
				$return .= '
				<tr>
					<td class="label"><label for="embed">'. T_("Embed") .'</label></td>
					<td>
						<input type="text" name="embed" id="embed" />
						<select class="notsowide" name="embedtype" id="embedtype">';
						
						if( $cfg['user']['sfw'] == '1' OR $board['worksafe'] == '1' )
							$nonsfw = " WHERE `sfw` = '1'";
						else
							$nonsfw = '';
							
						$query = mysql_query("SELECT `name`, `id`, `sfw` FROM `embed_sources`". $nonsfw ." ORDER BY `name` DESC");
						while($result = mysql_fetch_assoc($query)) {
							$return .= '
							<option value="'. $result['id'] .'">'. $result['name'] . ($result['sfw'] == 1 ? '' : ' ('. T_('NSFW') .')') .'</option>';
						}
						$return .= '
						</select> <span id="embedhelp"><a href="'. $cfg['htmldir'] .'/scripts/ajax/message.php?id=embedhelp" onclick="return false;" title="ajax;'. $cfg['htmldir'] .'/scripts/ajax/message.php?id=embedhelp">'. T_("(Embed help)") .'</a></span>
					</td>
				</tr>';
				}
				$return .= '
				<tr>
					<td class="label"><label for="password"><abbr title="tooltip;'. T_("Used to delete files and posts") .'">'. T_("Password") .'</abbr></label></td>
					<td><input type="password" name="password" id="password"';
					if(!empty($cfg['user']['post_password']))
						$return .= ' value="'. $cfg['user']['post_password'] .'"';
					$return .= ' /></td>
				</tr>';
				if($cfg['user_class'] >= 1 AND $cfg['user_class'] <= 3) $return .= '
				<tr>
					<td class="label"><label for="modpost"><abbr title="tooltip;'. T_("Show the admin tag after poster name") .'">'. T_("Modpost") .'</abbr></label></td>
					<td><input type="checkbox" name="modpost" id="modpost" /></td>
				</tr>';
				$return .= '
				<tr id="sending">
					<td id="sending_td" colspan="2">
						<div id="sending_bg"></div>
						<div id="sending_text"></div>
					</td>
				</tr>

			</table>
			</fieldset>
		</form>
		<ul id="postinfo">
			<li>';
			
			$get = mysql_query("SELECT GROUP_CONCAT(`extension` SEPARATOR ', ') AS `types` FROM `filetypes`");
			$types = mysql_result($get, 0, 'types');
			$return .= sprintf(T_("Allowed file types are: %s"), $types);

			$onlinecount = get_online_count(true);
			if($onlinecount == 1)
				$onlinetxt = sprintf(T_("There is %s user online."), $onlinecount);
			else
				$onlinetxt = sprintf(T_("There are %s users online."), $onlinecount);
			
			$loads = '';
			if($cfg['show_loads'])
			{
				$loads .= '<li>';
				$i = 0;
				foreach( $cfg['snmp_hosts'] AS $name => $ip )
				{
					if( $i != 0 )
						$loads .= ' - ';
						
					$load = getServerLoad($ip, $cfg['snmp_domain']);
					$loads .= $name .': '. $load;
					++$i;
				}
				$loads .= '</li>';
			}
		
			//<li>'. $onlinetxt .' <a href="#">'. T_("More statistics") .'</a></li>
			$return .= '</li>
			<li>'. sprintf(T_("Max allowed file size is %s."), convert_filesize($cfg['max_filesize'])) .'</li>
			<li>'. $onlinetxt .'</li>
			'. $loads .'
			<li><a href="'. $cfg['htmldir'] .'/'. $board['url'] .'/threadlist/">'. T_("Show thread list") .'</a></li>
		</ul>
		</div>';
		
		$adq = mysql_query("SELECT `content` FROM `ads` WHERE `category` = '". $board['ad_category'] ."' LIMIT 1");
		if( mysql_num_rows( $adq ) == 1 )
		{
			$ad = mysql_fetch_assoc( $adq );
			if( !empty( $ad['content'] ) )
			{
				$return .= '<div style="text-align: center; margin-top: 20px;">'. $ad['content'] .'</div>';
			}
		}
		
		$return .= '
		<hr class="line" />';
		
	return $return;
}

?>
