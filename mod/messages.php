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
		$q = mysql_query("SELECT * FROM `posts` WHERE `deleted_time` = '0' ORDER BY `time` DESC LIMIT 100");
		echo '<h2>'. T_("100 newest posts") .'</h2>';
		
		echo '
		<table class="table">
			<tr>
				<th>'. T_("ID") .'</th>
				<th>'. T_("Board-ID") .'</th>
				<th>'. T_("Thread") .'</th>
				<th>'. T_("File-ID") .'</th>
				<th>'. T_("Name") .'</th>
				<th>'. T_("Message") .'</th>
				<th>'. T_("Sent") .'</th>
				<th>'. T_("Remove") .'</th>
				<th>'. T_("Remove all") .'</th>
			</tr>';
		
		$i = 0;
		while($b = mysql_fetch_assoc($q)) {
			echo '
			<tr>
				<td><a href="'. $cfg['htmldir'] .'/redirect/'. $b['id'] .'" title="ajax;'. $cfg['htmldir'] .'/scripts/ajax/message.php?id='. $b['id'] .'">'. $b['id'] .'</a></td>
				<td>'. getBoardUrlByID($b['board']) .'</td>
				<td>'. ($b['thread'] != 0 ? '<a href="'. $cfg['htmldir'] .'/redirect/'. $b['thread'] .'" title="ajax;'. $cfg['htmldir'] .'/scripts/ajax/message.php?id='. $b['thread'] .'">'. $b['thread'] .'</a>' : '<a href="'. $cfg['htmldir'] .'/redirect/'. $b['id'] .'">'. $b['id'] .'</a>') .'</td>
				<td>'. str_replace(",", ", ", $b['file']) .'</td>
				<td>'. $b['name'] . $b['tripcode'] .'</td>
				<td>'. nl2br(stripslashes($b['message'])) .'</td>
				<td>'. date(T_("Y/m/d g:i:s A"), $b['time']) .'</td>
				<td><a href="'. $cfg['htmldir'].'/mod/messages/delete/'. $b['id'] .'">Poista</a></td>
				<td><a href="'. $cfg['htmldir'].'/mod/messages/deleteallbyiphash/'. $b['ip'] .'">Poista kaikki</a></td>
			</tr>';
			$i++;
		}
		if($i == 0) echo '
			<tr><td colspan="9">'. T_("No messages were found") .'</td></tr>';
		
		echo '
		</table>';
	}
	elseif($_GET['a'] == "reports") {
		if(!empty($_GET['b']) AND is_numeric($_GET['b'])) {
			$del = mysql_real_escape_string($_GET['b']);
			mysql_query("DELETE FROM `reports` WHERE `id` = '". $del ."' LIMIT 1");
		}
		$haku = mysql_query("SELECT * FROM `reports` ORDER BY `time` DESC LIMIT 100");
		echo '<h2>'. T_("Reported messages") .'</h2>';
		
		echo '
		<table class="table">
			<tr>
				<th>'. T_("ID") .'</th>
				<th>'. T_("Reason") .'</th>
				<th>'. T_("Time") .'</th>
				<th>'. T_("Checked") .'</th>
			</tr>';
		
		$i = 0;
		while($b = mysql_fetch_assoc($haku)) {
			echo '
			<tr>
				<td><a href="'. $cfg['htmldir'] .'/redirect/'. $b['message'] .'" title="ajax;'. $cfg['htmldir'] .'/scripts/ajax/message.php?id='. $b['message'] .'">'. $b['message'] .'</a></td>
				<td>'. $b['reason'] .'</td>
				<td>'. date(T_("Y/m/d g:i:s A"), $b['time']) .'</td>
				<td><a href="'. $cfg['htmldir'] .'/mod/messages/reports/'. $b['id'] .'">'. T_("Checked") .'</a></td>
			</tr>';
			$i++;
		}
		if($i == 0) echo '
			<tr><td colspan="9">'. T_("No messages were found") .'</td></tr>';
		
		echo '
		</table>';
	}
	elseif($_GET['a'] == "locked") {
		$q = mysql_query("SELECT * FROM `posts` WHERE `locked` = '1' ORDER BY `time` DESC");
		echo '<h2>'. T_("Locked threads") .'</h2>';
		
		echo '
		<table class="table">
			<tr>
				<th>'. T_("ID") .'</th>
				<th>'. T_("Board-ID") .'</th>
				<th>'. T_("Message") .'</th>
				<th>'. T_("Unlock") .'</th>
			</tr>';
		
		$i = 0;
		while($b = mysql_fetch_assoc($q)) {
			echo '
			<tr>
				<td><a href="'. $cfg['htmldir'] .'/redirect/'. $b['id'] .'">'. $b['id'] .'</a></td>
				<td>'. $b['board'] .'</td>
				<td>'. $b['message'] .'</td>
				<td><a href'. $cfg['htmldir'] .'/mod/messages/unlock/'. $b['id'] .'">'. T_("Unlock") .'</a></td>
			</tr>';
			$i++;
		}
		if($i == 0) echo '
			<tr><td colspan="4">'. T_("No messages were found") .'</td></tr>';
		
		echo '
		</table>';
	}
	elseif($_GET['a'] == "stickied") {
		$q = mysql_query("SELECT * FROM `posts` WHERE `sticky` = '1' ORDER BY `time` DESC");
		echo '<h2>'. T_("Stickied threads") .'</h2>';
		
		echo '
		<table class="table">
			<tr>
				<th>'. T_("ID") .'</th>
				<th>'. T_("Board-ID") .'</th>
				<th>'. T_("Message") .'</th>
				<th>'. T_("Unstick") .'</th>
			</tr>';
		
		$i = 0;
		while($b = mysql_fetch_assoc($q)) {
			echo '
			<tr>
				<td><a href="'. $cfg['htmldir'] .'/redirect/'. $b['id'] .'">'. $b['id'] .'</a></td>
				<td>'. $b['board'] .'</td>
				<td>'. $b['message'] .'</td>
				<td><a href'. $cfg['htmldir'] .'/mod/messages/unstick/'. $b['id'] .'">'. T_("Unstick") .'</a></td>
			</tr>';
			$i++;
		}
		if($i == 0) echo '
			<tr><td colspan="4">'. T_("No messages were found") .'</td></tr>';
		
		echo '
		</table>';
	}
	elseif($_GET['a'] == 'merge') {
		if(!isset($_POST['thread_from'])) {
			echo '
			<h2>'. T_("Merge threads") .'</h2>';
			
			echo '
			<form action="'. $cfg['htmldir'] .'/mod/messages/merge" method="post">
				'. T_("Merge this thread-ID") .':<br />
				<input type="text" name="thread_from"'. (!empty($_GET['b']) ? ' value="'. $_GET['b'] .'"' : '') .' /><br />
				'. T_("with this thread-ID") .':<br />
				<input type="text" name="thread_to" /><br />
				<input type="submit" name="submit" value="'. T_("Merge") .'" />
			</form>';
		}
		else {
			if(empty($_POST['thread_from'])) error(T_("Thread-ID is missing"));
			$t_from = mysql_real_escape_string($_POST['thread_from']);
			if(!is_numeric($t_from)) error(T_("Malformed thread-ID"));
			
			if(empty($_POST['thread_to'])) error(T_("Thread-ID is missing"));
			$t_to = mysql_real_escape_string($_POST['thread_to']);
			if(!is_numeric($t_to)) error(T_("Malformed thread-ID"));
			
			$qa = mysql_query("SELECT `bump_time` FROM `posts` WHERE `id` = '". $t_from ."' LIMIT 1");
			$qb = mysql_query("SELECT `bump_time` FROM `posts` WHERE `id` = '". $t_to ."' LIMIT 1");
			if(mysql_num_rows($qa) == 0 OR mysql_num_rows($qb) == 0)
				error(T_("One of the threads to be merged does not exist or it is not a thread."));
			
			$time_a = mysql_result($qa, 0, "bump_time");
			$time_b = mysql_result($qb, 0, "bump_time");
			
			if($time_a >= $time_b) $time = $time_a;
			else $time = $time_b;
			
			mysql_query("UPDATE `posts` SET `bump_time` = '". mysql_real_escape_string($time) ."' WHERE id = '". $t_to ."' LIMIT 1");
			
			$move = mysql_query("UPDATE `posts` SET `thread` = '". $t_to ."' WHERE `id` = '". $t_from ."' OR `thread` = '". $t_from ."'");
			if($move)
				info(T_("Threads successfully merged"), false);
			else
				error(T_("Merging the threads failed!"), false);
		}
	}
	elseif($_GET['a'] == 'move') {
		if(!isset($_POST['thread'])) {
			echo '
			<h2>'. T_("Move thread") .'</h2>';
			
			echo '
			<form action="'. $cfg['htmldir'] .'/mod/messages/move" method="post">
				'. T_("Thread-ID") .':<br />
				<input type="text" name="thread"'. (!empty($_GET['b']) ? ' value="'. $_GET['b'] .'"' : '') .' /><br />
				'. T_("Move to board") .':<br />
				<select name="board">';
				
				$haku = mysql_query("SELECT `id`, `url` FROM `boards` ORDER BY `url` ASC");
				while($l = mysql_fetch_assoc($haku)) {
					echo '
					<option value="'. $l['id'] .'">'. $l['url'] .'</option>';
				}
				
			echo '
				</select><br />
				<input type="submit" name="submit" value="'. T_("Move") .'" />
			</form>';
		}
		else {
			if(empty($_POST['thread'])) error(T_("Thread-ID is missing"));
			$thread = mysql_real_escape_string($_POST['thread']);
			if(!is_numeric($thread)) error(T_("Malformed thread-ID"));
			
			if(empty($_POST['board'])) error(T_("Board-ID is missing"));
			$board = mysql_real_escape_string($_POST['board']);
			if(!is_numeric($board)) error(T_("Malformed board-ID"));
			
			$move = mysql_query("UPDATE `posts` SET `board` = '". $board ."' WHERE `id` = '". $thread ."' OR `thread` = '". $thread ."'");
			if($move)
				info(T_("Thread is moved"), false);
			else
				error(T_("Moving the thread failed!"), false);
		}
	}
	elseif($_GET['a'] == "delete" AND !empty($_GET['b']) AND is_numeric($_GET['b'])) {
		delete_post($_GET['b'], false, false, true);
		info(T_("Message deleted!"), false);
	}
	elseif($_GET['a'] == "deleteallbyiphash" AND !empty($_GET['b']) AND ctype_alnum($_GET['b'])) {
		$count = delete_all_posts_by_ip_hash($_GET['b'], false, false, true);
		info(sprintf(T_("%s messages deleted!"), $count), false);
	}
	elseif($_GET['a'] == "lock" AND !empty($_GET['b']) AND is_numeric($_GET['b'])) {
		$q = mysql_query("UPDATE `posts` SET `locked` = '1' WHERE `id` = '". mysql_real_escape_string($_GET['b']) ."' LIMIT 1");
		if($q) info(T_("Thread locked!"), false);
		else error(T_("Locking the thread failed!"), false);
	}
	elseif($_GET['a'] == "stick" AND !empty($_GET['b']) AND is_numeric($_GET['b']) AND $cfg['user_class'] >= 1 AND $cfg['user_class'] <= 2) {
		$q = mysql_query("UPDATE `posts` SET `sticky` = '1' WHERE `id` = '". mysql_real_escape_string($_GET['b']) ."' LIMIT 1");
		if($q) info(T_("Thread stickied!"), false);
		else error(T_("Sticking the thread failed!"), false);
	}
	elseif($_GET['a'] == "unlock" AND !empty($_GET['b']) AND is_numeric($_GET['b']) AND $cfg['user_class'] >= 1 AND $cfg['user_class'] <= 2) {
		$q = mysql_query("UPDATE `posts` SET `locked` = '0' WHERE `id` = '". mysql_real_escape_string($_GET['b']) ."' LIMIT 1");
		if($q) info(T_("Thread is now unlocked!"), false);
		else error(T_("Unlocking the thread failed!"), false);
	}
	elseif($_GET['a'] == "unstick" AND !empty($_GET['b']) AND is_numeric($_GET['b']) AND $cfg['user_class'] >= 1 AND $cfg['user_class'] <= 2) {
		$q = mysql_query("UPDATE `posts` SET `sticky` = '0' WHERE `id` = '". mysql_real_escape_string($_GET['b']) ."' LIMIT 1");
		if($q) info(T_("Thread is no longer stickied!"), false);
		else error(T_("Unsticking the thread failed!"), false);
	}
	else error(T_("Function does not exist (or insufficient permissions)"), false);
}
else header("Location: ". $cfg['htmldir']);

?>
