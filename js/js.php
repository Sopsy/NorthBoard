<?php
// Northpole.fi
// JavaScript loader
// If you modify any JS-file, you need to delete the cache file (nb_jscache.js) to get it updated.
// 11.3.2010

// Load settings
$min = true;
$nostatsupdate = true;
require_once("../inc/include.php");

$js = '/* Language */
var txt_1 = "'. T_("Loading...") .'";
var txt_2 = "'. T_("Sending...") .'";
var txt_3 = "'. T_("Saving post...") .'";
var txt_4 = "'. T_("KB") .'";
var txt_5 = "'. T_("Show sidebar") .'";
var txt_6 = "'. T_("Hide sidebar") .'";
var txt_7 = "'. T_("Show postform") .'";
var txt_8 = "'. T_("Hide postform") .'";
var txt_9 = "'. T_("Your message has reached it's maximum length.<br />Any characters beyond this limit will be removed.") .'";
var txt_10 = "'. T_("characters remaining") .'";
var txt_11 = "'. T_("An error occurred!") .'";
var txt_12 = "'. T_("The requested message cannot be found on this page!") .'";
var txt_13 = "'. T_("Thread hidden. If you wish to, you can restore this thread from the 'Site Personalization' -page.") .'";
var txt_14 = "'. T_("Thread added to followed threads.") .'";
var txt_15 = "'. T_("Thread removed from followed threads.") .'";
var txt_16 = "'. T_("Replying to thread") .'";
var txt_17 = "'. T_("Undo") .'";
var txt_18 = "'. T_("Undo successful. Thread will be restored after a page reload.") .'";
var txt_19 = "'. T_("Your browser seems to be outdated or is unsupported. Please update your browser or change it into a supported one! Supported browsers are: ") .'";
var txt_20 = "'. T_("and") .'";

/* Settings and variables */
var style_cookie_duration = '. $cfg['js']['style_cookie_duration'] .';
var htmldir = "'. $cfg['htmldir'] .'";
var maxfiles = '. $cfg['max_files'] .';
var msg_max_length = '. $cfg['msg_maxlength'] .';
var msg_max_height = '. $cfg['js']['msgbox_max_height'] .';
var image_max_width = '. $cfg['js']['image_max_width'] .';
var user_id = "'. $cfg['user']['uid'] .'";
var noBrowserWarning = "'. $cfg['user']['hide_browserwarning'] .'";
var saveScroll = "'. $cfg['user']['save_scroll'] .'";

/* The bloaty JS */';

if($cfg['use_tmpfs'])
	$jscache = "/dev/shm/";
else
	$jscache = "../tmp/";

$jscache .= "nb_jscache.js";
// This is here to remove the cache file so we don't need to do it manually after every update.
// This line should be removed when going stable.
//@unlink($jscache);

if(!is_file($jscache)) {
		
	// Load JS Libraries and custom functions
	$js_static = @file_get_contents($cfg['srvdir'] .'/js/lib/jquery.js');
	$js_static .= @file_get_contents($cfg['srvdir'] .'/js/lib/jquery.elastic.js');
	$js_static .= @file_get_contents($cfg['srvdir'] .'/js/lib/jquery.tooltip.js');
	$js_static .= @file_get_contents($cfg['srvdir'] .'/js/lib/jquery.flash.js');
	$js_static .= @file_get_contents($cfg['srvdir'] .'/js/lib/jquery.scrollto.js');
	$js_static .= @file_get_contents($cfg['srvdir'] .'/js/lib/swfobject.js');
	$js_static .= @file_get_contents($cfg['srvdir'] .'/js/lib/sprintf.js');

	// Load NorthBoard JavaScripts
	$js_static .= @file_get_contents($cfg['srvdir'] .'/js/shortcut.js');
	$js_static .= @file_get_contents($cfg['srvdir'] .'/js/northboard.js');


	if($cfg['minimize_js']) {
		require_once($cfg['srvdir'] ."/inc/compressors/jsmin.php");
		$js_static = JSMin::minify($js_static);
	}

	$put = file_put_contents($jscache, $js_static);
	if(!$put)
		error(T_("Opening the JavaScript cache file has failed!"));
	
}
else {
	$js_static = file_get_contents($jscache);
}

$js .= $js_static;

$hash = md5($js);
$headers = getallheaders();
// if Browser sent ID, we check if they match
if(!empty($headers['If-None-Match']) AND ereg($hash, $headers['If-None-Match'])) {
	header('HTTP/1.1 304 Not Modified');
}
else {
	header("ETag: \"". $hash ."\"");
	header("Content-type: text/javascript");

	if($cfg['gzip_js']) {
		ob_start('ob_gzhandler'); //start output buffering using gzip
	}
	echo $js;
	if($cfg['gzip_js']) {
		ob_end_flush();
	}
}
?>
