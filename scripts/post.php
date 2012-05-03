<?php
// Northpole.fi
// Postauksen käsittelijä
// 15.2.2010

$nostatsupdate = true;
require_once("../inc/include.php");

$ip = mysql_real_escape_string(encrypt_ip(get_ip()));

if(empty($_POST))
{
	header("Location: ". $cfg['htmldir']);
	die();
}

// Tarkistetaan bannit..
$getip = mysql_query("SELECT * FROM `bans` WHERE `ip` = '". $ip ."' AND `is_old` = '0' LIMIT 1");
if(mysql_num_rows($getip) != 0) {
	header("Location: ". $cfg['htmldir']."/banned/");
	die();
}
if(!empty($_COOKIE['passwd'])) {
	$session = mysql_real_escape_string($_COOKIE['passwd']);
	$getcookie = mysql_query("SELECT * FROM `bans` WHERE `uid` = '". $session ."' AND `is_old` = '0' LIMIT 1");
	if(mysql_num_rows($getcookie) != 0) {
		header("Location: ". $cfg['htmldir']."/banned/");
		die();
	}
}

// Tarkistetaan lähetetyt tiedot ja asetetaan muuttujiin
//Lauta-ID
if(!empty($_POST['board']) AND is_numeric($_POST['board']))
	$board = mysql_real_escape_string($_POST['board']);
else error(T_("Board ID is missing"));
$query = mysql_query("SELECT `url`, `locked`, `international`, `namefield`, `worksafe` FROM `boards` WHERE `id` = '". $board ."' LIMIT 1");
if(mysql_num_rows($query) != 0) {
	$url = mysql_result($query, 0, "url");
	$locked = mysql_result($query, 0, "locked");
	$international = mysql_result($query, 0, "international");
	$namefield = mysql_result($query, 0, "namefield");
	$worksafe = mysql_result($query, 0, "worksafe");
}
else error(T_("The board does not exist!"));

if($locked == 1) error(T_("The board is locked and cannot be posted to"), true, false, false);

// Reconstruct the file array to support HTML5 multiple file upload
$files = array();
if(!empty($_FILES))
{
	foreach($_FILES AS $fdata)
	{
		if(is_array($fdata['name'])) {
			for($i = 0 ; $i < count($fdata['name']) ; ++$i) {
				if($fdata['name'][$i] != '')
				{
					$files[]=array(
						'name' => $fdata['name'][$i],
						'type' => $fdata['type'][$i],
						'tmp_name' => $fdata['tmp_name'][$i],
						'error' => $fdata['error'][$i],
						'size' => $fdata['size'][$i]
					);
				}
			}
		}
		else
			$files[] = $fdata;
	}
}


//Lanka-ID
if(!empty($_POST['thread']) AND is_numeric($_POST['thread']) OR $_POST['thread'] == 0) {
	$thread = mysql_real_escape_string($_POST['thread']);
	if($thread != 0) $answer = true;
	else $answer = false;
}
else error(T_("Thread ID is missing"));

// Spämmifiltteri
if($thread == 0) {
	$get = mysql_query("SELECT `id` FROM `posts` WHERE `thread` = '0' AND `ip` = '". $ip ."' AND `time` >= '". (time() - $cfg['threadlimit']) ."'");
}
else {
	$get = mysql_query("SELECT `id` FROM `posts` WHERE `ip` = '". $ip ."' AND `time` >= '". (time() - $cfg['floodlimit']) ."'");
}
if(mysql_num_rows($get) != 0)
	error(T_("You are posting too fast. Please wait a while beetween your posts."), true, false, false);
$get = mysql_query("SELECT `id` FROM `posts` WHERE `board` = '". $board ."' AND `ip` = '". $ip ."' AND `time` >= '". (time() - $cfg['floodlimit']) ."'");
if(mysql_num_rows($get) != 0)
	error(T_("You are posting too fast. Please wait a while beetween your posts."), true, false, false);
if(!empty($_POST['email']))
	error(T_("Spambot detected. Your message will not be saved."));

$ref_comp = $cfg['htmldir'];
$http_ref = str_replace("www.", "", $_SERVER['HTTP_REFERER']);
$http_ref = substr($http_ref, 0, strlen($ref_comp));
if($http_ref != $ref_comp)
	error(T_("Your browser did not send a HTTP-Referer -value or it was incorrect. Please check your browser settings."), true, false, false);

if($namefield == 1) {
	//Nimi
	if( !empty( $_POST['name'] ) )
		$postername = mysql_real_escape_string( removeForbiddenUnicode( substr($_POST['name'], 0, $cfg['name_maxlength'] ) ) );
	else $postername = '';
	
	setcookie( "postername", $postername, time() + (60 * 60 * 24 * 365), '/' );
}
elseif($namefield == 2) {
	//AP-nimi
	if(!empty($_POST['op']) AND $_POST['op'] == "on") $op = true;
	else $op = false;
	$postername = '';
}
else {
	$postername = '';
}

