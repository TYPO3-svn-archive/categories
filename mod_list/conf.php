<?php

    // DO NOT REMOVE OR CHANGE THESE 3 LINES:
define('TYPO3_MOD_PATH', '../typo3conf/ext/categories/mod_list/');
$BACK_PATH='../../../../typo3/';
$MCONF['name']='txcategoriesMain_txcategoriesList';

$MCONF['navFrameScript'] = '../mod_main/class.tx_categories_navframe.php';


$MCONF['access']='user,group';
$MCONF['script']='index.php';

$MLANG['default']['tabs_images']['tab'] = '../gfx/list.gif';
$MLANG['default']['ll_ref']='LLL:EXT:categories/mod_list/locallang_mod.xml';
?>