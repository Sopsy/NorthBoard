<?php
// Northpole.fi
// Yleiset funktiot
// 15.2.2010

function initialize() {
	global $cfg;

	// Check that all requirements are met and that there are no other fatal issues.
	$warnings = '';
	if(!function_exists('apc_fetch') AND $cfg['use_apc'])
		$warnings .= sprintf(T_('%s could not be found! Please install the module or set %s in config.php to false.'), 'PHP-APC', '$cfg["use_apc"]') .' ('. sprintf(T_('Debian package name: %s'), 'php-apc') .")\r\n";
	if(!function_exists('curl_init') AND $cfg['allow_embeds'])
		$warnings .= sprintf(T_('%s could not be found! Please install the module or set %s in config.php to false.'), 'PHP-Curl', '$cfg["allow_embeds"]') .' ('. sprintf(T_('Debian package name: %s'), 'php5-curl') .")\r\n";
	if(!function_exists('imagecreatefromstring'))
		$warnings .= sprintf(T_('%s could not be found! Please install the module.'), 'PHP-GD') .' ('. sprintf(T_('Debian package name: %s'), 'php5-gd') .")\r\n";
	if(!function_exists('geoip_country_name_by_name') AND $cfg['post_countries'] OR !function_exists('geoip_country_code_by_name') AND $cfg['post_countries'])
		$warnings .= sprintf(T_('%s could not be found! Please install the module or set %s in config.php to false.'), 'PHP-GeoIP', '$cfg["post_countries"]') .' ('. sprintf(T_('Debian package name: %s'), 'php5-geoip') .")\r\n";
	if(!is_file($cfg['imagick_bin']) AND $cfg['use_imagick'] )
		$warnings .= sprintf(T_("%s was not found from %s!"), "ImageMagick", $cfg['imagick_bin']) .' '. T_('ImageMagick is required to run the board.') .' '. sprintf(T_('Please check the value of %s in config.php.'), '$cfg["imagick_bin"]') .' ('. sprintf(T_('Debian package name: %s'), 'imagemagick') .")\r\n";
	if(!is_file($cfg['mp4box_bin']) AND $cfg['use_mp4box'])
		$warnings .= sprintf(T_("%s was not found from %s!"), "MP4Box", $cfg['mp4box_bin']) .' '. sprintf(T_('Please check the value of %s or change %s to false in config.php.'), '$cfg["mp4box_bin"]', '$cfg["use_mp4box"]') .' ('. sprintf(T_('Debian package name: %s'), 'gpac') .")\r\n";
	if(!is_file($cfg['nice_bin']) AND $cfg['lower_priority'])
		$warnings .= sprintf(T_("%s was not found from %s!"), "nice", $cfg['nice_bin']) .' '. sprintf(T_('Please check the value of %s or change %s to false in config.php.'), '$cfg["nice_bin"]', '$cfg["lower_priority"]') ."\r\n";
	if(!is_file($cfg['jpegtran_bin']) AND $cfg['use_jpegtran'])
		$warnings .= sprintf(T_("%s was not found from %s!"), "JPEGTran", $cfg['jpegtran_bin']) .' '. sprintf(T_('Please check the value of %s or change %s to false in config.php.'), '$cfg["jpegtran_bin"]', '$cfg["use_jpegtran"]') .' ('. sprintf(T_('Debian package name: %s'), 'libjpeg-progs') .")\r\n";
	if(!is_file($cfg['optipng_bin']) AND $cfg['use_optipng'])
		$warnings .= sprintf(T_("%s was not found from %s!"), "OptiPNG", $cfg['optipng_bin']) .' '. sprintf(T_('Please check the value of %s or change %s to false in config.php.'), '$cfg["optipng_bin"]', '$cfg["use_optipng"]') .' ('. sprintf(T_('Debian package name: %s'), 'optipng') .")\r\n";
	if(!is_file($cfg['pngcrush_bin']) AND $cfg['use_pngcrush'])
		$warnings .= sprintf(T_("%s was not found from %s!"), "PNGCrush", $cfg['pngcrush_bin']) .' '. sprintf(T_('Please check the value of %s or change %s to false in config.php.'), '$cfg["pngcrush_bin"]', '$cfg["use_pngcrush"]') .' ('. sprintf(T_('Debian package name: %s'), 'pngcrush') .")\r\n";
	if(is_file($cfg['srvdir'] ."/install.php"))
		$warnings .= T_("You have not deleted the file install.php. This is a serious security issue!");
	
	// If they weren't, kill the script.
	if($warnings != '') {
		die(T_("Fatal error! Script execution halted.") ."<br /><br/>". nl2br($warnings));
	}
	
	$mod = check_permissions();
	
	$cfg['user_class'] = $mod['user_class'];
	$cfg['user']['mod_id'] = $mod['id'];
	
	if(!empty($cfg['user']['site_style'])) {
		if(!array_key_exists($cfg['user']['site_style'], $cfg['themes']) AND $cfg['user']['site_style'] != '0')
			$cfg['user']['site_style'] = '0';
	}
	else {
		$cfg['user']['site_style'] = '0';
		update_user($cfg['user']['uid'], 'site_style', $cfg['user']['site_style']);
	}
		
	$banned = check_bans();
	if($banned)
		$cfg['banned_info'] = '<div class="banbar">'. sprintf(T_("You are banned! %sDetails%s"), '<a href="'. $cfg['htmldir'] .'/banned/">', '</a>') .'</div>';
	else
		$cfg['banned_info'] = '';
	
	define("INITIALIZED", true);
}

