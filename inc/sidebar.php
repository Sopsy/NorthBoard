<?php
	echo '
	<div id="left">
		<div id="logo"><a href="'. $cfg['htmldir'] .'"><img src="'. $cfg['htmldir'] .'/css/img/northpole_170px.png" alt="logo" /></a></div>
		<h1>'. $cfg['site_name'] .'</h1>

		<ul id="ul_upper">
			<li><a href="'. $cfg['htmldir'] .'/">'. T_("Front page") .'</a></li>
			<li><a href="'. $cfg['htmldir'] .'/search/">'. T_("Message search") .'</a></li>
			<li><a href="'. $cfg['htmldir'] .'/settings/">'. T_("Site personalization") .'</a></li>';
			
		echo '
		</ul>';
		
		$q = mysql_query('SELECT `name`, `address` FROM `sidebar_links` ORDER BY `order`, `name` ASC');
		if(mysql_num_rows($q) > 0) {
			echo '
		<ul>';
			while($row = mysql_fetch_assoc($q)) {
				echo '
			<li><a href="'. $row['address'] .'">'.$row['name'].'</a></li>';
			}
			echo '
		</ul>';
		}
		
		if($cfg['user_class'] != 0) {
		echo '
		<ul>
			<li class="header">'. T_("You are logged in") .'</li>
			<li>'. T_("Class:") .' '. ($cfg['user_class'] == 1 ? T_('Admin') : '') . ($cfg['user_class'] == 2 ? T_('Super Moderator') : '') . ($cfg['user_class'] == 3 ? T_('Moderator') : '') .'</li>
			<li><a href="'. $cfg['htmldir'] .'/mod/index/">'. T_("Administration") .'</a></li>
			<li><a href="'. $cfg['htmldir'] .'/mod/logout/">'. T_("Logout") .'</a></li>';
			$q = mysql_query("SELECT COUNT(`id`) AS 'count' FROM `reports`");
			$reports = mysql_result($q, 0, "count");
			if($reports != 0) echo '
			<li><a href="'. $cfg['htmldir'] .'/mod/messages/reports/"><strong>'. sprintf(T_("%s unchecked reports!"), $reports) .'</strong></a></li>';
		echo '
		</ul>';
		
		}
		echo '
		<ul>
			<li class="header">'. T_("Boards") .'</li>
			<li><a href="'. $cfg['htmldir'] .'/personal/followed/">'. T_("Followed threads") .'</a></li>
			<li><a href="'. $cfg['htmldir'] .'/personal/mythreads/">'. T_("My threads") .'</a></li>
			<li><a href="'. $cfg['htmldir'] .'/personal/repliedthreads/">'. T_("Replied threads") .'</a></li>
		</ul>';
		echo boardnav(true);
		
		echo '
		<p id="software">
		<a href="https://northpole.fi/northboard/">NorthBoard v'. $cfg['version'] .'</a></p>
	</div>
	<div id="hide_sidebar">
		';
		if($cfg['user']['show_sidebar'] == 1)
			echo '<a class="hide_link" title="'. T_("Hide sidebar") .'" href="javascript:void(hide_element(\'sidebar\', \'hide\'));"></a>';
		else 
			echo '<a class="show_link" title="'. T_("Show sidebar") .'" href="javascript:void(hide_element(\'sidebar\', \'show\'));"></a>';
		echo '
	</div>';
?>
