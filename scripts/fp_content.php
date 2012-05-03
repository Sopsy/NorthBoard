<?php

$nostatsupdate = true;
if(is_file("inc/include.php"))
	require_once("inc/include.php");
else
	require_once("../inc/include.php");

if(empty($_GET['page'])) {
	$qa = mysql_query("SELECT `url` FROM `fp_categories` ORDER BY `order`, `name` ASC LIMIT 1");
	if(mysql_num_rows($qa) == 0)
		error(T_("No categories exist!"), false);
	else
		$category = mysql_result($qa, 0, "url");
}
else
	$category = mysql_real_escape_string($_GET['page']);
	
$qa = mysql_query("SELECT `id` FROM `fp_categories` WHERE `url` = '". $category ."' LIMIT 1");
if(mysql_num_rows($qa) != 0) {
	
	$qc = mysql_query("SELECT * FROM `fp_categories` ORDER BY `order`, `name` ASC");

	echo '
			<div id="fp_menu">
				<ul>';
	while($r = mysql_fetch_assoc($qc)) {
		echo '
					<li class="tab '. $r['url'] .'"><a href="'. $cfg['htmldir'] .'/?page='. $r['url'] .'" onclick="fp_page(\''. $r['url'] .'\'); return false;">'. $r['name'] .'</a></li>';
	}
	echo '
				</ul>
			</div>';


	
	$qb = mysql_query("SELECT * FROM `fp_posts` WHERE `category` = '". mysql_result($qa, 0, "id") ."' ORDER BY `time` DESC");
	while($r = mysql_fetch_assoc($qb)) {
		echo '
			<div class="news">
				<h4 class="newstitle">'. sprintf(T_('<span>%s</span>, written by %s - %s'), $r['subject'], $r['added_by'], date(T_("Y/m/d g:i A"), $r['time'])) .'</h4>
				<p>
					'. nl2br(stripslashes($r['text'])) .'
				</p>
			</div>';
	}
	if(mysql_num_rows($qb) == 0) echo '
			<h4 class="newstitle">'. T_('This category does not contain anything.') .'</h4>';
		
}
else error(T_("Category not found"), false);
?>
