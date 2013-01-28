<?php
// Northpole.fi
// Postaustoiminnot
// 15.2.2010

// Matkakoodit
// http://community.livejournal.com/avimedia/1583.html
function mktripcode($pw)
{
	global $cfg;
	
    $pw = mb_convert_encoding($pw, 'SJIS', 'UTF-8');
    $pw = str_replace('&', '&amp;', $pw);
    $pw = str_replace('"', '&quot;', $pw);
    $pw = str_replace("'", '&#39;', $pw);
    $pw = str_replace('<', '&lt;', $pw);
    $pw = str_replace('>', '&gt;', $pw);
    
    $salt = substr($pw .'H.', 0, 3);
    $salt = preg_replace('/[^.\/0-9:;<=>?@A-Z\[\\\]\^_`a-z]/', '.', $salt);
    $salt = strtr($salt, ':;<=>?@[\]^_`', 'ABCDEFGabcdef');
    
    $trip = substr(crypt($pw, $salt), -10);
    return $trip;
}
function mksecuretripcode($pw)
{
	global $cfg;
	
	$pw = mktripcode($pw);
	
	$salt = hash("sha512", $cfg['st_salt']);
	$pw = crypt($pw, $salt);
	
    $trip = substr(crypt($pw, $salt), -10);
	
	return $trip;
}

// Upotteet
function check_link($link)
{
	global $cfg;

	$headers = get_headers($link);
	$httpCode = $headers[0];
	return $httpCode;
}

// Kuvien kopiointi/pienennys
function create_image( $source, $destination, $animooted = true ) {
	global $cfg;
	
	// Tarkistetaan ettei kuvaa jo ole
	if(is_file($destination)) return false;
	else
	{
		if( $cfg['convert_lower_priority'] )
		{
			if( !is_file( $cfg['nice_bin'] ) ) error( sprintf( T_("%s binary was not found from %s!" ), "Nice", $cfg['nice_bin'] ) );
			$nice = $cfg['nice_bin'] .' -n 10 ';
		}
		else $nice = '';

		if( $cfg['use_imagick'] )
		{
			if( !is_file( $cfg['imagick_bin'] ) ) error( sprintf( T_( "%s binary was not found from %s!" ), "ImageMagick", $cfg['imagick_bin'] ) );
			$imagick = $nice . $cfg['imagick_bin'];
		}

		if( $cfg['use_gifsicle'] )
		{
			if( !is_file( $cfg['gifsicle_bin'] ) ) error( sprintf( T_( "%s binary was not found from %s!" ), "gifsicle", $cfg['gifsicle_bin'] ) );
			$gifsicle = $nice . $cfg['gifsicle_bin'];
		}

		if( $cfg['use_jpegtran'] )
		{
			if( !is_file( $cfg['jpegtran_bin'] ) ) error( sprintf( T_( "%s binary was not found from %s!" ), "jpegtran", $cfg['jpegtran_bin'] ) );
			$jpegtran = $nice . $cfg['jpegtran_bin'];
		}

		if( $cfg['use_pngcrush'] )
		{
			if( !is_file( $cfg['pngcrush_bin'] ) ) error( sprintf( T_( "%s binary was not found from %s!" ), "pngcrush", $cfg['pngcrush_bin'] ) );
			$pngcrush = $nice . $cfg['pngcrush_bin'];
		}

		if( $cfg['use_optipng'] )
		{
			if( !is_file( $cfg['optipng_bin'] ) ) error( sprintf( T_( "%s binary was not found from %s!" ), "optipng", $cfg['optipng_bin'] ) );
			$optipng = $nice . $cfg['optipng_bin'];
		}

		$maxwidth = $cfg['thumbsize_x'];
		$maxheight = $cfg['thumbsize_y'];
		$put_caption = false;
		$extension = pathinfo($destination, PATHINFO_EXTENSION);

		$image = new imagick();
		if( $image->readImage( $source .'[0]' ) != true ) return false;
		if( $image->getImageFormat() == "GIF" )
		{
			$image->destroy();
			$image = new imagick();
			if( $image->readImage( $source ) != true ) return false;
		}

		// gifsicle is faster than imagick
		if($image->getImageFormat() == "GIF" AND $cfg['use_gifsicle'])
		{

			$com = $gifsicle .' --colors 255 '. escapeshellarg($source) .' ';

			if($image->getNumberImages() > 1)
			{
				if( !$animooted OR $extension != 'gif' )
				{
					$com .= ' --delete "#1-" ';
					if($cfg['gif_caption']) $put_caption = true;
				}
			}

			//if(!$cfg['high_thumb_quality'])
			//{
				$sizes = $image->getImageGeometry();
				if($sizes['width'] > $maxwidth OR $sizes['height'] > $maxheight)
				{
					if($sizes['width'] > $sizes['height']) $com .= ' --resize-width '. $maxwidth;
					else $com .= ' --resize-height '. $maxheight;
				}
			//}

			$com .= ' -o '. escapeshellarg($destination);
			shell_exec($com);

			//$com = $gifsicle .' -U -b '. escapeshellarg($destination);
			//shell_exec($com);

			$image->destroy();
			$image = new imagick($destination);
			$writeImage = false;
		}

		if($image->getImageFormat() == "JPEG" AND $cfg['use_jpegtran'])
		{
			$com = $jpegtran .' -copy none -outfile '. escapeshellarg($destination) .' '. escapeshellarg($source);
			shell_exec($com);

			if( filesize( $destination ) == 0 )
			{
				unlink( $destination );
				return false;
			}

			$image->destroy();
			$image = new imagick($destination);
			$writeImage = false;
		}

		if( $image->getNumberImages() > 1 )
		{
			if( !$animooted OR $extension != 'gif' )
			{
				$image_tmp = new Imagick();
				foreach ( $image as $frame )
				{
					$image_tmp->addImage( $frame->getImage() );
					break;
				}
				$image->destroy();
				$image = $image_tmp;
				if($cfg['gif_caption']) $put_caption = true;
				$writeImage = true;
			}
		}

		$sizes = $image->getImageGeometry();

		if($sizes['width'] > $maxwidth OR $sizes['height'] > $maxheight OR $image->getImageFormat() == "SVG")
		{
			if($cfg['high_thumb_quality'])
			{
				if($extension == "gif" AND $image->getNumberImages() > 1)
				{
					/*if( $cfg['use_imagick'] )
					{
						$image->writeImages($destination, true);
						$image->destroy();
						$com = $imagick .' '. escapeshellarg( $destination ) .' -resize "'. $maxwidth .'x'. $maxheight .'>" -level 0,100.01% -ordered-dither o4x4,16 +dither -colors 255 '. escapeshellarg( $destination );
						shell_exec($com);
						$image = new imagick($destination);
					}
					else
					{*/
						$filter = $cfg['anim_thumbs_resize_filter'];
						foreach ($image as $frame)
						{
							$frame->resizeImage($maxwidth, $maxheight, $filter, 1.0, true);
							$frame->levelImage(0, 1.0, 65535+1); // to circumvent a bug in imagick
							$frame->orderedPosterizeImage('o4x4,16');
						}
					//}
				}
				else $image->resizeImage($maxwidth, $maxheight, imagick::FILTER_CATROM, 1.0, true);
			}
			else
				foreach ($image as $frame) $frame->resizeImage($maxwidth, $maxheight, imagick::FILTER_POINT, 1.0, true);
			$writeImage = true;
		}
		
		if($put_caption)
		{
			$draw = new ImagickDraw();
			$draw->setGravity(imagick::GRAVITY_SOUTH);
			$draw->setFontSize($cfg['gif_caption_font_size']);
			$draw->setFillColor('#000c');
			$image->annotateImage($draw,  1, -1, 0, $cfg['gif_caption_text']);
			$image->annotateImage($draw, -1,  1, 0, $cfg['gif_caption_text']);
			$image->annotateImage($draw, -1, -1, 0, $cfg['gif_caption_text']);
			$image->annotateImage($draw,  1,  1, 0, $cfg['gif_caption_text']);
			$draw->setFillColor('white');
			//$draw->setTextUnderColor("#0008");
			$image->annotateImage($draw,  0,  0, 0, $cfg['gif_caption_text']);
			$writeImage = true;
		}

		if($extension == "jpg" OR $extension == "jpeg")
		{
			$image->setSamplingFactors(array(2, 1, 1));
			$image->setImageCompressionQuality($cfg['thumbquality']);
			$writeImage = true;
		}

		if($extension == "png")
		{
			$image->setImageCompressionQuality( $cfg['png_compression'] );
			$writeImage = true;
		}

		if( isset( $writeImage ) AND $writeImage )
			$image->writeImages($destination, true);

		if($extension == "gif" AND $cfg['use_gifsicle'])
		{
			$options = $cfg['gifsicle_options_thumb'];
			$com = $gifsicle .' '. $options .' -b '. escapeshellarg( $destination );
			shell_exec($com);
		}

		if( $extension == "png" AND $cfg['use_pngcrush'] )
		{
			shell_exec( $pngcrush .' '. $cfg['pngcrush_options_thumb'] .' '. escapeshellarg( $destination ) .' '. escapeshellarg( $destination .'.tmp' ) );
			unlink( $destination );
			rename( $destination .'.tmp', $destination );
		}

		if( $extension == "png" AND $cfg['use_optipng'] )
		{
			shell_exec( $optipng .' '. $cfg['optipng_options_thumb'] .' '. escapeshellarg( $destination ) );
		}

		if(is_file($destination)) return true;
		else return false;
	}
}

