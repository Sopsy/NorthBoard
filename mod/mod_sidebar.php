<?php
if(!empty($cfg['user_class']) AND $cfg['user_class'] >= 1) {
?>
	<div id="left">
		<h1><?php echo T_("Administration"); ?></h1>
		<?php
		if($cfg['user_class'] != 0) {
			$q = mysql_query("SELECT COUNT(`id`) AS 'count' FROM `reports`");
			$reportcount = mysql_result($q, 0, "count");
		echo '
		<ul>
			<li><a href="'. $cfg['htmldir'] .'/">'. T_("Return to the board") .'</a></li>
			<li><a href="'. $cfg['htmldir'] .'/mod/index/">'. T_("Notifications") .'</a></li>
			<li class="header">'. T_("You are logged in") .'</li>
			<li>'. T_("Class:") .' '. ($cfg['user_class'] == 1 ? T_('Admin') : '') . ($cfg['user_class'] == 2 ? T_('Super Moderator') : '') . ($cfg['user_class'] == 3 ? T_('Moderator') : '') .'</li>
			<li><a href="'. $cfg['htmldir'] .'/mod/my_account/'. $cfg['user']['uid'] .'">'. T_("My account") .' (TODO)</a></li>
			<li><a href="'. $cfg['htmldir'] .'/mod/logout/">'. T_("Logout") .'</a></li>
			';
			if($cfg['user_class'] == (1 OR 2)) echo '
			<li class="header">'. T_("Notifications") .' (TODO)</li>
			<li><a href="?sivu=viestit">'. T_("Manage notifications") .'</a></li>
			<li><a href="?sivu=viestit">'. T_("Add a notification") .'</a></li>
			';
			if($cfg['user_class'] == 1) echo '
			<li class="header">'. T_("Administrators") .'</li>
			<li><a href="'. $cfg['htmldir'] .'/mod/users/">'. T_("Manage administrators") .'</a></li>
			<li><a href="'. $cfg['htmldir'] .'/mod/users/add/">'. T_("Create an account") .'</a></li>
			<li><a href="'. $cfg['htmldir'] .'/mod/users/log/">'. T_("Moderation log") .' (TODO)</a></li>
			<li><a href="'. $cfg['htmldir'] .'/mod/errorlog/">'. T_("Errorlog") .'</a></li>
			';
			if($cfg['user_class'] == (1 OR 2)) echo '
			<li class="header">'. T_("Front page") .' (TODO)</li>
			<li><a href="'. $cfg['htmldir'] .'/mod/frontpage/">'. T_("Manage content") .'</a></li>
			<li><a href="'. $cfg['htmldir'] .'/mod/frontpage/add">'. T_("Add content") .'</a></li>
			<li><a href="'. $cfg['htmldir'] .'/mod/frontpage/categories/">'. T_("Manage categories") .'</a></li>
			<li><a href="'. $cfg['htmldir'] .'/mod/frontpage/categories/add">'. T_("Create a category") .'</a></li>
			';
			if($cfg['user_class'] == (1 OR 2)) echo '
			<li class="header">'. T_("Wordfilters") .' (TODO)</li>
			<li><a href="'. $cfg['htmldir'] .'/mod/wordfilters/">'. T_("Manage wordfilters") .'</a></li>
			<li><a href="'. $cfg['htmldir'] .'/mod/wordfilters/add">'. T_("Add a wordfilter") .'</a></li>
			';
			echo '
			<li class="header">'. T_("Bans") .'</li>
			<li><a href="'. $cfg['htmldir'] .'/mod/bans/">'. T_("Manage bans") .'</a></li>
			<li><a href="'. $cfg['htmldir'] .'/mod/bans/expired/">'. T_("Expired bans") .'</a></li>
			<li><a href="'. $cfg['htmldir'] .'/mod/bans/add/">'. T_("Add a ban") .'</a></li>
			';
			if($cfg['user_class'] == 1) echo '
			<li class="header">'. T_("Boards and categories") .' (TODO)</li>
			<li><a href="'. $cfg['htmldir'] .'/mod/boards/">'. T_("Manage boards") .'</a></li>
			<li><a href="'. $cfg['htmldir'] .'/mod/boards/add">'. T_("Create a board") .'</a></li>
			<li><a href="'. $cfg['htmldir'] .'/mod/categories/">'. T_("Manage categories") .'</a></li>
			<li><a href="'. $cfg['htmldir'] .'/mod/categories/add/">'. T_("Create a category") .'</a></li>
			';
			if($cfg['user_class'] == 1) echo '
			<li class="header">'. T_("Site administration") .'</li>
			<li><a href="'. $cfg['htmldir'] .'/mod/ipwhitelist/">'. T_("IP whitelist") .'</a></li>
			';
			echo '
			<li class="header">'. T_("Files") .'</li>
			<li><a href="'. $cfg['htmldir'] .'/mod/files/">'. T_("Latest uploaded files") .'</a></li>
			
			<li class="header">'. T_("Messages") .'</li>
			<li><a href="'. $cfg['htmldir'] .'/mod/messages/">'. T_("Latest messages") .'</a></li>
			<li><a href="'. $cfg['htmldir'] .'/mod/messages/reports/">'. sprintf(T_("Reports (%s)"), $reportcount) .'</a></li>
			<li><a href="'. $cfg['htmldir'] .'/mod/messages/locked/">'. T_("Locked threads") .'</a></li>
			<li><a href="'. $cfg['htmldir'] .'/mod/messages/stickied/">'. T_("Stickied threads") .'</a></li>
			<li><a href="'. $cfg['htmldir'] .'/mod/messages/move/">'. T_("Move thread") .'</a></li>
			<li><a href="'. $cfg['htmldir'] .'/mod/messages/merge/">'. T_("Merge threads") .'</a></li>
			
			<li class="header">'. T_("Statistics") .' (TODO)</li>
			<li><a href="'. $cfg['htmldir'] .'/mod/statistics/postcount/">'. T_("Post count") .'</a></li>
			<li><a href="'. $cfg['htmldir'] .'/mod/statistics/countries/">'. T_("Posting countries") .'</a></li>
			<li><a href="'. $cfg['htmldir'] .'/mod/statistics/files/">'. T_("Files and disk space") .'</a></li>

		</ul>';
		
		}
		?>	
	</div>
	
<?php
}
?>
