<?php
// A simple script to fool the browser into a fake filename
// Northpole.fi
// 25.0.2010 <-- What!?

include("../inc/functions_db.php");
dbconnect(true);

$file = mysql_real_escape_string($_GET['file']);
if( !is_numeric( $file ) )
{
	header("Location: ". $cfg['htmldir'] ."/404/");
	die();
}

$q = mysql_query("SELECT * FROM `files` WHERE `name` = '". $file ."' LIMIT 1");
$file = mysql_fetch_assoc($q);

// Force SVG mime
if( $file['extension'] == "svg" AND $file['mime'] == "text/plain" )
	$file['mime'] = "image/svg+xml";

$sauce = $cfg['srvdir'] ."/files/". $file['folder'] ."/orig/". $file['name'] .".". $file['extension'];

if( is_file( $sauce ) )
{

	// Let the browser cache the page with the help of ETag-header.
	$hash = $file['md5'];
	$headers = getallheaders();
	// if Browser sent ID, we check if they match
	if( !empty( $headers['If-None-Match'] ) AND ereg( $hash, $headers['If-None-Match'] ) )
	{
		header('HTTP/1.1 304 Not Modified');
	}
	else
	{
		header("ETag: \"". $hash ."\"");
		header("Content-Type: ". $file['mime']);
		header("Content-Length: ". $file['size']);

		if( $file['mime'] != 'text/plain' )
			readfile($sauce);
		else
		{
			$sauce = file_get_contents($sauce);
			echo utf8_encode($sauce);
		}
		
	}

}
else {
	header("HTTP/1.1 404 Not Found");
	header("Location: ". $cfg['htmldir'] ."/404/");
	die();
}

?>
