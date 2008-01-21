<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');


	//Defining constants. This will save some time and repetition
if (!defined('PATH_txcategories')) {    
	define('PATH_txcategories', t3lib_extMgm::extPath('categories'));
}

if (!defined('PATH_txcategories_rel')) {    
	define('PATH_txcategories_rel', t3lib_extMgm::extRelPath('categories'));
}

if(!defined('PATH_txcategories_siterel')){
	define('PATH_txcategories_siterel',t3lib_extMgm::siteRelPath('categories'));	
}





	//Including class with miscellaneous functions for categories
	
require_once(PATH_txcategories.'lib/class.tx_categories_div.php');


	//Including BE-functions 
if (TYPO3_MODE=='BE') {
	require_once(PATH_txcategories.'lib/class.tx_categories_befunc.php');
}




	//Extracting the configuration from the EM
$TYPO3_CONF_VARS['EXTCONF']['categories']['setup'] = unserialize($_EXTCONF);


	//Adding a "save and new" button in alt_doc.php for tx_categories
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_categories=1
');




//	Registering hooks


	//tcemain
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processDatamapClass'][] = 'EXT:categories/hooks/class.tx_categories_tcemain.php:tx_categories_tcemain';
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['processCmdmapClass'][] = 'EXT:categories/hooks/class.tx_categories_tcemain.php:tx_categories_tcemain';

	//additional tcemain hook functions - not used
//$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['moveRecordClass'][] = 'EXT:categories/hooks/class.tx_categories_tcemain.php:tx_categories_tcemain';
//$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearPageCacheEval'][] = 'EXT:categories/hooks/class.tx_categories_tcemain.php:tx_categories_tcemain->clearPageCacheEval';
//$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tcemain.php']['clearCachePostProc'][] = 'EXT:categories/hooks/class.tx_categories_tcemain.php:tx_categories_tcemain->clearCachePostProc';

	//tceforms
$TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tceforms.php']['getMainFieldsClass'][] = 'EXT:categories/hooks/class.tx_categories_tceform.php:tx_categories_tceform';

	//tslib_fe
//$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['determineId-PostProc'][] = 'EXT:categories/hooks/class.tx_categories_tslibfe.php:tx_categories_tslibfe->determineId_PostProc';

	//browser
//$TYPO3_CONF_VARS['SC_OPTIONS']['typo3/browse_links.php']['browserRendering'][] = 'EXT:categories/lib/class.tx_categories_browser.php:tx_categories_browser';


	//templavoila
$TYPO3_CONF_VARS['EXTCONF']['templavoila']['mod1']['renderTopToolbar'][] = 'EXT:categories/hooks/class.tx_categories_templavoilamod1.php:tx_categories_templavoilamod1->renderTopToolbar';



$TYPO3_CONF_VARS['EXTCONF']['kickstarter']['add_cat_moduleFunction'][] = 'EXT:categories/hooks/class.tx_categories_kickstarter.php:tx_categories_kickstarter->addModuleFunction';


	//Name to be displayed in the root of the category tree
$TYPO3_CONF_VARS['EXTCONF']['categories']['rootname'] = 'LLL:EXT:categories/locallang.xml:rootname';


	//own hook in "new element" wizard (Categories>List)
$TYPO3_CONF_VARS['EXTCONF']['categories']['ext/categories/mod_list/newelement_wizard.php']['pages'] = 'EXT:categories/hooks/class.tx_categories_modnewelement.php:tx_categories_modnewelement';



	//The table used as category table
	//Notice: don't change this unless you know what you are doing
	//this table must contain the following fields: title
$TYPO3_CONF_VARS['EXTCONF']['categories']['table'] = 'tx_categories';
//$TYPO3_CONF_VARS['EXTCONF']['categories']['fields'] =  array('uid','title','hidden','php_tree_stop');

//The table used as mm table
//Notice: don't change this unless you know what you are doing
//this table must contain the following fields: uid_local, uid_foreign, sorting, localtable
$TYPO3_CONF_VARS['EXTCONF']['categories']['MM'] = 'tx_categories_mm';



//TYPO3 version 4.2 stuff
$TYPO3_CONF_VARS['BE']['AJAX']['tx_categories_navframe::expandCollapse'] = 'EXT:categories/mod_main/class.tx_categories_navframe.php:tx_categories_navframe->ajaxExpandCollapse';
$TYPO3_CONF_VARS['BE']['AJAX']['tx_categories_selecttree::expandCollapse'] = 'EXT:categories/mod_browsecat/class.tx_categories_selecttree.php:tx_categories_selecttree->ajaxExpandCollapse';
$TYPO3_CONF_VARS['BE']['AJAX']['tx_categories_modulepositionmap::expandCollapse'] = 'EXT:categories/mod_list/class.tx_categories_modulepositionmap.php:tx_categories_modulepositionmap->ajaxExpandCollapse';






?>