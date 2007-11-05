<?php

unset($MCONF);
require('conf.php');
include ($BACK_PATH.'init.php');
include ($BACK_PATH.'template.php');
include ('class.tx_categories_ajaxtce.php');


	// Make instance:
$SOBE = t3lib_div::makeInstance('tx_categories_ajaxtce');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();




?>