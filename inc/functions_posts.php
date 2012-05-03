<?php
// NorthBoard
// Post related functions
// 13.4.2010


function delete_file_single( $id )
{
	global $cfg;

	$id = mysql_real_escape_string($id);
	
	$fsearch = mysql_query("SELECT `folder`, `name`, `extension`, `thumb_ext` FROM `files` WHERE `id` = '". $id ."' LIMIT 1");
	if(mysql_num_rows($fsearch) != 0) {
		$folder = mysql_result($fsearch, 0, "folder");
		$filename = mysql_result($fsearch, 0, "name");
		$ext = mysql_result($fsearch, 0, "extension");
		$thumb_ext = mysql_result($fsearch, 0, "thumb_ext");
		mysql_query("DELETE FROM `files` WHERE `id` = '". $id ."' LIMIT 1");
		mysql_query("DELETE FROM `post_files` WHERE `fileid` = '". $id ."'");
		
		if(is_file($cfg['srvdir'] ."/files/". $folder ."/orig/". $filename .'.'. $ext))
			unlink($cfg['srvdir'] ."/files/". $folder ."/orig/". $filename .'.'. $ext);
		if(is_file($cfg['srvdir'] ."/files/". $folder ."/thumb/". $filename .'.'. $thumb_ext))
			unlink($cfg['srvdir'] ."/files/". $folder ."/thumb/". $filename .'.'. $thumb_ext);
			
		return true;
	}
	else return false;
}

function delete_file_array( $arr )
{
	global $cfg;

	foreach( $arr AS $id )
	{
		delete_file_single( $id );
	}
}

function delete_files_from_post( $postid )
{
	global $cfg;

	$q = mysql_query("SELECT `fileid` FROM `post_files` WHERE `postid` = '". mysql_real_escape_string($postid) ."'");
	
	while( $row = mysql_fetch_assoc($q) )
	{
		$qb = mysql_query("SELECT `postid` FROM `post_files` WHERE `fileid` = '". $row['fileid'] ."' AND `postid` != '". mysql_real_escape_string($postid) ."'");

		if(mysql_num_rows( $qb ) == 0)
			delete_file_single( $row['fileid'] );
		else
			mysql_query("DELETE FROM `post_files` WHERE `fileid` = '". $row['fileid'] ."' AND `postid` = '". mysql_real_escape_string($postid) ."'");
	}
}

function delete_post($id, $password, $onlyfile = false, $mod = false)
{
	global $cfg;
	
	$id = mysql_real_escape_string($id);
	
	$password = encrypt_password($password);
	$password = mysql_real_escape_string($password);
	
	$msg = T_("Post deleted");
	
	$query = mysql_query("SELECT `password`, `thread`, `time` FROM `posts` WHERE `id` = '". $id ."' LIMIT 1");
	if(mysql_num_rows($query) != 0) {
		$passwd_sql = mysql_result($query, 0, "password");
		$thread = mysql_result($query, 0, "thread");
		$post_time = mysql_result($query, 0, "time");
		if($password == $passwd_sql OR $mod)
		{
			if(!$onlyfile)
			{
			
				$time = time() - $cfg['min_post_age'];
				if($post_time <= $time OR $mod)
				{
					if($thread == 0)
					{
						mysql_query("UPDATE `posts` SET `deleted_time` = UNIX_TIMESTAMP() WHERE `id` = '". $id ."' OR `thread` = '". $id ."'");
						mysql_query("DELETE FROM `hide` WHERE `thread` = '". $id ."'");
						mysql_query("DELETE FROM `follow` WHERE `thread` = '". $id ."'");
					}
					else
					{
						mysql_query("UPDATE `posts` SET `deleted_time` = UNIX_TIMESTAMP() WHERE `id` = '". $id ."'");
						
						// Revert back the bump_time.
						$q = mysql_query("SELECT `time` FROM `posts` WHERE `thread` = '". $thread ."' AND `deleted_time` = '0' AND `sage` = '0' ORDER BY `time` DESC LIMIT 1");
						if( mysql_num_rows( $q ) == 0 )
						{
							$q = mysql_query("SELECT `time` FROM `posts` WHERE `id` = '". $thread ."' ORDER BY `time` DESC LIMIT 1");
						}
						
						$time = mysql_result( $q, 0, 'time' );
						
						if( $time != 0 )
						{
							mysql_query("UPDATE `posts` SET `bump_time` = '". $time ."' WHERE `id` = '". $thread ."' LIMIT 1");
						}
						
					}
					
					if($mod AND ($password != $passwd_sql OR $post_time > $time)) {
						//write_modlog(2);
					}
				}
				else $msg = T_("This message cannot be deleted yet because it was posted only a while ago.");
			}
			else {
				delete_files_from_post($id);
				
				if($mod AND $password != $passwd_sql) {
					write_modlog(1);
				}
			}
		}
		else $msg = T_("Wrong password!");
	}
	else $msg = T_("The message requested for deletion cannot be found!");
	
	return $msg;
}

function purgePostBin()
{
	global $cfg;
	
	$delete_time_max = time() - $cfg['post_bin_ttl'] * 86400; // Convert days into seconds
	
	$query = mysql_query("SELECT `id` FROM `posts` WHERE `deleted_time` <= '". $delete_time_max ."' AND `deleted_time` != '0'");
	mysql_query("DELETE FROM `posts` WHERE `deleted_time` <= '". $delete_time_max ."' AND `deleted_time` != '0'");
	
	while( $res = mysql_fetch_assoc($query) )
	{
		delete_files_from_post($res['id']);
	}
}

function delete_all_posts_by_ip_hash($ip_hash)
{
  $ip_hash = mysql_real_escape_string($ip_hash);
  $q = mysql_query("SELECT `id` FROM `posts` WHERE `ip` = '".$ip_hash."' AND `deleted_time` = '0'");
  $count = mysql_num_rows($q);
  while($row = mysql_fetch_array($q))
  {
    delete_post( $row[0], false, false, true );
  }   
  return $count;
}
?>