function jpegtran( $source, $clearexif = true )
{
	global $cfg;
	
	$source_c = escapeshellarg( $source );
	if( !$cfg['convert_memory_limit'] )
		$maxmem = '';
	else
		$maxmem = ' -maxmemory '. $cfg['convert_memory_limit'] * 1024;
	
	if( $clearexif ) $copy = 'none';
	else $copy = 'all';
	
	$com = $cfg['jpegtran_bin'] .' -optimize -copy '. $copy . $maxmem .' -outfile '. $source_c .' '. $source_c;
	shell_exec( $com );
	
	if( is_file( $source ) AND filesize( $source ) > 0 ) return true;
	else return false;
}

function optipng( $source )
{
	global $cfg;
	
	$tmpfile = $cfg['srvdir'] ."/tmp/". time() . mt_rand( 0, 999999 ) .".png";
	rename( $source, $tmpfile );
	
	$tmpfile_c = escapeshellarg( $tmpfile );
	$source_c = escapeshellarg( $source );

	$com = $cfg['optipng_bin'] .' -o0 -out '. $source_c .' '. $tmpfile_c;
	shell_exec( $com );
	
	if( !is_file( $source ) )
	{
		$com = $cfg['optipng_bin'] .' -fix -o1 -out '. $source_c .' '. $tmpfile_c;
		shell_exec( $com );
	}

	unlink( $tmpfile );
	
	if( is_file( $source ) ) return true;
	else return false;
}

function gifsicle( $source, $clearexif = true )
{
	global $cfg;
	
	$com = $cfg['gifsicle_bin'] . ( $clearexif ? ' +x +c' : '' ) .' -b '. escapeshellarg( $source );
	shell_exec( $com );
	
	return true;
}

?>
