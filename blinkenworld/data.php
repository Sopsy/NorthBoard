<?php
header('Content-Type: application/json; charset=utf-8');
//header('Content-Type: text/plain; charset=utf-8');

$min = true;
$nostatsupdate = true;
require_once("../inc/include.php");

$time = time() - 43200; // 43200 seconds = 12 hours
$getposts = mysql_query("SELECT `geoip_country_code`, `geoip_lat`, `geoip_lon`, `proxy` FROM `posts` WHERE `time` > '". $time ."' GROUP BY `ip_plain` ORDER BY `time` DESC");

$locations = array();
while( $post = mysql_fetch_assoc($getposts) )
{
	if($post['proxy'] == '1')
		$post['geoip_country_code'] = 'prx';
	elseif($post['proxy'] == '2')
		$post['geoip_country_code'] = 'tor';

	$locations[] = array("src" => strtolower($post['geoip_country_code']), "lat" => $post['geoip_lat'], "lon" => $post['geoip_lon']);
}

shuffle($locations);
$locations = array("data" => $locations);
print json_encode($locations);
?>
