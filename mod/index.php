<?php
// Northpole.fi
// Hallinnan etusivu
// 17.2.2010
require_once("../inc/include.php");

if($cfg['user_class'] == 0)
	header("Location: ". $cfg['htmldir'] ."/mod/login/");
else {
	$mod_pages = true;
	$title = T_("Board administration"). $cfg['site_title'];
	include($cfg['srvdir'] ."/inc/header.php");

	echo '
		<div id="padded">
			<h2>'. T_("Notifications") .'</h2>
			<div class="notification">
				<p>
					Lol eihän nää viestit tuu ees tietokannasta xD
					<span>'. sprintf(T_("Posted by %s at %s"), "Joose", date(T_("Y/m/d g:i A"))) .'</span>
				</p>
			</div>
			<div class="notification">
				<p>
					Nyt perskule töihin!
					<span>'. sprintf(T_("Posted by %s at %s"), "admin", date(T_("Y/m/d g:i A"))) .'</span>
				</p>
			</div>
			<div class="notification">
				<p>
					Toinen testi vielä tähän
					<span>'. sprintf(T_("Posted by %s at %s"), "admin", date(T_("Y/m/d g:i A"))) .'</span>
				</p>
			</div>
		</div>
	</div>
	';

	include($cfg['srvdir'] ."/inc/footer.php");
}
	
?>