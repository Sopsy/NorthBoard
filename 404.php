<?php
require_once("inc/include.php");
header('HTTP/1.0 404 Not Found');

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"> 
<head>
	<title><?php echo T_("404 Not Found"); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	
	<link rel="stylesheet" type="text/css" href="<?php echo $cfg['htmldir']; ?>/css/<?php echo 'css.php?name='. $cfg['user']['site_style']; ?>" title="<?php echo $cfg['user']['site_style']; ?>" />

	<link rel="shortcut icon" href="<?php echo $cfg['htmldir']; ?>/favicon.ico" />
</head>
<body id="error_404">
	<h1><?php echo T_("HTTP-404"); ?></h1>
	<h3><em><?php echo T_("The requested page does not exist."); ?></em></h3>
	<p><?php echo T_("Why? How would I know?"); ?></p>
	<p><?php echo sprintf(T_("Return to %sfront page%s or %swhere you came from%s."), '<a href="'. $cfg['htmldir'] .'/">', '</a>', '<a href="javascript: history.go(-1)">', '</a>'); ?></p>
	<p><img src="<?php echo $cfg['htmldir']; ?>/css/img/not_found.jpg" alt="<?php echo T_("The page really cannot be found, damn!"); ?>" /></p>
</body>
</html>