//Sage
if(!empty($_POST['sage']) AND $_POST['sage'] == "on") $sage = true;
else $sage = false;
//RAGE!
if(!empty($_POST['rage']) AND $_POST['rage'] == "on") $rage = true;
else $rage = false;
//Love
if(!empty($_POST['love']) AND $_POST['love'] == "on") $love = true;
else $love = false;
//Noko
if(!empty($_POST['noko']) AND $_POST['noko'] == "on") $noko = true;
else $noko = false;
if($noko) setcookie("noko", "on", time() + (60 * 60 * 24 * 365), '/');
else setcookie("noko", "off", time() + (60 * 60 * 24 * 365), '/');

//Aihe
if(!empty($_POST['subject']) AND mb_strlen($_POST['subject']) <= $cfg['subj_maxlength'])
	$subject = mysql_real_escape_string( removeForbiddenUnicode( htmlspecialchars( $_POST['subject'] ) ) );
else
{
	if( empty( $_POST['subject'] ) )
		$subject = '';
	elseif( mb_strlen($_POST['subject']) > $cfg['subj_maxlength'] )
		error( sprintf(T_("Post subject is too long! The maximum is %s characters."), $cfg['subj_maxlength']), true, false, false );
}

//Message
$_POST['msg'] = str_replace("\r\n", "\n", $_POST['msg']);
if(!empty($_POST['msg']) AND mb_strlen($_POST['msg']) <= $cfg['msg_maxlength']) {
	$message = format_text($_POST['msg']);
	if( strlen( $message ) == 0  AND empty( $files[0] ) AND !is_file( $files[0]['tmp_name'] ) AND empty( $_POST['embed'] ) )
		error(T_("Message, file or embed is missing!"), true, false, false);
}
else {
	if( empty( $_POST['msg'] ) AND empty( $files[0] ) AND !is_file( $files[0]['tmp_name'] ) AND empty( $_POST['embed'] ) )
		error(T_("Message, file or embed is missing!"), true, false, false);
	elseif(mb_strlen($_POST['msg']) > $cfg['msg_maxlength'])
		error(sprintf(T_("Your message is too long! The maximum is %s characters."), $cfg['msg_maxlength']), true, false, false);
	else
		$message = '';
}
//Password
if(!empty($_POST['password'])) {
	update_user($cfg['user']['uid'], 'post_password', $_POST['password']);
	$password = mysql_real_escape_string($_POST['password']);
}
else {
	if(!empty($cfg['user']['post_password']))
		$password = $cfg['user']['post_password'];
	else {
		$password = generate_password(8);
		update_user($cfg['user']['uid'], 'post_password', $password);
	}
}
//Modpost
if(!empty($_POST['modpost']) AND $_POST['modpost'] == "on") $modpost = true;
else $modpost = false;


// Country detection
if($cfg['post_countries']) {
	$geoip_data = geoip_record_by_name(get_ip(false));
	$geoip_data['region_name'] = geoip_region_name_by_code($geoip_data['country_code'], $geoip_data['region']);
}
else {
	$geoip_data = array(
		'continent_code' => 'N/A',
		'country_code' => 'N/A',
		'country_name' => 'N/A',
		'region' => '0',
		'region_name' => 'N/A',
		'latitude' => '0',
		'longitude' => '0'
	);
}

if($cfg['user_class'] == 0 AND strtolower($geoip_data['country_code']) != "fi" AND !$international AND !in_array(get_ip(false), $cfg['ip_whitelist'])) {
	error(T_("Sorry, posting from the country you are posting from at the moment is disallowed to this board."), true, false, false);
}

//-----------------------------
//Proxy and TOR detection START
//-----------------------------
$proxy = 0;