function alphabet($innum) {
	$alphabet = array('a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
	'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z', '1', '2', '3',
	'4', '5', '6', '7', '8', '9', '0');
	return $alphabet[$innum];
}

function generate_password($pituus = 8) {

	$salasana = '';
	$a = 0;
	while($a < $pituus) {
		$salasana .= alphabet(rand(0,61));
		$a++;
	}
	return $salasana;
}

// http://php.net/manual/en/function.filesize.php
function convert_filesize($bytes, $precision = 2) {
    $units = array(T_('B'), T_('KB'), T_('MB'), T_('GB'), T_('TB'));
  
    $bytes = max($bytes, 0);
    $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
    $pow = min($pow, count($units) - 1);
  
    $bytes /= pow(1024, $pow);
  
    return round($bytes, $precision) .' '. $units[$pow];
}

function getMimeType($file_path)
{
	$mtype = '';
	if (function_exists('mime_content_type')){
    	     $mtype = mime_content_type($file_path);
  	}
	else if (function_exists('finfo_file')){
    	     $finfo = finfo_open(FILEINFO_MIME);
    	     $mtype = finfo_file($finfo, $file_path);
    	     finfo_close($finfo);  
  	}
	if ($mtype == ''){
    	     $mtype = "application/force-download";
  	}
	return $mtype;
}

function error($text = false, $html = true, $metarefresh = false, $log = true) {
	global $cfg;

	if(!$text) $text = T_('Unknown error.');
	
	if($html) {
		$title = T_("An error occurred!"). $cfg['site_title']; // Sivun otsikko
		include("header.php"); // Html-headi
		echo '
			<h1>'. T_("An error occurred!") .'</h1>
			<h3>'. $text .'</h3>
		</div>';
		
		include("footer.php"); // Html-footeri
	}
	else {
		echo '
		<h1>'. T_("An error occurred!") .'</h1>
		<h3>'. $text .'</h3>';
	}
	ob_start();
	echo "\n\nHeaders:\n";
	print_r(getallheaders());
	if(!empty($_POST)) {
		echo "\n\n_POST:\n";
		print_r($_POST);
	}
	if(!empty($_GET))
	{
		echo "\n\n_GET:\n";
		print_r($_GET);
	}
	if(!empty($_FILES))
	{
		echo "\n\n_FILES:\n";
		print_r($_FILES);
	}
	if(!empty($_SERVER)) {
		echo "\n\n_SERVER\n";
		print_r($_SERVER);
	}
	$headers = ob_get_clean();
	
	if($log)
		mysql_query("INSERT INTO `errorlog`(`info`, `headers`, `time`, `ip`) VALUES ('". mysql_real_escape_string($text) ."', '". mysql_real_escape_string($headers) ."', UNIX_TIMESTAMP(), '". mysql_real_escape_string($_SERVER['REMOTE_ADDR']) ."')");
		
	die();
}

