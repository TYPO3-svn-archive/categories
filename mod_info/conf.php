<?php

    // DO NOT REMOVE OR CHANGE THESE 3 LINES:
define('TYPO3_MOD_PATH', '../typo3conf/ext/categories/mod_info/');
$BACK_PATH='../../../../typo3/';
$MCONF['name']='txcategoriesMain_txcategoriesInfo';

$MCONF['navFrameScript'] = '../mod_main/class.tx_categories_navframe.php';

$MCONF['access']='user,group';

$MCONF['script']='index.php';

$MLANG['default']['tabs_images']['tab'] = '../gfx/info.gif';
$MLANG['default']['ll_ref']='LLL:EXT:categories/mod_info/locallang_mod.xml';
?>