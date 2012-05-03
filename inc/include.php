<?php
// Northpole.fi
// Tämä tiedosto sisältää joka latauksella tehtäviä toimintoja ja yhdistää useammat funktioluokat yhteen tiedostoon ettei kaikkia tarvitse sisällyttää erikseen.
// 6.1.2009


function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    $usec = str_replace("0.", "", $usec);
    $usec = substr($usec, 0, -2);
    return $sec . $usec;
}
define('STARTTIME', microtime_float());

require_once("config.php");

$load = sys_getloadavg();
if( $load[0] > $cfg['load_max'] )
{
	header('HTTP/1.1 503 Too busy, try again later');
	// WHY DON'T I SEE THIS MESSAGE!?
	echo '<strong>HTTP/1.1 503 Too busy, try again later!</strong><br /><br />' . sprintf(T_("The server is too busy at the moment. Please try again later.<br />Current load is %s, the limit is %s."), $load[0], $cfg['load_max']);
	die();
}

if( empty($min) )
	$min = false;

if( empty($nostatsupdate) )
	$nostatsupdate = false;

require_once("functions_db.php");
dbconnect();
require_once("functions_general.php");
require_once("functions_essential.php");
require_once("functions_statistics.php");

load_user($nostatsupdate);
load_locale();

if(!$min) {
	require_once("functions_posts.php");
	require_once("functions_html.php");
	require_once("functions_upload.php");
	require_once("functions_bans.php");
	require_once("functions_text.php");
	require_once("functions_mod.php");

	if(!defined("INITIALIZED"))
		initialize();

	if( substr(get_ip(), -9) == '.keuda.fi' )
		die("<h1>Pääsy evätty</h1><p>Keudan verkko on estetty selaamasta tätä sivustoa jatkuvan aktiivisen häiriköinnin vuoksi.</p><p>Lisätietoja voi udella Keudan oppilaalta Samu \"arska-testo\" Lahdenperä.</p>");
}
?>
