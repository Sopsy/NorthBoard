<?php
// Northpole.fi
// 12.4.2011
require_once("../../inc/include.php");

if(empty($_GET['ajax']))
	$ajax = false;
else
	$ajax = $_GET['ajax'];
	
if($ajax) {

	if(empty($_GET['data']) AND !is_numeric($_GET['data']))
		die(T_("No data to save!"));

	if($ajax == "sidebar") {
		if($_GET['data'] != "0" AND $_GET['data'] != "1")
			die(T_("The selected value does not exist!"));
		$set_col = 'show_sidebar';
	}
	elseif($ajax == "postform") {
		if($_GET['data'] != "0" AND $_GET['data'] != "1")
			die(T_("The selected value does not exist!"));
		$set_col = 'show_postform';
	}
	elseif($ajax == "browserwarning") {
		if($_GET['data'] != "0" AND $_GET['data'] != "1")
			die(T_("The selected value does not exist!"));
		$set_col = 'hide_browserwarning';
	}
	else {
		die(T_("No data to save!"));
	}
	
	$q = update_user($cfg['user']['uid'], $set_col, $_GET['data']);
	if($q)
		echo "OK";
	else
		die(T_("Error in SQL query!"));
		

}
?>
