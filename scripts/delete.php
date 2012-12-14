<?php
// Northpole.fi
// Viestien poisto ja ilmianto
// 15.2.2010
$nostatsupdate = true;
require_once("../inc/include.php");


$delete = false;
$report = false;
$download = false;

if(!empty($_POST['reason']) OR isset($_POST['report'])) {
	$report = true;
}
elseif(isset($_POST['delete'])) {
	$delete = true;
}
elseif(!empty($_POST['filename']) OR isset($_POST['download'])) {
	$download = true;
	$files = array();
	$files_size = 0;
}

$infos = array();

foreach($_POST AS $key => $value) {
	if(substr($key, 0, 6) != "delete")
		continue;
	
	$id = substr($key, 6);
	
	if(!is_numeric($id)) continue;
	
	if($delete) {
		if(empty($_POST['passwd'])) error(T_("Password is missing!"));

		if(!empty($_POST['onlyfile']) AND $_POST['onlyfile'] == "on") $onlyfile = true;
		else $onlyfile = false;
		
		if($cfg['user_class'] >= 1 AND $cfg['user_class'] <= 3) $mod = true;
		else $mod = false;
		
		$info = delete_post($id, $_POST['passwd'], $onlyfile, $mod);
		$infos[$id] = $info;
	}
	elseif($report) {
		if(empty($_POST['reason'])) error(T_("You cannot report without a reason!"));
		$reason = mysql_real_escape_string(htmlspecialchars($_POST['reason']));
		$ip = mysql_real_escape_string(encrypt_ip(get_ip()));
		$time = time();
		$id = mysql_real_escape_string($id);
		$q = mysql_query("SELECT `id` FROM `reports` WHERE `message` = '". $id ."'");
		if(mysql_num_rows($q) == 0) {
			$qb = mysql_query("SELECT `id` FROM `posts` WHERE `id` = '". $id ."' LIMIT 1");
			if(mysql_num_rows($qb) == 1) {
				mysql_query("INSERT INTO `reports`(`message`, `reason`, `reported_by`, `time`) VALUES('". $id ."', '". $reason ."', '". $ip ."', '". $time ."')");
				$infos[$id] = T_("Message reported.");
			}
			else {
				$infos[$id] = T_("The message you tried to report does not exist.");
			}
		}
		else
			$infos[$id] = T_("This message has been reported already");
	}
	elseif($download) {
		
		$files_tmp = array();
		
		$q = mysql_query("SELECT `post_files`.`fileid`, `files`.`name`, `files`.`folder`, `files`.`size`, `files`.`extension`, `files`.`orig_name` FROM `post_files`, `files` WHERE `files`.`id` = `post_files`.`fileid` AND `post_files`.`postid` = '". $id ."'");
		while($fileids = mysql_fetch_assoc($q)) {
			$files_tmp[] = $fileids['fileid'];
			$files_size += $fileids['size'];
			$files[$fileids['folder'] .'/orig/'. $fileids['name'] .'.'. $fileids['extension']] = $fileids['orig_name'] .'.'. $fileids['extension'];
		}
	}
}

if($download) {
	if(count($files) != 0) {
		if($files_size <= $cfg['max_dl_filesize']) {
			if($cfg['use_tmpfs'] AND $files_size <= $cfg['max_tmpfs_filesize'])
				$archive = "/dev/shm/";
			else
				$archive = $cfg['srvdir'] .'/tmp/';
		
			$archive_fn = 'np-'. date("dmyHi");
			while(is_file($archive .'.zip')) {
				$archive_fn .= "-". mt_rand(0, 999999);
			}
			$archive_fn .= '.zip';

			$archive .= $archive_fn;
			$zip = new ZipArchive();

			if($zip->open($archive, ZIPARCHIVE::CREATE) != true) {
				error(T_("Generating the archive failed!"));
			}

			$filebase = $cfg['srvdir'] .'/files/';
			foreach($files AS $file => $name) {
				if(is_file($filebase.$file)) {
					$zip->addFile($filebase.$file, $name);
				}
			}
			if(!$zip->close())
				error(T_("Generating the archive failed!"));
				
			if(!is_file($archive)) {
				error(T_("Opening the generated archive failed!"));
			}
			
			if(empty($_POST['filename']))
			{
				$filename = $archive_fn;
			}
			else {
				$filename = $_POST['filename'];
				$filename = preg_replace("/[^a-z0-9\-\_\ \ä\ö\å]/i", "_", $filename);
				$filename .= ".zip";
			}
				
			header("Content-type: application/zip"); 
			header("Content-Disposition: attachment; filename=". $filename); 
			header("Content-Length: ". filesize($archive)); 
			
			header("Pragma: no-cache"); 
			header("Expires: 0"); 

			readfile($archive); 
			unlink($archive);
			
		}
		else error(sprintf(T_("The files you have selected exceeds the maxium size of a downloadable archive (%s)!"), convert_filesize($cfg['max_dl_filesize'])), true, false, false);
	}
	else error(T_("You did not select any posts with files to download."), true, "2;url=". $cfg['htmldir'] . urldecode($_POST['url']), true, false, false);
}
else {

	$foo = '';
	foreach($infos AS $id => $info) {
		$foo .= '
		'. T_("ID") . $id .": ". $info ."<br />";
	}

	$foo .= '<br />'. T_("Returning to the board in 2 seconds...");

	if( !empty($_POST['open_thread']) )
	{
		$open_thread = mysql_real_escape_string($_POST['open_thread']);
		$q = mysql_query("SELECT `id` FROM `posts` WHERE `id` = '". $open_thread ."' LIMIT 1");
	
		if(mysql_num_rows($q) == 1)
			$goto = urldecode( $_POST['url'] );
		else
			$goto = '/'. $_POST['open_board'] .'/';
	}
	elseif( !empty($_POST['open_board']) )
		$goto = '/'. $_POST['open_board'] .'/';
	elseif( !empty($_POST['url']) )
		$goto = urldecode($_POST['url']);
	else
		$goto = '';
	
	info($foo, true, "2;url=". $cfg['htmldir'] . $goto);
}

?>