function info($text = false, $html = true, $metarefresh = false, $customtitle = false) {
	global $cfg;

	if(!$text) $text = T_('What the hell just happened?');
	if(!$customtitle) $customtitle = T_("Operation completed!");
	
	if($html) {
		$title = $customtitle . $cfg['site_title'];
		include("header.php"); // Html-head
	echo '
		<h1>'. $customtitle .'</h1>
		<h3>'. $text .'</h3>
	</div>';
	
	include("footer.php"); // Html-footeri
	}
	else {
		echo '
		<h1>'. $customtitle .'</h1>
		<h3>'. $text .'</h3>';
	}
	die();
}

function update_user($uid, $cols, $vals) {

	$fail = false;
	if(is_array($cols) OR is_array($vals)) {
		if( count($cols) == count($vals) )
		{	
			$i = 0;
			$query = '';
			foreach($cols AS $col)
			{
				if($i != 0)
					$query .= ', ';
				$query .= "`". mysql_real_escape_string($col) ."` = '". mysql_real_escape_string($vals[$i]) ."'"; 
				$i++;
			}
		}
		else
			$fail = true;
	}
	else
	{
		$query = "`". mysql_real_escape_string($cols) ."` = '". mysql_real_escape_string($vals) ."'";
	}

	if(!$fail) {
		$q = mysql_query("
			UPDATE `users`
			SET ". $query ."
			WHERE `uid` = '". mysql_real_escape_string($uid) ."'
			LIMIT 1
		");

		if($q)
			return true;
		else
			return false;
	}
	return false;

}

function load_user($nostatsupdate = false) {
	global $cfg;

	if(empty($_COOKIE['uid'])) {
		$uid = sha1(uniqid('', true) . time() . mt_rand(0,99));
		setcookie("uid", $uid, time() + (60 * 60 * 24 * 365), '/');
	}
	else $uid = mysql_real_escape_string($_COOKIE['uid']);

	if(!$nostatsupdate)
	{
		update_online_log($uid);
	}

	$get_guest = mysql_query("SELECT * FROM `users` WHERE `uid` = '". $uid ."' LIMIT 1");

	if(mysql_num_rows($get_guest) == 1) {
		$cfg['user'] = mysql_fetch_assoc($get_guest);

		// Try to get the timezone and set it if it's not set
		if(!$cfg['user']['timezone']) {
			$country_code = @geoip_country_code_by_name(get_ip());
			if($country_code) {
				$cfg['user']['timezone'] = geoip_time_zone_by_country_and_region( $country_code );
			}
			if(!$cfg['user']['timezone']) {
				$cfg['user']['timezone'] = $cfg['fallback_timezone'];
			}
			update_user($cfg['user']['uid'], 'timezone', $cfg['user']['timezone']);
		}
		date_default_timezone_set( $cfg['user']['timezone'] );

		// If the post password is empty, generate one
		if(!$cfg['user']['post_password']) {
			$cfg['user']['post_password'] = generate_password(8);
			update_user($cfg['user']['uid'], 'post_password', $cfg['user']['post_password']);
		}
		
		// Generate random username and password for the user (for site personalization)
		if(!$cfg['user']['uname'])
		{
			// Time + random value -> MD5 -> first 12 characters
			$cfg['user']['uname'] = substr( md5( time() . mt_rand( 0, 999999 ) ), 0, 12 );
			$cfg['user']['password'] = substr( md5( time() . mt_rand( 0, 999999 ) ), 0, 12 );
			$check_duplicate = mysql_query("SELECT `uid` FROM `users` WHERE `uname` = '". mysql_real_escape_string($cfg['user']['uname']) ."' LIMIT 1");
			while( mysql_num_rows($check_duplicate) != 0 )
			{
				// Was used already! Regenerate!
				$cfg['user']['uname'] = substr( md5( time() . mt_rand( 0, 999999 ) ), 0, 12 );
			}
			
			update_user($cfg['user']['uid'], 'uname', $cfg['user']['uname']);
			update_user($cfg['user']['uid'], 'password', $cfg['user']['password']);
		}
		
		// Load hidden and followed threads
		$cfg['user']['follow'] = array();
		$q = mysql_query("SELECT `thread` FROM `follow` WHERE `uid` = '". $cfg['user']['uid'] ."'");
		while($a = mysql_fetch_assoc($q))
			$cfg['user']['follow'][] = $a['thread'];
		
		$cfg['user']['hide'] = array();
		$q = mysql_query("SELECT `thread` FROM `hide` WHERE `uid` = '". $cfg['user']['uid'] ."'");
		while($a = mysql_fetch_assoc($q))
			$cfg['user']['hide'][] = $a['thread'];
		
		$cfg['user']['hide_boards'] = array();
		$q = mysql_query("SELECT `board` FROM `hide_boards` WHERE `uid` = '". $cfg['user']['uid'] ."'");
		while($a = mysql_fetch_assoc($q))
			$cfg['user']['hide_boards'][] = $a['board'];
		
	}
	else {
		
		$cfg['user'] = array(
			"uid" => $uid,
			"ip" => encrypt_ip(get_ip()),
			"online" => '1',
			"last_load" => time(),
			"site_style" => '0',
			"show_sidebar" => '1',
			"show_postform" => '1',
			"save_scroll" => '0',
			"sfw" => '1',
			"hide" => array(),
			"follow" => array(),
			"post_password" => '',
			"post_name" => '',
			"post_noko" => '0',
			"hide_boards" => array(),
			"autoload_media" => '1',
			"autoplay_gifs" => '0',
			"hide_names" => '1',
			"hide_ads" => '0',
			"hide_region" => '0',
			"hide_browserwarning" => '0',
			"uname" => '',
			"password" => ''
		);
		
		// Set default timezone
		$country_code = @geoip_country_code_by_name(get_ip());
		if($country_code) {
			$cfg['user']['timezone'] = geoip_time_zone_by_country_and_region( $country_code );
		}
		if(empty($cfg['user']['timezone'])) {
			$cfg['user']['timezone'] = $cfg['fallback_timezone'];
		}
		date_default_timezone_set( $cfg['user']['timezone'] );
			
	}
	
}

function load_locale() {
	global $cfg;
	
	if(empty($cfg['user']['locale'])) {
		// http://www.thefutureoftheweb.com/blog/use-accept-language-header
		if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
			$locales = array();
			// break up string into pieces (languages and q factors)
			preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)\s*(;\s*q\s*=\s*(1|0\.[0-9]+))?/i', $_SERVER['HTTP_ACCEPT_LANGUAGE'], $locale_parse);

			if(count($locale_parse[1])) {
				// create a list like "en" => 0.8
				$locales = array_combine($locale_parse[1], $locale_parse[4]);
				
				// set default to 1 for any without q factor
				foreach ($locales as $locale => $val) {
					if ($val === '') $locales[$locale] = 1;
				}

				// sort list based on value	
				arsort($locales, SORT_NUMERIC);
			}
		
			foreach($locales AS $locale => $priority) {
				if(strpos($locale, "-") !== false) {
					$locale = explode("-", $locale);
					$locale = $locale[0];
				}
				$locales[$locale] = $priority;
			}
			array_unique($locales);
			$locale_found = false;
			foreach($locales AS $locale => $priority) {
				$arr = glob($cfg['srvdir'] ."/inc/locales/". $locale ."*");
				if(!empty($arr[0])) {
					$arr = array_reverse(explode("/", $arr[0]));
					$locale = $arr[0];
					$cfg['user']['locale'] = $locale;
					update_user($cfg['user']['uid'], 'locale', $cfg['user']['locale']);
					$locale_found = true;
					break;
				}
			}
			
			if( !$locale_found )
			{
				$cfg['user']['locale'] = $cfg['default_locale'];
				update_user($cfg['user']['uid'], 'locale', $cfg['user']['locale']);
			}
		}
		else {
			$cfg['user']['locale'] = $cfg['default_locale'];
			update_user($cfg['user']['uid'], 'locale', $cfg['user']['locale']);
		}
	}
	else {
		if(!is_file($cfg['srvdir'] ."/inc/locales/". $cfg['user']['locale'] ."/LC_MESSAGES/messages.mo") OR !is_file($cfg['srvdir'] ."/inc/locales/". $cfg['user']['locale'] ."/LC_MESSAGES/messages.po")) {
			$cfg['user']['locale'] = $cfg['default_locale'];
			update_user($cfg['user']['uid'], 'locale', $cfg['user']['locale']);
		}
	}		

	setlocale(LC_ALL, $cfg['user']['locale']);
	bindtextdomain("messages", $cfg['srvdir'] ."/inc/locales/");
	bind_textdomain_codeset("messages", "UTF-8");
	textdomain("messages");
}