if($cfg['proxy_detection'])
{


	$purge_cache_time = time() - $cfg['proxy_cache_ttl']; // Convert the amount into days.
	mysql_query("DELETE FROM `cache_proxydetection` WHERE `time` <= '". $purge_cache_time ."'");

	$proxydq = mysql_query("SELECT `port`, `type` FROM `cache_proxydetection` WHERE `ip` = '". get_ip(false) ."' LIMIT 1");

	if(mysql_num_rows($proxydq) != 1)
	{

		$proxy_list_file = "../tmp/proxy_list.php";
		$fetch_proxy = false;
		if( !is_file($proxy_list_file) )
			$fetch_proxy = true;
		else
		{
			include($proxy_list_file);
			if( $proxy_fetched < (time() - $cfg['proxy_list_ttl']) )
			{
				$fetch_proxy = true;
			}
		}

		if($fetch_proxy)
		{
			$proxylist = file_get_contents( $cfg['proxy_query_urls'] );
			if($proxylist)
			{
				$proxylist = decompress_first_file_from_zip($proxylist);
				$proxylist = explode(",", $proxylist);

				$proxy_out_file = '<?php'."\r\n".'$proxy_fetched = \''. time() .'\';'."\r\n".'$proxylist = array(';

				$i = 0;
				foreach($proxylist AS $row) {
					if(!preg_match("/^#/", $row))
					{
						if(!empty($row)) {
							if($i != 0)
								$proxy_out_file .= ', ';

							$proxy_out_file .= '\''. $row .'\'';

							++$i;
						}
					}
				}

				$proxy_out_file .= ');'."\r\n".'?>';

				file_put_contents($proxy_list_file, $proxy_out_file);
			}
			include($proxy_list_file);
		}

		// At least it detects Opera turbo.
		$proxy_forwarded = false;
		if(!empty($_SERVER['X-Forwarded-For']))
			$proxy_forwarded = true;
		
		if(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
			$proxy_forwarded = true;
		
		if( in_array( get_ip(false), $proxylist ) OR $proxy_forwarded )
		{
			$proxy = 1;
		}
	
		$port = 0;
		if($proxy == '0' AND $cfg['proxy_advanced_detection'])
		{

			foreach($cfg['proxy_ports'] AS $port)
			{
				$try_port = @fsockopen(get_ip(false), $port, $errno, $errstr, $cfg['proxy_connect_timeout']);
				if($try_port)
				{
					$proxy = '1';
					break;
				}
			}
			
			if($port == '80' OR $port == '8080')
			{
				$headers = get_headers(get_ip(false));

				if(!$headers[2])
					$proxy = 0;

				if(substr($headers[2], 0, 14) == 'Server: Apache')
					$proxy = 0;
		
				if($headers[0] == 'HTTP/1.1 401 Unauthorized')
					$proxy = 0;
			}
			
			if($proxy == '0')
				$port = 0;
		}

		$tor_list_file = "../tmp/tor_exit_node_list.php";
		$fetch_tor = false;
		if( !is_file($tor_list_file) )
			$fetch_tor = true;
		else
		{
			include($tor_list_file);
			if( $tor_fetched < (time() - $cfg['tor_list_ttl']) )
			{
				$fetch_tor = true;
			}
		}
	
		if($fetch_tor)
		{
			$torlist = file_get_contents($cfg['tor_query_url']);
		
			if($torlist)
			{
				$torlist = explode("\n", $torlist);
			
				$tor_out_file = '<?php'."\r\n".'$tor_fetched = \''. time() .'\';'."\r\n".'$torlist = array(';

				$i = 0;
				foreach($torlist AS $row) {
					if(!preg_match("/^#/", $row))
					{
						if(!empty($row)) {
							if($i != 0)
								$tor_out_file .= ', ';
						
							$tor_out_file .= '\''. $row .'\'';
					
							++$i;
						}
					}
				}

				$tor_out_file .= ');'."\r\n".'?>';
			
				file_put_contents($tor_list_file, $tor_out_file);
			}
			include($tor_list_file);
		}
	
		if( in_array( get_ip(false), $torlist ) )
		{
			$proxy = 2;
		}
		
		mysql_query("INSERT INTO `cache_proxydetection` (`ip`, `time`, `port`, `type`) VALUES ('". get_ip(false) ."', UNIX_TIMESTAMP(), '". $port ."', '". $proxy ."')");
	}
	else
	{
		$proxy = mysql_result($proxydq, 0, 'type');
	}
	if( $cfg['proxy_disallow_posting'] AND ( $proxy == '1' OR $proxy == '2' ) )
		error(T_("Sorry, posting through a proxy is not allowed."), true, false, false);
}
//---------------------------
//Proxy and TOR detection END
//---------------------------

if($answer) {
	$query = mysql_query("SELECT `locked` FROM `posts` WHERE `id` = '". $thread ."' AND `thread` = '0' LIMIT 1");
	if(mysql_num_rows($query) != 0) {
		$locked = mysql_result($query, 0, "locked");
		if($locked == 1 AND $cfg['user_class'] == 0)
			error(T_("The thread you tried to post to is locked."), true, false, false);
	}
	else error(T_("Thread does not exist!"), true, false, false);
}

//Tiedoston tallennus
$fileid = array();
$embed = '';
$embedded = false;
$file = false;
$filecount = count($files);
$skipped_files = array();
$inserted_files = array();

$post_size = 0;
foreach($files AS $name => $this_file) {
	$post_size += $this_file['size'];
}

if($post_size > $cfg['max_filesize'])
	error(sprintf(T_("The sum of file sizes of the files you uploaded exceeded the maxium allowed file size of %s."), convert_filesize($cfg['max_filesize'])));

if($filecount != 0) {
	$folder = mysql_real_escape_string($url);
	// Check the free hdd space if files were sent
	if(disk_free_space($cfg['srvdir']) < $cfg['free_space_limit'])
		error(T_("The server is running out of free hard drive space. New files will not be accepted before some space is freed."));

	if(!is_dir($cfg['srvdir'] ."/files/"))
		if(!mkdir($cfg['srvdir'] ."/files/", 0777))
			error(T_('File directory is missing and it cannot be created automatically! Please create a directory called "files" to the board root and give the script full read/write access into it.'));
	if(!is_dir($cfg['srvdir'] ."/files/". $folder .""))
		if(!mkdir($cfg['srvdir'] ."/files/". $folder ."/", 0777))
			error(T_('Cannot create file directories! Please make sure that the script has full read/write access to the "files" -directory.'));
	if(!is_dir($cfg['srvdir'] ."/files/". $folder ."/thumb"))
		if(!mkdir($cfg['srvdir'] ."/files/". $folder ."/thumb/", 0777))
			error(T_('Cannot create file directories! Please make sure that the script has full read/write access to the "files" -directory.'));
	if(!is_dir($cfg['srvdir'] ."/files/". $folder ."/orig"))
		if(!mkdir($cfg['srvdir'] ."/files/". $folder ."/orig/", 0777))
			error(T_('Cannot create file directories! Please make sure that the script has full read/write access to the "files" -directory.'));
}

$i = 1;
foreach($files AS $name => $singlefile) {
	
	if($i > $cfg['max_files'])
	{
		$skipped_files[] = sprintf( T_("File %s: The maximum amount of files has been reached, excess files will be discarded."), $singlefile['name'] );
		continue;
	}
	
	if(is_uploaded_file($singlefile['tmp_name']))
	{

		$file = $singlefile;
		if($file['size'] <= $cfg['max_filesize'])
		{

			$a = array();
			$query = mysql_query("SELECT `extension`, `mime` FROM `filetypes`");
			while($type = mysql_fetch_assoc($query))
			{
				$mimes = explode(",", $type['mime']);
				$a[$type['extension']] = $mimes;
			}
			
			$path_info = pathinfo($file['name']);
			$extension = mysql_real_escape_string(strtolower($path_info['extension']));
			$name = mysql_real_escape_string($path_info['filename']);
			$dest_name = time() . mt_rand(000000, 999999);
			$mime = mysql_real_escape_string(mime_content_type($file['tmp_name']));
			$data = file_get_contents($file['tmp_name']);
			$md5 = mysql_real_escape_string(md5($data));
			$data = mysql_real_escape_string($data);
			
			if(!empty($_POST['clearexif']) AND $_POST['clearexif'] == "on")
				$clearexif = true;
			else
				$clearexif = false;
			
			
			
			if( !is_uploaded_file( $file['tmp_name'] ) )
			{
				if( $filecount == 1 )
					error( T_("Uploading a file has failed. Please try again.") );
				else {
					$skipped_files[] = T_("Uploading a file has failed. Please try again.");
					continue;
				}
			}
			
			if(array_key_exists($extension, $a))
			{
				if(in_array($mime, $a[$extension]))
				{
					
					$query = mysql_query("SELECT `id` FROM `files` WHERE `md5` = '". $md5 ."' LIMIT 1");
					if(mysql_num_rows($query) == 0)
					{
						if( in_array( $extension, $cfg['thumbnail_filetypes'] ) )
						{
							if( $extension == "gif" )
							{
								$thumb_ext = "gif";
							}
							else if( $extension == "jpeg" OR $extension == "jpg" )
							{
								$thumb_ext = "jpg";
							}
							else
							{
								$thumb_ext = "png";
							}
							
							$dest_thumb = $cfg['srvdir'] ."/files/". $folder ."/thumb/". $dest_name .".". $thumb_ext;
							$dest_thumb_noanim = $cfg['srvdir'] ."/files/". $folder ."/thumb/noanim-". $dest_name .".". $thumb_ext;
							
							if( is_file( $dest_thumb ) )
							{
								if($filecount == 1)
									error(T_("Destination file already exists! Please try uploading again."));
								else
								{
									$skipped_files[] = T_("Destination file already exists! Please try uploading again.");
									continue;
								}
							}
							
							$thumbnail = create_image( $file['tmp_name'], $dest_thumb );
							
							if( $thumb_ext == "png" AND filesize( $dest_thumb ) > $cfg['png_thumbs_max_file_size'] AND $cfg['png_thumbs_max_file_size'] != false AND $thumbnail )
							{
								$dest_thumb_tmp = $cfg['srvdir'] ."/files/". $folder ."/thumb/". $dest_name .".jpg";
								if( !is_file( $dest_thumb_tmp ) )
								{
									create_image( $dest_thumb, $dest_thumb_tmp );
									unlink( $dest_thumb );
									$dest_thumb = $dest_thumb_tmp;
									$thumb_ext = "jpg";
								}
							}

							if(!$thumbnail) {
								if($filecount == 1)
									error( sprintf( T_( 'Thumbnail generation failed: %s.%s' ), $name, $extension ) );
								else {
									$skipped_files[] = sprintf( T_( 'Thumbnail generation failed: %s.%s' ), $name, $extension );
									continue;
								}
							}
							else {
								if( $thumb_ext == 'gif' )
								{
									$thumbnail_noanim = create_image( $dest_thumb, $dest_thumb_noanim, false );
									if(!$thumbnail_noanim)
									{
										if($filecount == 1)
											error( sprintf( T_( 'Thumbnail generation failed: %s.%s' ), $name, $extension ) );
										else {
											$skipped_files[] = sprintf( T_( 'Thumbnail generation failed: %s.%s' ), $name, $extension );
											continue;
										}
									}
								}
								
								$imgsize = getimagesize($file['tmp_name']);
								$information = mysql_real_escape_string($imgsize[0] ."x". $imgsize[1] ."px");
								
								if($imgsize[0] > $cfg['max_img_size_x'] OR $imgsize[1] > $cfg['max_img_size_y']) {
									if($filecount == 1)
										error(T_("Your image file is larger than the maxium allowed. File discarded."), true, false, false);
									else {
										$skipped_files[] = T_("Your image file is larger than the maxium allowed. File discarded.");
										continue;
									}
								}
								
								$dest = $cfg['srvdir'] ."/files/". $folder ."/orig/". $dest_name .".". $extension;
								
								if(is_file($dest)) {
									if($filecount == 1)
										error(T_("Destination file already exists! Please try uploading again."));
									else {
										$skipped_files[] = T_("Destination file already exists! Please try uploading again.");
										continue;
									}
								}
								
								// Do what you want cause a pirate is free
								$img = move_uploaded_file($file['tmp_name'], $dest);
								
								if($extension == "jpg" OR $extension == "jpeg") {
									if($cfg['use_jpegtran']) {
										$img = jpegtran( $dest, $clearexif );
									}
								}
								elseif($extension == "png") {
									if($cfg['use_optipng']) {
										$img = optipng($dest);
									}
								}
								elseif( $extension == "gif" )
								{
									if( $cfg['use_gifsicle'] )
									{
										$img = gifsicle( $dest, $clearexif );
									}
								}
								
								if(!$img) {
									@unlink($dest_thumb);
									@unlink($dest);
									if($filecount == 1)
										error( sprintf( T_( 'Saving the uploaded file has failed: %s.%s' ), $name, $extension ) );
									else {
										$skipped_files[] = sprintf( T_( 'Saving the uploaded file has failed: %s.%s' ), $name, $extension );
										continue;
									}
								}
								
								$size = mysql_real_escape_string(filesize($dest));
								list($width, $height, $ftype, $attr) = getimagesize($dest_thumb);
								
								$add = mysql_query("
									INSERT INTO `files`
									(`orig_name`, `name`, `extension`, `thumb_ext`, `thumb_width`, `thumb_height`, `mime`, `size`, `information`, `md5`, `folder`)
									VALUES
									('". $name ."', '". $dest_name ."', '". $extension ."', '". $thumb_ext ."', '". $width ."', '". $height ."', '". $mime ."', '". $size ."', '". $information ."', '". $md5 ."', '". $folder ."')
								");
							}
						}
						elseif($extension == "mp3" OR $extension == "ogg" OR $extension == "wma" OR $extension == "flac") {
							require_once($cfg['srvdir'] .'/inc/getid3/getid3.php');

							$dest = $cfg['srvdir'] ."/files/". $folder ."/orig/". $dest_name .".". $extension;
							move_uploaded_file($file['tmp_name'], $dest);
							
							$getID3 = new getID3;
							$getID3->encoding = 'UTF-8';
							$getID3->analyze($dest);
							$source_data = false;
							$imgmime = false;
							$title = '';
							$artist = '';
							$length = '';
							$kbps = '';
							
							if (isset($getID3->info['id3v2']['APIC'][0]['data']) && isset($getID3->info['id3v2']['APIC'][0]['image_mime'])) {
								$source_data = $getID3->info['id3v2']['APIC'][0]['data'];
								$imgmime = $getID3->info['id3v2']['APIC'][0]['image_mime'];
							}
							elseif (isset($getID3->info['id3v2']['PIC'][0]['data']) && isset($getID3->info['id3v2']['PIC'][0]['image_mime'])) {
								$source_data = $getID3->info['id3v2']['PIC'][0]['data'];
								$imgmime = $getID3->info['id3v2']['PIC'][0]['image_mime'];
							}
							// Title
							if(!empty($getID3->info['tags']['id3v2']['title'][0]))
								$title = $getID3->info['tags']['id3v2']['title'][0];
							elseif(!empty($getID3->info['id3v2']['TIT2'][0]['data']))
								$title = $getID3->info['id3v2']['TIT2'][0]['data'];
							// Artist
							if(!empty($getID3->info['tags']['id3v2']['artist'][0]))
								$artist = $getID3->info['tags']['id3v2']['artist'][0];
							elseif(!empty($getID3->info['id3v2']['TPE1'][0]['data']))
								$artist = $getID3->info['id3v2']['TPE1'][0]['data'];
							// Length
							if(!empty($getID3->info['playtime_string']))
								$length = $getID3->info['playtime_string'];
							// bps
							if(!empty($getID3->info['bitrate']))
								$kbps = round(($getID3->info['bitrate'] / 1000), 0) . "kbps";
							
							$title = mysql_real_escape_string($title);
							$artist = mysql_real_escape_string($artist);
							$length = mysql_real_escape_string($length);
							$kbps = mysql_real_escape_string($kbps);
							
							$thumb = '';
							$thumb_small = '';
							$image = '0';
							$thumbext = '';
							
							if($source_data) {
							
								// GD >_<
								$im = imagecreatefromstring($source_data);

								$thumbext = "jpg";
								$dest_thumb_b = $cfg['srvdir'] ."/tmp/". $dest_name .'.'. $thumbext;
								imagejpeg($im, $dest_thumb_b );
								
								$dest_thumb = $cfg['srvdir'] ."/files/". $folder ."/thumb/". $dest_name .'.'. $thumbext;

								$thumbnail = create_image( $dest_thumb_b, $dest_thumb );
								@unlink( $dest_thumb_b );
								if(!$thumbnail) {
									@unlink( $dest );
									if($filecount == 1)
										error( T_("Thumbnail generation failed!") );
									else {
										$skipped_files[] = T_("Thumbnail generation failed!");
										continue;
									}
								}
								$image = '1';
							}
							
							$size = mysql_real_escape_string(filesize($dest));
							list($width, $height, $ftype, $attr) = getimagesize($dest_thumb);
							
							$add = mysql_query("
								INSERT INTO `files`
								(`orig_name`, `name`, `extension`, `thumb_ext`, `thumb_width`, `thumb_height`, `mime`, `size`, `md5`, `id3_name`, `id3_artist`, `id3_length`, `id3_bitrate`, `id3_image`, `folder`)
								VALUES
								('". $name ."', '". $dest_name ."', '". $extension ."', '". $thumbext ."', '". $width ."', '". $height ."', '". $mime ."', '". $size ."', '". $md5 ."', '". $title ."', '". $artist ."', '". $length ."', '". $kbps ."', '". $image ."', '". $folder ."')
							");
						}
						elseif($extension == "mp4") {
							$dest = $cfg['srvdir'] ."/files/". $folder ."/orig/". $dest_name .".". $extension;
							if(move_uploaded_file($file['tmp_name'], $dest)) {
							
								$tmp_dir = $cfg['srvdir'] .'/tmp/';
								
								if(!is_dir($tmp_dir)) {
									@unlink( $dest );
									if($filecount == 1)
										error( T_('Temporary file directory is missing! Please create a folder called "tmp" to the board root.') );
									else {
										$skipped_files[] = T_('Temporary file directory is missing! Please create a folder called "tmp" to the board root.');
										continue;
									}
								}
								
								if($cfg['use_mp4box']) {
									$mp4box = $cfg['mp4box_bin'] .' -inter 500 -hint -tmp '. $tmp_dir .' '. escapeshellarg($dest);
									shell_exec($mp4box);
								}
								$size = mysql_real_escape_string(filesize($dest));
								
								$add = mysql_query("
									INSERT INTO `files`
									(`orig_name`, `name`, `extension`, `mime`, `size`, `md5`, `folder`)
									VALUES
									('". $name ."', '". $dest_name ."', '". $extension ."', '". $mime ."', '". $size ."', '". $md5 ."', '". $folder ."')
								");
							}
							else {
								if($filecount == 1)
									error( T_('Saving the uploaded file failed. Is there write access to the "files" -folder?') );
								else {
									$skipped_files[] = T_('Saving the uploaded file failed. Is there write access to the "files" -folder?');
									continue;
								}
							}
						}
						else {
							$dest = $cfg['srvdir'] ."/files/". $folder ."/orig/". $dest_name .".". $extension;
							if(move_uploaded_file($file['tmp_name'], $dest)) {
								$size = mysql_real_escape_string(filesize($dest));
								
								$add = mysql_query("
									INSERT INTO `files`
									(`orig_name`, `name`, `extension`, `mime`, `size`, `md5`, `folder`)
									VALUES
									('". $name ."', '". $dest_name ."', '". $extension ."', '". $mime ."', '". $size ."', '". $md5 ."', '". $folder ."')
								");
							}
							else {
								if($filecount == 1)
									error( T_('Saving the uploaded file failed. Is there write access to the "files" -folder?') );
								else {
									$skipped_files[] = T_('Saving the uploaded file failed. Is there write access to the "files" -folder?');
									continue;
								}
							}
						}
						if($add) {
							$inserted_files[] = mysql_insert_id();
							$fileid[] = mysql_insert_id();
						}
						else {
							@unlink( $dest );
							@unlink( $dest_thumb );
							if($filecount == 1)
								error( T_("Saving the file has failed!") );
							else {
								$skipped_files[] = T_("Saving the file has failed!");
								continue;
							}
						}
					}
					else {
						$fileid[] = mysql_result($query, 0, "id");
					}
					$file = true;
				}
				else {
					if($filecount == 1)
						error( sprintf(T_("File %s: Wrong extension (%s) for the file mime-type (%s)! This usually means that your file is faulty."), $file['name'], $extension, $mime), true, false, false );
					else {
						$skipped_files[] = sprintf(T_("File %s: Wrong extension (%s) for the file mime-type (%s)! This usually means that your file is faulty."), $file['name'], $extension, $mime);
						continue;
					}
				}
			}
			else {
				if($filecount == 1)
					error( sprintf(T_("The type of the file you tried to upload is not allowed (%s, %s)."), $extension, $mime), true, false, false );
				else {
					$skipped_files[] = sprintf(T_("The type of the file you tried to upload is not allowed (%s, %s)."), $extension, $mime);
					continue;
				}
			}
		}
		else {
			if($filecount == 1)
				error( sprintf(T_("Your file is too big (%s)! The maximum allowed file size is (%s)."), convert_filesize($file['size']), convert_filesize($cfg['max_filesize'])), true, false, false );
			else {
				$skipped_files[] = sprintf(T_("Your file is too big (%s)! The maximum allowed file size is (%s)."), convert_filesize($file['size']), convert_filesize($cfg['max_filesize']));
				continue;
			}
		}
	}
	$i++;
}

$errors = '';
foreach($skipped_files AS $filename => $error)
{
	$errors .= '<br />'. $error;
}

if( count( $skipped_files ) == count( $files ) AND count( $skipped_files ) != 0 )
{
	error( 	T_("All of your files had errors while uploading. This post will not be saved.") .	$errors, true, false, false	);
}

if(empty($files) AND $cfg['embedandfile'] == false OR $cfg['embedandfile'] == true) {
//Upote, tarkistetaan vain jos tiedostoa ei ole tai molemmat on sallittu yhtäaikaa
	if(!empty($_POST['embed']) AND !empty($_POST['embedtype']) AND $cfg['allow_embeds']) {
	
		// Fix for Youtube autoplay
		if( !preg_match("/([\w\_\-\/]*)/", $_POST['embed'], $embed) OR empty($embed[1]))
			error(T_("Malformed embed-ID"));
		$_POST['embed'] = $embed[1];

		$embed = mysql_real_escape_string($_POST['embed']);
		$embedtype = mysql_real_escape_string($_POST['embedtype']);
		
		$query = mysql_query("SELECT `id`, `video_url`, `sfw` FROM `embed_sources` WHERE `id` = '". $embedtype ."' LIMIT 1");
		if(mysql_num_rows($query) != 0) {
			$embedurl = mysql_result($query, 0, "video_url");
			$embedtype = mysql_result($query, 0, "id");
			$embedsfw = mysql_result($query, 0, "sfw");
			
			if( $worksafe == '1' AND $embedsfw == '0' )
			{
				delete_file_array($inserted_files);
				error(T_("Posting a NSFW-embed is disallowed to SFW boards!"), true, false, false);
			}

			$foo = check_link($embedurl . $embed);
			if($foo == "HTTP/1.0 404 Not Found")
			{
				delete_file_array($inserted_files);
				error(sprintf(T_("Embedding failed. Checking %s%s resulted in an HTTP-404 error."), $embedurl, $embed), true, false, false);
			}
			elseif($foo == "HTTP/1.0 303 See Other")
			{
				delete_file_array($inserted_files);
				error(T_("Malformed embed-ID"));
			}
			elseif($foo != "HTTP/1.0 301 Moved Permanently" AND $foo != "HTTP/1.0 302 Found" AND $foo != "HTTP/1.0 200 OK" AND $foo != "HTTP/1.1 200 OK")
			{
				delete_file_array($inserted_files);
				error(sprintf(T_("Embedding failed. Embed source could not be confirmed because an error occurred at the source. (%s)"), (!empty($foo) ? $foo : '???')), true, false, false);
			}
			
			$embedded = true;
		}
		else
		{
			delete_file_array($inserted_files);
			error(T_("Malformed embed type!"));
		}
	}
}

if(!$embedded) {
	$embed = '';
	$embedtype = '';
}

if(!$file AND !$embedded AND !$answer) {
	delete_file_array($inserted_files);
	error(T_("A file or an embed is required to make a new thread!"), true, false, false);
}

// Spämmifiltteri
if($thread == 0) {
	$get = mysql_query("SELECT `id` FROM `posts` WHERE `thread` = '0' AND `ip` = '". $ip ."' AND `time` >= '". (time() - $cfg['threadlimit']) ."'");
}
else {
	$get = mysql_query("SELECT `id` FROM `posts` WHERE `ip` = '". $ip ."' AND `time` >= '". (time() - $cfg['floodlimit']) ."'");
}
if(mysql_num_rows($get) != 0)
	error(T_("You are posting too fast. Please wait a while beetween your posts."), true, false, false);
	
//Tönitäänkö?
$bumped_a = '';
$bumped_b = '';
if(!$sage AND $answer) {
	$haq = mysql_query("SELECT COUNT(`id`) AS 'count' FROM `posts` WHERE `thread` = '". $thread ."' LIMIT 1");
	if(mysql_num_rows($haq) != 0) {
		$count = mysql_result($haq, 0, "count");
		if($count < $cfg['bumplimit']) {
			mysql_query("UPDATE `posts` SET `bump_time` = '". time() ."' WHERE `id` = '". $thread ."' LIMIT 1");
		}
	}
}

if(!$answer) {
	$bumped_a = ', `bump_time`';
	$bumped_b = ", '". time() ."'";
}

$sage = ($sage? 1 : 0);
$rage = ($rage? 1 : 0);
$love = ($love? 1 : 0);

// Modpost
if($cfg['user_class'] >= 1 AND $cfg['user_class'] <= 3) $authorized = true;
else $authorized = false;
if($modpost AND $authorized) {
	if($cfg['user_class'] == 1)
		$modpost = 1;
	elseif($cfg['user_class'] == 2)
		$modpost = 2;
	elseif($cfg['user_class'] == 3)
		$modpost = 3;
	else
		$modpost = 0;
		
	write_modlog(3);
}
else $modpost = 0;

$password = encrypt_password($password);

// Poster name
if($namefield == 1) {
	// Matkakoodi, tripcode
	$name_b = explode("##", $postername);
	if(count($name_b) == 1) {
		$name_b = explode("#", $postername);
		if(count($name_b) == 1) {		
			$name_b = explode("!", $postername);
			if(count($name_b) == 1) {		
				$tripcode = '';
			}
			else {
				$pw = array_pop($name_b);
				$tripcode = '!'. mktripcode($pw);
			}
		}
		else {
			$pw = array_pop($name_b);
			$tripcode = '!'. mktripcode($pw);
		}
	}
	else {
		$pw = array_pop($name_b);
		$tripcode = '!!'. mksecuretripcode($pw);
	}
	$tripcode = mysql_real_escape_string($tripcode);

	$postername = $name_b[0];
	$posted_by_op = '0';
}
elseif($namefield == 2) {
	//AP-nimi
	if($thread != 0 AND $op) {
		$q = mysql_query("SELECT `ip`, `password` FROM `posts` WHERE `id` = '". $thread ."' LIMIT 1");
		if(mysql_num_rows($q) != 0) {
			$ipa = mysql_result($q, 0, "ip");
			$ipb = encrypt_ip(get_ip());
			$ssa = mysql_result($q, 0, "password");
			$ssb = encrypt_password($_POST['password']);
			if($ipa == $ipb OR $ssa == $ssb) $posted_by_op = '1';
			else $posted_by_op = '2';
		}
	}
	elseif($thread == 0 AND $op) $posted_by_op = '1';
	else $posted_by_op = '0';
	$tripcode = false;
}

$add = mysql_query("
	INSERT INTO	`posts`(
		`board`,
		`thread`,
		`uid`,
		`ip`,
		`ip_plain`,
		`proxy`,
		`geoip_country_code`,
		`geoip_country_name`,
		`geoip_region_code`,
		`geoip_region_name`,
		`geoip_city`,
		`geoip_lat`,
		`geoip_lon`,
		`name`,
		`tripcode`,
		`posted_by_op`,
		`modpost`,
		`time`,
		`subject`,
		`message`,
		`password`,
		`embed_source`,
		`embed_code`,
		`sage`,
		`rage`,
		`love`
		". $bumped_a ."
	)
	VALUES (
		'". $board ."',
		'". $thread ."',
		'". $cfg['user']['uid'] ."',
		'". $ip ."',
		'". mysql_real_escape_string($_SERVER['REMOTE_ADDR']) ."',
		'". mysql_real_escape_string($proxy) ."',
		'". mysql_real_escape_string($geoip_data['country_code']) ."',
		'". mysql_real_escape_string(utf8_encode($geoip_data['country_name'])) ."',
		'". mysql_real_escape_string($geoip_data['region']) ."',
		'". mysql_real_escape_string(utf8_encode($geoip_data['region_name'])) ."',
		'". mysql_real_escape_string(utf8_encode($geoip_data['city'])) ."',
		'". mysql_real_escape_string($geoip_data['latitude']) ."',
		'". mysql_real_escape_string($geoip_data['longitude']) ."',
		'". $postername ."',
		'". $tripcode ."',
		'". $posted_by_op ."',
		'". $modpost ."',
		'". time() ."',
		'". $subject ."',
		'". $message ."',
		'". $password ."',
		'". $embedtype ."',
		'". $embed ."',
		'". $sage ."',
		'". $rage ."',
		'". $love ."'
		". $bumped_b ."
	)
");

if($add)
{

	$postid = mysql_insert_id();
	$i = 0;
	foreach($fileid AS $file)
	{
		if($file == '0')
			continue;
			
		mysql_query("INSERT INTO `post_files` (`postid`, `fileid`, `order`) VALUES ('". $postid ."', '". $file ."', '". $i ."')");
		++$i;
	}

	if($noko)
	{
		$pagesq = mysql_query("SELECT COUNT(`id`) AS 'count' FROM `posts` WHERE `deleted_time` = '0' AND `thread` = '". $thread ."'");
		$pages = ceil(mysql_result($pagesq, 0, "count") / $cfg['ppp']);
		if( !$answer ) $thread = $postid;
		
		$redirto = $cfg['htmldir'] .'/'. $url .'/'. $thread .'-'. $pages .'/#hl_'. $postid;
	}
	elseif( !empty($_POST['overboard']) AND $_POST['overboard'] == 'true' )
	{
		$redirto = $cfg['htmldir'] .'/'. $cfg['overboard_url'] .'/';
	}
	else
	{
		$redirto = $cfg['htmldir'] .'/'. $url .'/';
	}
		
	if( count( $skipped_files ) != 0 )
	{
		info(
			T_("There were errors when uploading some of your files. These files were discarded, but your post was saved.") .
			'<br /><br />'. $errors .
			'<br /><br /><a href="'. $redirto .'">'. T_("Click here to continue") .'</a>'
		);
	}

	header("Location: ". $redirto);
}
else {
	delete_file_array($inserted_files);
	error(T_("An error occurred while saving your message!"));
}

?>
