<?php

unset($MCONF);
include ('conf.php');
include ($BACK_PATH.'init.php');
include ($BACK_PATH.'template.php');

require_once (PATH_txcategories.'mod_main/class.tx_categories_navframe.php');

$SOBE = t3lib_div::makeInstance('tx_categories_navframe');
$SOBE->init();
$SOBE->initPage();
$SOBE->main();
$SOBE->printContent();


?>
