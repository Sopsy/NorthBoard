<?php
// Northpole.fi
// Moderator logout
// 17.4.2012

require_once("../inc/include.php");

setcookie("mod", "", 1, '/');
header("Location: ". $cfg['htmldir'] ."/mod/index/");
?>
