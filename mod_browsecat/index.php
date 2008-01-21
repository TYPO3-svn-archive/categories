<?php

unset($MCONF);
require('conf.php');
include ($BACK_PATH.'init.php');
include ($BACK_PATH.'template.php');

require_once (PATH_txcategories.'mod_browsecat/class.tx_categories_selecttree.php');
	// Make instance:
$SOBE = t3lib_div::makeInstance('tx_categories_selecttree');
$SOBE->init();
$SOBE->initPage();
$SOBE->main();
$SOBE->printContent();



?>