<?php
die();

if($_GET['a'] == '300' OR $_GET['a'] == '600' OR $_GET['a'] == '900' OR $_GET['a'] == '1200' OR $_GET['a'] == '1500')
	die("RDY!");
	
include("inc/include.php");

$lim = mysql_real_escape_string($_GET['a']) * 1;

$q = mysql_query("SELECT * FROM `files` WHERE `thumb_ext` = 'gif' LIMIT ". $lim .", 1");

if(mysql_num_rows($q) == 0) die("The end.");

echo '
<html>
<head>
<meta http-equiv="refresh" content="0;url=reconvert_images.php?a='. ($_GET['a'] + 1) .'" >
</head>
<body>
';
while($a = mysql_fetch_assoc($q))
{

	if( $a['extension'] == "gif" ) {
		$thumb_ext = "gif";
	}
	else if( $a['extension'] == "jpeg" OR $a['extension'] == "jpg" ) {
		$thumb_ext = "jpg";
	}
	else {
		$thumb_ext = "png";
	}
	
	mkdir($cfg['srvdir'] ."/files/". $a['folder'] ."/thumb/noanim");
	
	$dest_thumb = $cfg['srvdir'] ."/files/". $a['folder'] ."/thumb/". $a['name'] .".". $thumb_ext;
	$dest_thumb_noanim = $cfg['srvdir'] ."/files/". $a['folder'] ."/thumb/noanim-". $a['name'] .".". $thumb_ext;
/*
	if($a['extension'] == "mp3" OR $a['extension'] == "ogg" OR $a['extension'] == "wma" OR $a['extension'] == "flac") {
		require_once($cfg['srvdir'] .'/inc/getid3/getid3.php');

		$dest = $cfg['srvdir'] ."/files/". $a['folder'] ."/orig/". $a['name'] .".". $thumb_ext;

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
		else
		{
			@unlink($dest_thumb);
			echo $dest_thumb .' removed<br />';
			mysql_query("UPDATE `files` SET `thumb_ext` = '', `thumb_width` = '', `thumb_height` = ''  WHERE `id` = '". $a['id'] ."' LIMIT 1");
			continue;
		}

	}
	*/
	@unlink($dest_thumb);
	@unlink($dest_thumb_noanim);
	//if(is_file($dest_thumb))
	//{
	//	echo 'Destination file already exists! Please try uploading again.';
		//continue;
	//}

	$orig = $cfg['srvdir'] ."/files/". $a['folder'] ."/orig/". $a['name'] .".". $a['extension'];
	//echo $orig;
	
	//die();
	$thumbnail = create_image( $orig, $dest_thumb );
	
	if( $thumb_ext == 'gif' )
	{
		$thumbnail_noanim = create_image( $orig, $dest_thumb_noanim, false );
	}
	
	if( $thumb_ext == "png" AND filesize( $dest_thumb ) > $cfg['png_thumbs_max_file_size'] AND $cfg['png_thumbs_max_file_size'] != false AND $thumbnail )
	{
		$dest_thumb_tmp = $cfg['srvdir'] ."/files/". $a['folder'] ."/thumb/". $a['name'] .".jpg";
		@unlink($dest_thumb_tmp);
		create_image( $dest_thumb, $dest_thumb_tmp );
		unlink( $dest_thumb );
		$dest_thumb = $dest_thumb_tmp;
		$thumb_ext = "jpg";
	}

	if(!$thumbnail OR !$thumbnail_noanim) {
			echo 'Thumbnail generation failed';
	}
	else {
		echo $dest_thumb .' '. $dest_thumb_noanim .' converted<br />';
	}

}

?>
</body>
</html>
