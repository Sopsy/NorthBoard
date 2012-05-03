<?php

$redirs = array();
$redirs[] = 'http://track.adform.net/adfscript/?bn=611558;click=http://ads.guava-affiliate.com/z/30919/CD2102/?redirect=';
$redirs[] = 'http://ads.guava-affiliate.com/z/30919/CD2102/?redirect=http://track.adform.net/C/?bn=611558;C=0';
$redirs[] = 'http://track.adform.net/adfserve/?bn=611558;srctype=4;ord=[timestamp]';

if(!empty($_GET['redir']) AND array_key_exists($_GET['redir'], $redirs))
header("Location: ". $redirs[$_GET['redir']]);

?>