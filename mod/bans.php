<?php
// Northpole.fi
// Bans
// 17.2.2010
require_once("../inc/include.php");

if($cfg['user_class'] >= 1 AND $cfg['user_class'] <= 3) {

	$mod_pages = true;
	$title = T_("Bans") .', '. T_("Board administration"). $cfg['site_title'];
	include($cfg['srvdir'] ."/inc/header.php");
	echo '
		<div id="padded">';
	
	if(!empty($_GET['a']) AND $_GET['a'] == "add") {
		if(!empty($_GET['b'])) {
			$ipq = $_GET['b'];
			$ip = ' value="'. $_GET['b'] .'"';
		}
		else {
			$ip = "";
			$ipq = "";
		}
		
		if(!isset($_POST['ip']) AND !isset($_POST['ip-plain'])) {
			$q = mysql_query("SELECT `message` FROM `posts` WHERE `id` = '". mysql_real_escape_string($_GET['c']) ."' LIMIT 1");
				if(mysql_num_rows($q) != 0)
					$msg = T_("Message sent by the user:") ."\r\n". mysql_result($q, 0, "message");
				else $msg = "";
			echo '
			<h2>'. T_("Add a ban") .'</h2>
			<form action="'. $cfg['htmldir'] .'/mod/bans/add/" method="post" name="ban" id="adminform">
			<fieldset>
				<input type="hidden" name="msg" value="'. (!empty($_GET['c']) ? $_GET['c'] : '0') .'" />
			
				<legend>'. T_("IP-Address and ban type") .'</legend>
				<label for="ip">'. T_("IP-Address (SHA512-encrypted)") .':</label>
				<input type="text" name="ip" id="ip"'. $ip .' size="60" />
				
				<label for="ip">'. T_("IP-Address (Unencrypted - replaces the former field, leave empty if using it)") .':</label>
				<input type="text" name="ip-plain" id="ip-plain" />
				
				<label for="read">'. T_("Allow reading of the boards") .':</label>
				<select name="read" id="read">
					<option value="1">'. T_("Yes") .'</option>
					<option value="2">'. T_("No") .'</option>
				</select>
				
				'. (!empty($_GET['c']) ? '
				<label for="redtext">'. T_("Note to add after the message") .':</label>
				<textarea name="redtext" id="redtext" cols="50">&lt;br /&gt;&lt;br /&gt;&lt;span style="color: red; font-weight: bold;"&gt;('. T_("User was banned for this post") .')&lt;/span&gt;</textarea>'
				: '') .'
				
				<legend>'. T_("Ban length and reason") .'</legend>
				<label for="sec">'. T_("Length in seconds") .':</label>
				<input type="text" name="sec" id="sec" value="3600" />
				<div class="desc">'. T_("Choose") .':
					<a href="#" onclick="$(\'#sec\').attr(\'value\', 3600);return false;">1'. T_("h") .'</a>
					<a href="#" onclick="$(\'#sec\').attr(\'value\', 86400);return false;">1'. T_("d") .'</a>
					<a href="#" onclick="$(\'#sec\').attr(\'value\', 259200);return false;">3'. T_("d") .'</a>
					<a href="#" onclick="$(\'#sec\').attr(\'value\', 604800);return false;">1'. T_("w") .'</a>
					<a href="#" onclick="$(\'#sec\').attr(\'value\', 1209600);return false;">2'. T_("w") .'</a>
					<a href="#" onclick="$(\'#sec\').attr(\'value\', 2592000);return false;">1'. T_("m") .'</a>
					<a href="#" onclick="$(\'#sec\').attr(\'value\', 31536000);return false;">1'. T_("y") .'</a>
					<a href="#" onclick="$(\'#sec\').attr(\'value\', 0);return false;">'. T_("Permanent") .'</a>
				</div>

				<label for="reason">'. T_("Reason") .':</label>
				<textarea name="reason" id="reason" cols="50"></textarea>
				
				<label for="note">'. T_("Note to another moderators") .':</label>
				<textarea name="note" id="note" cols="50">'. htmlspecialchars(stripslashes($msg)) .'</textarea>
				
				<input type="submit" value="'. T_("Add ban") .'" name="add" id="add" />

			</fieldset>
			</form>
			
			';
			
			if($ip == "") {
				$haku = mysql_query("SELECT * FROM `bans` ORDER BY `start_time` DESC LIMIT 20");
				echo '<div class="subtitle">'. T_("Previous 20 bans") .'</div>';
			}
			else {
				$haku = mysql_query("SELECT * FROM `bans` WHERE `ip` = '". mysql_real_escape_string($ipq) ."' ORDER BY `start_time` DESC");
				echo '<div class="subtitle">'. T_("Previous bans for this user") .'</div>';
			}
			echo '
			<table class="table">
				<tr>
					<th>'. T_("Reason") .'</th>
					<th>'. T_("Note") .'</th>
					<th>'. T_("Can read") .'</th>
					<th>'. T_("Added") .'</th>
					<th>'. T_("Length") .' ('. T_("Expires") .')</th>
					<th>'. T_("Expired/Deleted") .'</th>
					<th>'. T_("Banned by") .'</th>
				</tr>';
			
			$i = 0;
			while($b = mysql_fetch_assoc($haku)) {
				echo '
				<tr>
					<td>'. stripslashes($b['reason']) .'</td>
					<td>'. regulateWords($b['staff_note'], $cfg['max_word_length']) .'</td>
					<td>'. ($b['can_read'] == 1 ? T_('Yes') : T_('No')) .'</td>
					<td>'. date(T_("Y/m/d g:i:s A"), $b['start_time']) .'</td>
					<td>'. $b['length'] . T_("sec") .' ('. ($b['length'] != 0 ? date(T_("Y/m/d g:i:s A"), ($b['start_time'] + $b['length'])) : T_("Never")) .')</td>
					<td>'. ($b['is_old'] != 0 ? T_('Yes') : T_('No')) .'</td>
					<td>'. $b['banned_by'] .'</td>
				</tr>';
				$i++;
			}
			if($i == 0) echo '
				<tr><td colspan="8">'. T_("No bans were found.") .'</td></tr>';
			
			echo '
			</table>';
		}
		else {
		
			if(is_numeric($_POST['msg'])) $msg = mysql_real_escape_string($_POST['msg']);
			else $msg = 0;
		
			if(!empty($_POST['redtext'])) $redtext = mysql_real_escape_string($_POST['redtext']);
			else $redtext = '';
		
			if(!empty($_POST['ip'])) $ip = mysql_real_escape_string($_POST['ip']);

			if(!empty($_POST['ip-plain']) AND check_ip($_POST['ip-plain'])) $ip = mysql_real_escape_string(encrypt_ip(gethostbyaddr($_POST['ip-plain'])));

			if(empty($_POST['ip']) AND empty($_POST['ip-plain']))
				error(T_("IP-Address is missing!"), false);
			
			if(!empty($_POST['read']) AND $_POST['read'] == 1 OR !empty($_POST['read']) AND $_POST['read'] == 2) $read = mysql_real_escape_string(($_POST['read'] == 1 ? '1' : '0'));
			else error(T_("Malformed form field"), false);
			
			if(!empty($_POST['sec']) AND is_numeric($_POST['sec'])) $sec = mysql_real_escape_string($_POST['sec']);
			else $sec = 0;
			
			if(!empty($_POST['reason'])) $reason = mysql_real_escape_string($_POST['reason']);
			else error(T_("Ban reason is missing!"), false);
			
			if(!empty($_POST['note'])) $note = mysql_real_escape_string($_POST['note']);
			else $note = "";
			
			$add = mysql_query("INSERT INTO `bans`(`ip`, `can_read`, `start_time`, `length`, `reason`, `staff_note`, `banned_by`) VALUES ('". $ip ."', '". $read ."', UNIX_TIMESTAMP(), '". $sec ."', '". $reason ."', '". $note ."', '". $cfg['mod_name'] ."')");
			if($add) {
				mysql_query("UPDATE `posts` SET `message` = CONCAT(`message`, '". $redtext ."') WHERE `id` = '". $msg ."' LIMIT 1");
				info(T_("Ban added!"), false);
			}
			else error(T_("Adding the ban failed!"), false);
		}
	}
	elseif($_GET['a'] == "remove") {
		if(!empty($_GET['b']) AND is_numeric($_GET['b'])) {
			$juu = mysql_query("UPDATE `bans` SET `is_old` = '1' WHERE `id` = '". mysql_real_escape_string($_GET['b']) ."' LIMIT 1");
			if($juu) info(T_("Ban removed!"), false);
			else error(T_("Removing the ban failed!"), false);
		}
	}
	else {
		if(empty($_GET['a'])) {
			$haku = mysql_query("SELECT * FROM `bans` WHERE `is_old` = '0' ORDER BY `start_time` DESC LIMIT 100");
			echo '<h2>'. T_("All valid bans") .'</h2>';
		}
		elseif($_GET['a'] == "expired") {
			$haku = mysql_query("SELECT * FROM `bans` WHERE `is_old` = '1' ORDER BY `start_time` DESC LIMIT 100");
			echo '<h2>'. T_("Last 100 expired bans") .'</h2>';
		}
		
		echo '
		<table class="table">
			<tr>
				<th>'. T_("Reason") .'</th>
				<th>'. T_("Note") .'</th>
				<th>'. T_("Can read") .'</th>
				<th>'. T_("Added") .'</th>
				<th>'. T_("Length") .' ('. T_("Expires") .')</th>
				<th>'. T_("Banned by") .'</th>'.
				(empty($_GET['a']) ? '
				<th>'. T_("Remove") .'</th>' : '') .'
			</tr>';
		
		$i = 0;
		while($b = mysql_fetch_assoc($haku)) {
			echo '
			<tr>
				<td>'. stripslashes($b['reason']) .'</td>
				<td>'. regulateWords($b['staff_note'], $cfg['max_word_length']) .'</td>
				<td>'. ($b['can_read'] == 1 ? T_('Yes') : T_('No')) .'</td>
				<td>'. date(T_("Y/m/d g:i:s A"), $b['start_time']) .'</td>
				<td>'. $b['length'] . T_("sec") .' ('. ($b['length'] != 0 ? date(T_("Y/m/d g:i:s A"), ($b['start_time'] + $b['length'])) : T_('Never')) .')</td>
				<td>'. $b['banned_by'] .'</td>'.
				(empty($_GET['a']) ? '
				<td><a href="'. $cfg['htmldir'] .'/mod/bans/remove/'. $b['id'] .'">'. T_("Remove") .'</a></td>' : '') .'
			</tr>';
			$i++;
		}
		if($i == 0) echo '
			<tr><td colspan="8">'. T_("No bans were found.") .'</td></tr>';
		
		echo '
		</table>';
	}
	echo '
			</div>
		</div>';
	include($cfg['srvdir'] ."/inc/footer.php");
}
else header("Location: ". $cfg['htmldir']);
?>