function T_($str)
{
	return _($str);
}

function get_ip($resolvedns = true) {
	global $cfg;

	$ip = mysql_real_escape_string($_SERVER['REMOTE_ADDR']);

	if($cfg['resolve_dns'] AND $resolvedns) {
	
		if(!defined('REMOTEHOSTADDR')) {
			mysql_query("DELETE FROM `cache_dns` WHERE `time` < '". time() - $cfg['dnscache_ttl'] ."'");
			$q = mysql_query("SELECT `host` FROM `cache_dns` WHERE `ip` = '". $ip ."' LIMIT 1");
			if(mysql_num_rows($q) == 1) {
				$host = mysql_result($q, 0, 'host');
			}
			else {
				$host = mysql_real_escape_string(gethostbyaddr($_SERVER["REMOTE_ADDR"]));
				mysql_query("INSERT INTO `cache_dns`(`ip`, `host`, `time`) VALUES ('". $ip ."', '". $host ."', UNIX_TIMESTAMP())");
			}
			define('REMOTEHOSTADDR', $host);
		}
		else {
			$host = REMOTEHOSTADDR;
		}
	}
	else {
		$host = $ip;
	}
	return $host;
}

function encrypt_ip($ip) {
	global $cfg;
	//$ip = gethostbyaddr($ip);
	$ip = hash("sha512", hash("sha512", $ip) . hash("sha512", $cfg['ip_salt']));
	return $ip;
}

