<!DOCTYPE html>
<html lang="<?php echo $cfg['local_country_code']; ?>">
<head>
<meta http-equiv="content-type" content="text/html; charset=utf-8" />
<meta name="robots" content="index, follow, noarchive" />
<meta name="keywords" content="<?php echo $cfg['meta_keywords']; ?>" />
<meta name="description" content="<?php echo stripslashes($title) . $cfg['meta_common_desc']; ?>" />

<?php
if(!empty($metarefresh))echo '
<meta http-equiv="refresh" content="'. $metarefresh .'" />';
?>

<link rel="shortcut icon" href="<?php echo $cfg['htmldir']; ?>/favicon.ico" type="image/x-icon" />
<?php

if(empty($board) OR !is_array($board) OR empty($board['default_style']) OR !array_key_exists($board['default_style'], $cfg['themes']))
{
	if(!empty($i) AND !empty($i['default_style']) AND !empty($i['default_style']))
	{
		$defaultstyle = $i['default_style'];
		$hidesidebar = $i['hide_sidebar'];
	}
	else
	{
		$defaultstyle = $cfg['default_theme'];
		$hidesidebar = '0';
	}
}
else
{
	$defaultstyle = $board['default_style'];
	$hidesidebar = $board['hide_sidebar'];
}

if(empty($cfg['user']['site_style']) OR $cfg['user']['site_style'] == '0' AND !empty($defaultstyle) AND array_key_exists($defaultstyle, $cfg['themes']))
	$stylesheet = $defaultstyle;
else
	$stylesheet = $cfg['user']['site_style'];

echo '
<link rel="stylesheet" id="stylesheet" type="text/css" media="screen, projection" href="'. $cfg['htmldir'] .'/css/css.php?name='. $stylesheet .'" title="'. $stylesheet .'" />';

if($cfg['user']['show_sidebar'] == '1' OR ($cfg['user']['show_sidebar'] == '2' AND $hidesidebar == '0'))
	$show_sidebar = true;
else
	$show_sidebar = false;
	
if(!$show_sidebar) echo '
<style type="text/css">
<!--
#left { display: none; }
#right { margin: 0 5px; }
#hide_sidebar { left: 0; }
-->
</style>';
?>

<script type="text/javascript" src="<?php echo $cfg['htmldir']; ?>/js/js.php"></script>

<title><?php echo stripslashes($title); ?></title>

<?php
// Admin messages to users
$adminmsgq = mysql_query("SELECT * FROM `admin_messages` WHERE `receiver` = '". $cfg['user']['uid'] ."' AND `is_read` = '0' LIMIT 1");

if(mysql_num_rows($adminmsgq) == 1)
{
	$adminmsg = mysql_fetch_assoc($adminmsgq);
	echo '
	<script type="text/javascript">alert("'. T_('Message from a moderator') .'\r\n'. T_('Sent') .': '. date(T_("Y/m/d g:i:s A"), $adminmsg['sent']) .'\r\n\r\n'. str_replace("\r\n", '\r\n', $adminmsg['message']) .'");</script>';
	mysql_query("UPDATE `admin_messages` SET `is_read` = '1' WHERE `id` = '". $adminmsg['id'] ."' LIMIT 1");
}
?>

</head>
<body>

<?php

// Notify the user if cookies or javascript is disabled.
echo '
<noscript><div class="infobar">'. T_("This page requires JavaScript to run properly. Please configure your browser to allow JavaScript for this site.") .'</div></noscript>';

if( empty( $_COOKIE['testcookie'] ) ) echo '
<div class="infobar" id="cookiewarning">'. T_("Cookies are required to have your personalized settings saved and for some functions to work. Please configure your browser to allow cookies for this site.") .'</div>';

if(empty($header_min)) $header_min = false;

if(!$header_min) {
echo '
<div id="wrapper">';

if(empty($mod_pages) OR !$mod_pages)
	$sidebar = $cfg['srvdir'] ."/inc/sidebar.php";
else
	$sidebar = $cfg['srvdir'] ."/mod/mod_sidebar.php";

include($sidebar); // Vasen valikkopalkki

}
echo '
	<div id="right">
	'. $cfg['banned_info'] .'
	<div class="boardnav">
		[ <a href="'. $cfg['htmldir'] .'/personal/followed/">'. T_("Followed") .'</a> |
		<a href="'. $cfg['htmldir'] .'/personal/mythreads/">'. T_("My threads") .'</a> |
		<a href="'. $cfg['htmldir'] .'/personal/repliedthreads/">'. T_("Replied") .'</a> ]
		<div style="float: right;">
			[ <a href="'. $cfg['htmldir'] .'/">'. T_('Front page') .'</a> |
			<a href="'. $cfg['htmldir'] .'/search/">'. T_('Message search') .'</a> |
			<a href="'. $cfg['htmldir'] .'/settings/">'. T_("Site personalization") .'</a> ]
		</div>
		<br />'. boardnav() .'
	</div>';
?>