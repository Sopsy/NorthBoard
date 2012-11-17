<?php
require_once("inc/include.php");

if( empty( $_COOKIE['uid'] ) )
{
	error( T_("You need to have cookies allowed to personalize the site. If you just deleted your cookies and therefore receive this error, please reload this page.") );
}

if( empty( $_GET['do'] ) OR $_GET['do'] == "saved" )
{

	$title = T_("Site personalization") .' - '. $cfg['fp_title']; // Site title
	include("inc/header.php"); // Html-header

	echo '
			<div id="padded">
				<h2>'. T_("Site personalization") .'</h2>
				
				<h4>'. T_("General site settings") .'</h4>
				<form action="'. $cfg['htmldir'] .'/settings/save/general/" method="post">
				<fieldset>
				';
				
				// Stylesheet
				if(count($cfg['themes']) != 1) {
					echo '
					<label for="stylesheet">'. T_("Stylesheet") .'</label>
					<select name="stylesheet" id="stylesheet">
						<option value="0">'. T_("Use board defaults") .'</option>';
					foreach($cfg['themes'] AS $name => $css) {
						if($cfg['user']['site_style'] == $name) $cur = ' selected="selected"';
						else $cur = '';
						echo '
						<option'. $cur .'>'. $name .'</option>';
					}
					echo "
					</select>";
				}
				
				// Locale
				if(count($cfg['locales']) != 1) {
					echo '
					<label for="locale">'. T_("Locale") .'</label>
					<select name="locale" id="locale">';
						foreach($cfg['locales'] AS $name => $locale) echo '
						<option value="'. $locale .'"'. ($cfg['user']['locale'] == $locale ? ' selected="selected"' : '' ) .'>'. $name .'</option>';
						echo '
					</select>
					';
				}

				// Timezone
				static $regions = array(
					'Africa' => DateTimeZone::AFRICA,
					'America' => DateTimeZone::AMERICA,
					'Antarctica' => DateTimeZone::ANTARCTICA,
					'Aisa' => DateTimeZone::ASIA,
					'Atlantic' => DateTimeZone::ATLANTIC,
					'Europe' => DateTimeZone::EUROPE,
					'Indian' => DateTimeZone::INDIAN,
					'Pacific' => DateTimeZone::PACIFIC
				);
				foreach ($regions as $name => $mask) {
					$tzlist[] = DateTimeZone::listIdentifiers($mask);
				}
		
				echo '
				<label for="timezone">'. T_("Timezone") .'</label>
				<select name="timezone" id="timezone">';
					foreach($tzlist AS $offset)
						foreach($offset AS $timezone)
							echo  '
					<option value="'. $timezone .'"'. ($cfg['user']['timezone'] == $timezone ? ' selected="selected"' : '' ) .'>'. str_replace("/", " / ", str_replace("_", " ", $timezone)) .'</option>';
				echo '
				</select>';
				
				// NSFW
				echo '
				<p><input class="inline" type="checkbox" name="sfw" id="sfw" '. ($cfg['user']['sfw'] ? 'checked="checked" ' : '') .'/>
				<label class="inline" for="sfw">'. T_("Hide NSFW-boards") .'</label></p>
				<p>'. T_("Unckecking this box will unlock parts of the site which may contain nudity, gore etc. and thus are not recommended for persons under the age of 18.") .'</p>';
				
				// Autoload media
				echo '
				<p><input class="inline" type="checkbox" name="autoload_media" id="autoload_media" '. ($cfg['user']['autoload_media'] ? 'checked="checked" ' : '') .'/>
				<label class="inline" for="autoload_media">'. T_("Autoload media players and embeds") .'</label></p>
				<p>'. T_("Uncheck this to make page loading faster if you are on a slow computer or behind a slow connection.") .'</p>';
				
				// Autoplay gifs
				echo '
				<p><input class="inline" type="checkbox" name="autoplay_gifs" id="autoplay_gifs" '. ($cfg['user']['autoplay_gifs'] ? 'checked="checked" ' : '') .'/>
				<label class="inline" for="autoplay_gifs">'. T_("Animate thumbnails of animated gifs") .'</label></p>
				<p>'. T_("Uncheck this to make page loading faster if you are on a slow computer or behind a slow connection.") .'</p>';
				
				// Save content scroll
				echo '
				<p><input class="inline" type="checkbox" name="save_scroll" id="save_scroll" '. ($cfg['user']['save_scroll'] ? 'checked="checked" ' : '') .'/>
				<label class="inline" for="save_scroll">'. T_("Don't scroll to top on page reload") .'</label></p>
				<p>'. T_("By default, when you reload the page, you are returned to the top of it. Check this to save the last scroll position.") .'</p>';
				
				// Hide sidebar
				echo '
				<p><strong>'. T_("Show sidebar") .'</strong></p>
				<p>
					<input class="inline" type="radio" value="1" name="show_sidebar" id="show_sidebar_1" '. ($cfg['user']['show_sidebar'] === '1' ? 'checked="checked" ' : '') .'/>
					<label class="inline" for="show_sidebar_1">'. T_("Always show") .'</label>
					<input class="inline" type="radio" value="0" name="show_sidebar" id="show_sidebar_0" '. ($cfg['user']['show_sidebar'] === '0' ? 'checked="checked" ' : '') .'/>
					<label class="inline" for="show_sidebar_0">'. T_("Always hide") .'</label>
					<input class="inline" type="radio" value="2" name="show_sidebar" id="show_sidebar_2" '. ($cfg['user']['show_sidebar'] === '2' ? 'checked="checked" ' : '') .'/>
					<label class="inline" for="show_sidebar_2">'. T_("Use board defaults") .'</label>
				</p>';
					
				// Hide names
				echo '
				<p><input class="inline" type="checkbox" name="hide_names" id="hide_names" '. ($cfg['user']['hide_names'] ? 'checked="checked" ' : '') .'/>
				<label class="inline" for="hide_names">'. T_("Hide all poster names") .'</label></p>
				<p>'. T_("Check this to hide names and tripcodes of posters in all posts. This does not include the name 'OP' when the 'Name: OP' -checkbox was checked when posting, but includes the board default poster name (eg. Anonymous).") .'</p>';
							
				// Hide region
				echo '
				<p><input class="inline" type="checkbox" name="hide_region" id="hide_region" '. ($cfg['user']['hide_region'] ? 'checked="checked" ' : '') .'/>
				<label class="inline" for="hide_region">'. T_("Hide my region") .'</label></p>
				<p>'. T_("Check this to hide your region and city from the flag in your posts.") .'</p>';
					
	
				echo '
				<input type="submit" class="button" value="'. T_("Save settings") .'" />
				</fieldset>
				</form>';
				
				// Hidden threads
				echo '
				<h4>'. T_("Hidden threads") .'</h4>';
				
				$q = mysql_query("SELECT `thread` FROM `hide` WHERE `uid` = '". $cfg['user']['uid'] ."' ORDER BY `time` DESC");
				
				while($res = mysql_fetch_assoc($q))
				{
					$qb = mysql_query("SELECT * FROM `posts` WHERE `id` = '". $res['thread'] ."' LIMIT 1");
					$qb = mysql_fetch_assoc($qb);
					echo '<p id="hidden_'. $qb['id'] .'" class="hiddenthread"><a href="'. $cfg['htmldir'] .'/'. getBoardUrlByID($qb['board']) .'/'. $qb['id'] .'/">/'. getBoardUrlByID($qb['board']) .'/'. $qb['id'] .'/</a> - '. (!empty($qb['subject']) ? $qb['subject'] : T_("No subject")) .'  &mdash; <a href="javascript:void(restore_thread(\''. $qb['id'] .'\'));">'. T_("Restore") .'</a></p>';
				}
				
				if(mysql_num_rows($q) != 0) echo '
				<p><a href="javascript:void(restore_thread(\'all\'));">'. T_("Restore all") .'</a></p>';
				else echo '
				<p>'. T_("You have no hidden threads") .'</p>';
				
				if($cfg['use_overboard'])
				{
					echo '
					<h4>'. T_("Overboard settings") .'</h4>
					<p>'. T_("Boards to hide from the overboard") .'</p>
					<form action="'. $cfg['htmldir'] .'/settings/save/overboard/" method="post">
						<fieldset>
							';
							if($cfg['user']['sfw'])
								$sfw = " AND `worksafe` = '1'";
							else
								$sfw = '';
								
							$q = mysql_query("SELECT `id`, `name`, `url` FROM `boards` WHERE `category` != '0'". $sfw ." ORDER BY `url` ASC");
											
							while($board = mysql_fetch_assoc($q))
							{
								echo '
								<div class="list">
									<input class="inline" type="checkbox" name="board_'. $board['id'] .'" id="board_'. $board['url'] .'"'. (in_array($board['id'], $cfg['user']['hide_boards']) ? ' checked="checked"' : '') .' />
									<label class="inline" for="board_'. $board['url'] .'">'. $board['url'] .'</label>
								</div>';
							}
						
							echo '
							<input type="submit" name="save" class="button" value="'. T_("Save") .'" />
						</fieldset>
					</form>';
				}
			
				echo '
				<h4>'. T_("Saved settings") .'</h4>
				<p>'. sprintf( T_("Your personalization settings are automatically saved and are deleted if you are inactive for more than %s days."), $cfg['save_guest_info_time'] ) .'</p>
				<p>'. T_("These settings include your selected stylesheet, sidebar hidden or not, NSFW-boards hidden or not, locale, timezone, hidden- and followed threads, overboard customization and the message deletion password.") .'</p>
				<p>'. T_("If you would like to clear your cookies or load the settings into another computer or browser, you can use these automatically generated username and password (Note that as for now, you cannot change these)") .':</p>
				<p><strong>'. T_("Username") .':</strong> '. $cfg['user']['uname'] .'</p>
				<p><strong>'. T_("Password") .':</strong> '. $cfg['user']['password'] .'</p>
			
				<h4>'. T_("Load saved settings") .'</h4>
				<form action="'. $cfg['htmldir'] .'/settings/load/" method="post">
					<label for="uname">'. T_("Username") .'</label>
					<input type="text" name="uname" id="uname" />
					<label for="password">'. T_("Password") .'</label>
					<input type="text" name="password" id="password" />
				
					<input type="submit" value="'. T_("Load settings") .'" class="button" />
				</form>
				
				<h4>'. T_("Delete saved settings") .'</h4>
				<p>'. T_("If you, for some reason, would like to completely delete all of your personalization settings and saved information about you from the service, you can do it by clicking the following button.") .'</p>
				<p>'. T_("Please note that after deleting your profile, there's no way of restoring any data of it. Also note that after deletion, a new profile will be created for you automatically.") .'</p>
				<p><a href="'. $cfg['htmldir'] .'/settings/delete/'. $cfg['user']['uname'] .'/" class="button" onclick="if(!confirm(\''. T_("Are you sure you want to delete all of your personalized settings?") .'\')){return false;}">'. T_("Delete profile") .'</a></p>
			</div>
		</div>
	';
	
	include("inc/footer.php"); // Html-footer
}
// Saving the settings
elseif( $_GET['do'] == "save" )
{
	// Overboard settings
	if( !empty( $_GET['id'] ) AND $_GET['id'] == 'overboard' )
	{
		if( !empty( $_POST ) )
		{
			$hideboards = array('');
			
			foreach( $_POST AS $input => $value )
			{
				if( $value != 'on' )
					continue;

				$input = str_replace("board_", "", $input);
				$hideboards[] = $input;
			}
			//SELECT COUNT(`hide_boards`.`board`) AS `count`, `boards`.`url` FROM `hide_boards`, `boards` WHERE `boards`.`id` = `hide_boards`.`board` GROUP BY `board` ORDER BY `count` DESC LIMIT 100
			mysql_query("DELETE FROM `hide_boards` WHERE `uid` = '". $cfg['user']['uid'] ."'");
			
			$query = "INSERT INTO `hide_boards`(`uid`, `board`) VALUES ";
			$insert = false;
			
			$i = 0;
			foreach($hideboards AS $boardid)
			{
				if($boardid != 0)
				{
					if($i != 0)
						$query .= ', ';
				
					$query .= "('". $cfg['user']['uid'] ."', '". $boardid ."')";
					++$i;
					$insert = true;
				}
			}
			
			$q = false;
			if($insert)
				$q = mysql_query($query);
			
			if($q OR !$insert)
				info( T_("Changes saved!") . '</p><p>'. T_("Returning back to settings..") .'</p>', true, '1;'. $cfg['htmldir'] .'/settings/' );
			else
				error( T_("Saving the changes has failed due to a database error!") );
		}
		else
		{
			header("Location: ". $cfg['htmldir'] ."/settings/");
			die();
		}
	}
	// General settings
	elseif( !empty( $_GET['id'] ) AND $_GET['id'] == 'general' )
	{
		if( !empty( $_POST ) )
		{
			// Verify sent data
			static $regions = array(
				'Africa' => DateTimeZone::AFRICA,
				'America' => DateTimeZone::AMERICA,
				'Antarctica' => DateTimeZone::ANTARCTICA,
				'Aisa' => DateTimeZone::ASIA,
				'Atlantic' => DateTimeZone::ATLANTIC,
				'Europe' => DateTimeZone::EUROPE,
				'Indian' => DateTimeZone::INDIAN,
				'Pacific' => DateTimeZone::PACIFIC
			);
			foreach ($regions as $name => $mask) {
				$tzlist[] = DateTimeZone::listIdentifiers($mask);
			}
			
			$timezones = array();
			
			foreach($tzlist AS $offset)
				foreach($offset AS $timezone)
					$timezones[] = $timezone;
			
			if( !in_array($_POST['timezone'], $timezones) )
				error( T_("The timezone you selected does not exist!") );
			if( !in_array($_POST['locale'], $cfg['locales']) )
				error( T_("The locale you selected does not exist!") );
			if( !array_key_exists($_POST['stylesheet'], $cfg['themes']) AND $_POST['stylesheet'] !== '0' )
				error( T_("The stylesheet you selected does not exist!") );
			
			// SFW
			if( !empty($_POST['sfw']) AND $_POST['sfw'] == 'on' )
				$sfw = '1';
			else
				$sfw = '0';
			
			// Autoplay gifs
			if( !empty($_POST['autoplay_gifs']) AND $_POST['autoplay_gifs'] == 'on' )
				$autoplay_gifs = '1';
			else
				$autoplay_gifs = '0';
				
			// Autoload media
			if( !empty($_POST['autoload_media']) AND $_POST['autoload_media'] == 'on' )
				$autoload_media = '1';
			else
				$autoload_media = '0';
				
			// Hide names
			if( !empty($_POST['hide_names']) AND $_POST['hide_names'] == 'on' )
				$hide_names = '1';
			else
				$hide_names = '0';
				
			// Hide region
			if( !empty($_POST['hide_region']) AND $_POST['hide_region'] == 'on' )
				$hide_region = '1';
			else
				$hide_region = '0';
				
			// Save scroll
			if( !empty($_POST['save_scroll']) AND $_POST['save_scroll'] == 'on' )
				$save_scroll = '1';
			else
				$save_scroll = '0';
				
			// Sidebar
			if( $_POST['show_sidebar'] === '0' OR $_POST['show_sidebar'] === '1' OR $_POST['show_sidebar'] === '2' )
				$show_sidebar = $_POST['show_sidebar'];
			else
				$show_sidebar = '2';
			
			$cols = array('timezone', 'locale', 'site_style', 'sfw', 'autoload_media', 'autoplay_gifs', 'hide_names', 'hide_region', 'save_scroll', 'show_sidebar');
			$vals = array($_POST['timezone'], $_POST['locale'], $_POST['stylesheet'], $sfw, $autoload_media, $autoplay_gifs, $hide_names, $hide_region, $save_scroll, $show_sidebar);
			
			$q = update_user($cfg['user']['uid'], $cols, $vals);
			
			if($q)
				info( T_("Changes saved!") . '</p><p>'. T_("Returning back to settings..") .'</p>', true, '1;'. $cfg['htmldir'] .'/settings/' );
			else
				error( T_("Saving the changes has failed due to a database error!") );
		}
		else
		{
			header("Location: ". $cfg['htmldir'] ."/settings/");
			die();
		}
	}
	else
	{
		header("Location: ". $cfg['htmldir'] ."/settings/");
		die();
	}
}
// Loading existing settings
elseif( $_GET['do'] == "load" )
{
	if( !empty( $_POST ) )
	{
		if( !empty( $_POST['uname'] ) AND !empty( $_POST['password'] ) )
		{
			$uname = mysql_real_escape_string( $_POST['uname'] );
			$pwd = mysql_real_escape_string( $_POST['password'] );
	
			$q = mysql_query("SELECT `uid` FROM `users` WHERE `uname` = '". $uname ."' AND `password` = '". $pwd ."' LIMIT 1");
			
			if($q)
			{
				if( mysql_num_rows($q) == 1 )
				{
					$uid = mysql_result($q, 0, "uid");
					setcookie("uid", $uid, time() + (60 * 60 * 24 * 365), '/');
					
					info( T_("Settings loaded successfully!") . '</p><p>'. T_("Returning back to settings..") .'</p>', true, '1;'. $cfg['htmldir'] .'/settings/' );
			
				}
				else
					error( T_("The username or password was wrong or the profile does not exist."), true, false, false );
			}
			else
				error( T_("Database error! Please try again.") );
			
		}
		else
			error( T_("You did not fill in your username or password."), true, false, false );
	}
	else
	{
		header("Location: ". $cfg['htmldir'] ."/settings/");
		die();
	}
}
// Deleting the profile
elseif( $_GET['do'] == "delete" )
{

	if( !empty( $_GET['id'] ) AND $_GET['id'] == $cfg['user']['uname'] )
	{
		$q = deleteUser($cfg['user']['uname']);
		if($q) {
			info( T_("Your profile was deleted successfully. To further clear your information, please delete all cookies before loading any other page.") );
		}
		else
			error( T_("Deleting your profile failed due to a database error.") );
	}
	else
		error( T_("Wrong confirmation code. Your profile was not deleted. Please try again.") );
}
?>
