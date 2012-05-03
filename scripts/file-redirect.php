<?php
// Northpole.fi
// Ohjataan linkitetystä tiedostosta kyseiseen postaukseen
// 21.3.2010

$min = true;
$nostatsupdate = true;
require("../inc/include.php");

if(!empty($_GET['s'])) {
	$filu = $_GET['s'];
	$filu = explode("/", $filu);
	$filu = array_reverse($filu);
	$filu = $filu[1];
	$filu = mysql_real_escape_string($filu);
	$qa = mysql_query("SELECT `id` FROM `files` WHERE `name` = '". $filu ."' LIMIT 1");
}
elseif(!empty($_GET['id']) AND is_numeric($_GET['id'])) {
	$qa = mysql_query("SELECT `id` FROM `files` WHERE `id` = '". mysql_real_escape_string($_GET['id']) ."' LIMIT 1");
}
if(mysql_num_rows($qa) != 0) {
	$fid = mysql_result($qa, 0, "id");
	$qb = mysql_query("SELECT `board`, `id`, `thread` FROM `posts` WHERE `deleted_time` = '0' AND `file` = '". $fid ."' OR `file` LIKE '". $fid .",%' OR `file` LIKE '%,". $fid .",%' OR `file` LIKE '%,". $fid ."' LIMIT 1");
	if(mysql_num_rows($qb) != 0) {
		$r = mysql_fetch_assoc($qb);
		$qc = mysql_query("SELECT `url` FROM `boards` WHERE `id` = '". $r['board'] ."' LIMIT 1");
		if(mysql_num_rows($qc) == 1) {
			$r['board'] = mysql_result($qc, 0, "url");
			if($r['thread'] == 0) $r['thread'] = $r['id'];

			$tpagesq = mysql_query("SELECT COUNT(`id`) AS 'count' FROM `posts` WHERE `deleted_time` = '0' AND `thread` = '". $r['thread'] ."' OR `id` = '". $r['thread'] ."'");
			$amount = mysql_result($tpagesq, 0, "count");
			$tmsg_page = array();
			for($i = 0; $i != mysql_result($tpagesq, 0, "count"); $i++) {
				$tmp = ceil((mysql_result($tpagesq, 0, "count") - $i) / $cfg['ppp']);
				if($tmp < 1) $tmp = 1;
				$tmsg_page[] = $tmp;
			}
			$tmsg_page = array_reverse($tmsg_page);
			$q = mysql_query("SELECT COUNT(`id`) AS 'count' FROM `posts` WHERE `deleted_time` = '0' AND `thread` = '". $r['thread'] ."' AND `id` < '". $r['id'] ."'");
			$pagecount = mysql_result($q, 0, "count");
			$page = $tmsg_page[$pagecount];

			if($page <= 0) $page = 1;

			if($page != 1)
				$page = '-'. $page;
			else
				$page = '';

			header("Location: ". $cfg['htmldir'] ."/". $r['board'] ."/". $r['thread'] . $page ."/#hl_". $r['id']);
		}
		else {
			header("Location: ". $cfg['htmldir']);
			die();
		}
	}
	else {
		header("Location: ". $cfg['htmldir']);
		die();
	}
}
else {
	header("Location: ". $cfg['htmldir']);
	die();
}

?>
