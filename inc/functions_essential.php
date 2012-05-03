<?php

// http://www.php.net/manual/en/ref.fileinfo.php
if(!function_exists('mime_content_type')) {
	function mime_content_type($file) {
		
		escapeshellarg(realpath($file));
		if(is_file($file)) {
			$type = `/usr/bin/file -i -b `. $file;
			$parts = explode(';', $type);

			return trim($parts[0]);
		}
		else
			return false;
	}
}

?>
