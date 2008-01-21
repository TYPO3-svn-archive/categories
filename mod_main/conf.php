<?php

    // DO NOT REMOVE OR CHANGE THESE 3 LINES:
define('TYPO3_MOD_PATH', '../typo3conf/ext/categories/mod_main/');
$BACK_PATH='../../../../typo3/';
$MCONF['name']='txcategoriesMain';
$MCONF['defaultMod']='txcategoriesMain_txcategoriesList';
$MCONF['navFrameScript'] = 'navframe.php';    
$MCONF['access']='user,group';
$MCONF['script']='index.php';

//only display this module in the online workspace
$MCONF['workspaces']='online,custom';


$MLANG['default']['tabs_images']['tab'] = '../gfx/main.gif';
$MLANG['default']['ll_ref']='LLL:EXT:categories/mod_main/locallang_mod.xml';
?>