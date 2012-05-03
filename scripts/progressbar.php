<?php
// Northpole.fi
// Tiedostolähetyksen tila, haetaan AJAXilla
// 26.2.2010

include("../inc/config.php");

if(isset($_GET['key'])) {

	header('Expires: Tue, 08 Oct 1991 00:00:00 GMT');
	header('Cache-Control: no-cache, must-revalidate');

	if($cfg['use_apc']) {
		$status = apc_fetch('upload_'.$_GET['key']);
		if($status['total'] != 0)
			$status['percent'] = round($status['current']/$status['total']*100, 2);
		else $status['percent'] = 0;
		$status['total_kb'] = round($status['total']/1024);
		$status['current_kb'] = round($status['current']/1024);
		if(strlen($status['filename']) > 40) {
			$status['filename_short'] = mb_substr($status['filename'], 0, 40) ."...";
		}
		else $status['filename_short'] = $status['filename'];
	}
	else {
		$status = array("percent" => 50);
	}
	echo json_encode($status);
}
?>
