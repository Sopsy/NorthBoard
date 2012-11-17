<?php
require_once("inc/include.php");

if( !empty( $_GET['msg'] ) AND is_numeric( $_GET['msg'] ) )
{
	$id = mysql_real_escape_string( $_GET['msg'] );
	$aq = mysql_query("SELECT `posts`.*, `boards`.`url`, `boards`.`international`, `boards`.`show_empty_names` FROM `posts`, `boards` WHERE `boards`.`id` = `posts`.`board` AND `posts`.`id` = '". $id ."' LIMIT 1");


	if( mysql_num_rows($aq) == 0 )
	{
		// When the thread recycle bin exists, we should change to error 410 if we know that there was a thread.
		//header('HTTP/1.0 410 Gone');
		header('HTTP/1.0 404 Not Found');
		header("Location: ". $cfg['htmldir'] ."/404/");
		die();
	}
		$msg = mysql_fetch_assoc( $aq );
	$msg['on_page'] = 1;
}
else
{

}

$title = $cfg['fp_title']; // Site title
include("inc/header.php"); // Html-header
echo '
		<div id="padded">
		<h4>'. sprintf( T_('Showing a single post from thread %s on board /%s/.'), ( $msg['thread'] == 0 ? $msg['id'] : $msg['thread'] ), $msg['url'] )  .'</h4>';
		
		print_post($msg, $location = 'board', $msg);
		
		echo '
		</div>
	</div>';
include("inc/footer.php"); // Html-footer
?>
