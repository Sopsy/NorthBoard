<?php
$umin = true;
include_once("inc/include.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en"> 
<head>
	<title><?php echo T_("403 Forbidden"); ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
</head>
<body style="text-align: center;">
	<h1><?php echo T_("HTTP-403"); ?></h1>
	<h3><em><?php echo T_("You do not have the permission to open the requested page."); ?></em></h3>
	<p><?php echo sprintf(T_("If you clicked a link at %s, please inform the board administrator at %s."), $cfg['htmldir'], $cfg['admin_email']); ?></p>
	<p><?php echo sprintf(T_("Return to %sfront page%s or %swhere you came from%s."), '<a href="'. $cfg['htmldir'] .'/">', '</a>', '<a href="javascript: history.go(-1)">', '</a>'); ?></p>
</body>
</html>
