<?php
// Northpole.fi
// Bannattu käyttäjä ohjataan tälle sivulle
// 15.02.2010

require_once("inc/include.php");

$ip = mysql_real_escape_string(encrypt_ip(get_ip()));

// Koskaan ei voi olla liian varma...
$haeip = mysql_query("SELECT * FROM `bans` WHERE `ip` = '". $ip ."' AND `is_old` = '0' ORDER BY `start_time` DESC LIMIT 1");
if(mysql_num_rows($haeip) != 0) {
	$banned = true;
	$ban = mysql_fetch_assoc($haeip);
}
else $banned = false;

if(!empty($cfg['user']['uid']) AND empty($banned)) {
	$session = mysql_real_escape_string($cfg['user']['uid']);
	$getcookie = mysql_query("SELECT * FROM `bans` WHERE `uid` = '". $session ."' AND `uid` != '' AND `is_old` = '0' ORDER BY `start_time` DESC LIMIT 1");
	if(mysql_num_rows($getcookie) != 0) {
		$banned = true;
		$ban = mysql_fetch_assoc($getcookie);
	}
	else $banned = false;
}
else
	$session = false;

if(!$banned) {
	header("Location: ". $cfg['htmldir']);
	die();
}

// Vanhennetaan banni
if(($ban['start_time'] + $ban['length']) <= time() AND $ban['length'] != '0') {
	mysql_query("UPDATE `bans` SET `is_old` = '1' WHERE `id` = '". $ban['id'] ."' LIMIT 1");
	$ban['is_old'] = 1;
}

$title = ($ban['is_old'] == 0 ? T_('You are banned') : T_('You were banned')) .'!'. $cfg['site_title'];
$header_min = true;
include("inc/header.php");

echo '
<div id="banned">
	<div class="banbar">'. ($ban['is_old'] == 0 ? T_('You are banned') : T_('You were banned')) .'!</div>
	<img style="background-color: white;" src="'. $cfg['htmldir'] .'/css/img/'. ($ban['is_old'] == 1 ? 'not_banned.jpg' : 'ohyeahyoumad1.gif') .'" alt="'. T_('Banned!') .'" id="banimage" />
	<p>'.
	sprintf(T_("%s from posting to the board%s for the following reason:"),
		($ban['is_old'] == 0 ? T_('You are blocked') : T_('You were blocked')),
		($ban['can_read'] == 0 ? T_(" and reading it") : '')
	)
	.'</p>
	<p><strong>'. (!empty($ban['reason']) ? stripslashes($ban['reason']) : T_('No reason given')) .'</strong></p>
	<p>'. 
	sprintf(T_("Your ban was placed at <strong>%s</strong> and %s"),
		date(T_("Y/m/d g:i A"), $ban['start_time']),
		($ban['is_old'] == 0 ?
			($ban['length'] != 0 ?
				sprintf(T_('it will expire at <strong>%s</strong>.'), date(T_("Y/m/d g:i A"), ($ban['start_time'] + $ban['length'])))
				:
				T_('it <strong>will not expire.</strong>')
			)
			:
			T_('it <strong>has now expired.</strong>')
		)
	)
	.'</p>
	<p>'. sprintf(T_('Your encrypted IP-address was:<br /><strong>%s</strong>'), (!empty($ban['ip']) ? wordwrap($ban['ip'], 32, " ", true) : T_("(Unknown)")) ) .'</p>';

	echo '
	<p>'. sprintf(T_('If you think you have been banned inadvertently or you want to appeal against your ban, please contact the board administration at %s.'), $cfg['admin_email']) .'</p>';
	
$getban = mysql_query("SELECT * FROM `bans` WHERE (`ip` = '". $ip ."' OR `uid` = '". $session ."') AND `uid` != '' ORDER BY `start_time` DESC LIMIT 1, 10");
if(mysql_num_rows($getban) != 0) echo '
	<h4>'. T_("Your earlier bans") .'</h4>';
while($bans = mysql_fetch_assoc($getban)) {
	echo '
	<p>'.
	sprintf(T_("%s, <strong>length:</strong> %s, <strong>reason:</strong> %s, <strong>banned by:</strong> %s%s"),
		date(T_("Y/m/d g:i A"), $bans['start_time']),
		($bans['length'] != 0 ? $bans['length'].T_('sec') : T_('permanent')),
		(!empty($bans['reason']) ? $bans['reason'] : T_('No reason given')),
		($bans['is_old'] == 1 ? ' <strong>'. T_('(Expired)') .'</strong> ' : '')
	)
	.'</p>';
}
	if($ban['is_old'] == 1) echo '
	<p><a href="'. $cfg['htmldir'] .'">'. T_("Return to front page") .'</a></p>';
	echo '
	<div class="banbar"></div>
</div>
</body>
</html>';

?>
