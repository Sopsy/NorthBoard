<?php
// Northpole.fi
// Loading and caching the CSS files
// 11.3.2010

// Load settings only
include("../inc/functions_db.php");
dbconnect(true);

require_once '../inc/functions_general.php';

if(empty($_GET['name'])) $name = $cfg['default_theme'];
else $name = addslashes($_GET['name']);

if(!array_key_exists($name, $cfg['themes'])) $name = $cfg['default_theme'];

if($cfg['use_tmpfs'])
	$csscache = "/dev/shm/";
else
	$csscache = "../tmp/";

$csscache .= "nb_csscache_". $cfg['themes'][$name] ."";

// This is here to remove the cache file so we don't need to do it manually after every update.
// This line should be removed when going stable.
//@unlink($csscache);

if(!is_file($csscache)) {

	$file = $cfg['srvdir'] ."/css/". $cfg['themes'][$name];
	if(is_file($file)) {
		$css = @file_get_contents($file);
		if($cfg['minimize_css']) {
			require_once($cfg['srvdir'] .'/inc/compressors/csscompressor.php');
			$cssCompressor = new CSSCompressor($css);
			$css = $cssCompressor->pack();
			$css = str_replace("; ", ";", $css);
			$css = str_replace(": ", ":", $css);
			$css = str_replace(" {", "{", $css);
		}

		$file = fopen($csscache, 'w') OR error(T_("Opening the CSS cache file has failed!"));
		fwrite($file, $css);
		fclose($file);
	}
}
else {
	$css = file_get_contents($csscache);
}

$hash = md5($css);
$headers = getallheaders();
// if Browser sent ID, we check if they match
if(!empty($headers['If-None-Match']) AND ereg($hash, $headers['If-None-Match'])) {
	header('HTTP/1.1 304 Not Modified');
}
else {
	header("ETag: \"". $hash ."\"");
	header("Content-type: text/css");

	if($cfg['gzip_css']) {
		ob_start('ob_gzhandler'); //start output buffering using gzip
	}
	echo $css;
	if($cfg['gzip_css']) {
		ob_end_flush();
	}
}
?>
