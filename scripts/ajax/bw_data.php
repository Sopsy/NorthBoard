<?php
header('Content-Type: application/json; charset=utf-8');

$min = true;
$nostatsupdate = true;
require_once("../../inc/include.php");

$time = time() - 86400; // 24 hours
$getposts = mysql_query("SELECT `geoip_country_code`, `geoip_lat`, `geoip_lon`, `proxy` FROM `posts` WHERE `time` > '". $time ."' GROUP BY `ip_plain`");

$locations = array();
while( $post = mysql_fetch_assoc($getposts) )
{
	if($post['proxy'] == '1')
		$post['geoip_country_code'] = 'prx';
	elseif($post['proxy'] == '2')
		$post['geoip_country_code'] = 'tor';

	$post['geoip_lat'] = str_replace( ',', '.', round( $post['geoip_lat'], 2 ) );
	$post['geoip_lon'] = str_replace( ',', '.', round( $post['geoip_lon'], 2 ) );
		
	$key = $post['geoip_lat'] .'|'. $post['geoip_lon'];
	if( !array_key_exists( $key, $locations ) )
		$locations[$key] = array(
			"src" => $cfg['htmldir'] . '/css/img/flags/' . strtolower($post['geoip_country_code']) . '.png',
			"count" => 1
		);
	else
		++$locations[$key]['count'];
}
$newLoc = array();
foreach( $locations AS $coords => $location ) {
	$coords = explode('|', $coords);
	$newLoc[] = array(
		'src' => $location['src'],
		'lat' => $coords[0],
		'lon' => $coords[1],
		'count' => $location['count']
	);
}
$locations = $newLoc;

shuffle($locations);
$locations = array("data" => $locations);
echo json_encode($locations);