function encrypt_password($password) {
	global $cfg;
	$password = hash("sha512", hash("sha512", $password) . hash("sha512", $cfg['pw_salt']));
	return $password;
}

function has_data($var) {
	if(empty($var) AND !is_numeric($var))
		return false;
	else
		return true;
}

function isThread($id)
{
	global $cfg;
	
	$q = mysql_query("SELECT `id` FROM `posts` WHERE `id` = '". mysql_real_escape_string($id) ."' AND `thread` = '0' LIMIT 1");
	if(mysql_num_rows($q) == 1)
		return true;
	else
		return false;
}

function getBoardUrlByID($id)
{
	global $cfg;
	
	$q = mysql_query("SELECT `url` FROM `boards` WHERE `id` = '". mysql_real_escape_string($id) ."' LIMIT 1");
	
	if($q AND mysql_num_rows($q) == 1)
		return mysql_result($q, 0, "url");
	else
		return false;
}

function decompress_first_file_from_zip($ZIPContentStr)
{
	//Input: ZIP archive - content of entire ZIP archive as a string
	//Output: decompressed content of the first file packed in the ZIP archive
    //let's parse the ZIP archive
    //(see 'http://en.wikipedia.org/wiki/ZIP_%28file_format%29' for details)
    //parse 'local file header' for the first file entry in the ZIP archive
    if(strlen($ZIPContentStr) < 102)
	{
        //any ZIP file smaller than 102 bytes is invalid
        return false;
    }
	else
	{
		$CompressedSize = binstrtonum(substr($ZIPContentStr,18,4));
		$UncompressedSize = binstrtonum(substr($ZIPContentStr,22,4));
		$FileNameLen = binstrtonum(substr($ZIPContentStr,26,2));
		$ExtraFieldLen = binstrtonum(substr($ZIPContentStr,28,2));
		$Offs = 30 + $FileNameLen + $ExtraFieldLen;
		$ZIPData = substr($ZIPContentStr,$Offs,$CompressedSize);
		$Data = gzinflate($ZIPData);
		if(strlen($Data) != $UncompressedSize){
			return false;
		}
		else return $Data;
	}
} 

function binstrtonum($Str)
{
	//Returns a number represented in a raw binary data passed as string.
	//This is useful for example when reading integers from a file,
	// when we have the content of the file in a string only.
	//Examples:
	// chr(0xFF) will result as 255
	// chr(0xFF).chr(0xFF).chr(0x00).chr(0x00) will result as 65535
	// chr(0xFF).chr(0xFF).chr(0xFF).chr(0x00) will result as 16777215
    $Num = 0;
    for($TC1 = strlen($Str) - 1; $TC1 >= 0; $TC1--){ //go from most significant byte
        $Num <<= 8; //shift to left by one byte (8 bits)
        $Num |= ord($Str[$TC1]); //add new byte
    }
    return $Num;
}
?>
