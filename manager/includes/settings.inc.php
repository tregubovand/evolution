<?php

// get the settings from the database.
$settings = array();
if ($modx && count($modx->config)>0) $settings = $modx->config;
else{
	$sql = "SELECT setting_name, setting_value FROM $dbase.".$table_prefix."system_settings";
	$rs = mysql_query($sql);
	$number_of_settings = mysql_num_rows($rs);
	while ($row = mysql_fetch_assoc($rs)) {
		$settings[$row['setting_name']] = $row['setting_value'];
	}
}

extract($settings, EXTR_OVERWRITE);

// setup default site id - new installation should generate a unique id for the site.
if(!isset($site_id)) $site_id = "MzGeQ2faT4Dw06+U49x3";


?>