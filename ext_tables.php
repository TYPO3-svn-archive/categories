<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');



$temp_categoriesfolder = 0;


if (TYPO3_MODE=='BE') {
	$temp_categoriesfolder = tx_categories_div::getPid();
	
	if ($TYPO3_CONF_VARS['EXTCONF']['categories']['setup']['hide_category_sysfolder']) {
		t3lib_extMgm::addUserTSConfig('
			options.hideRecords.pages = '.$temp_categoriesfolder.'
		');
	}

	//placing the categories module right after the web module 
	if (!isset($TBE_MODULES['txcategoriesMain']))	{
		$temp_TBE_MODULES = array();
		foreach($TBE_MODULES as $key => $val) {
			if ($key=='web') {
				$temp_TBE_MODULES[$key] = $val;
				$temp_TBE_MODULES['txcategoriesMain'] = $val;
			} else {
				$temp_TBE_MODULES[$key] = $val;
			}
		}
		$TBE_MODULES = $temp_TBE_MODULES;
	}        

	//main module
	t3lib_extMgm::addModule('txcategoriesMain','','',PATH_txcategories.'mod_main/');
	//list module
	t3lib_extMgm::addModule('txcategoriesMain','txcategoriesList','',PATH_txcategories.'mod_list/');
	//info module
	t3lib_extMgm::addModule('txcategoriesMain','txcategoriesInfo','',PATH_txcategories.'mod_info/');	
	//functions module
	t3lib_extMgm::addModule('txcategoriesMain','txcategoriesFunc','',PATH_txcategories.'mod_func/');		

	
	$GLOBALS["TBE_MODULES_EXT"]["xMOD_alt_clickmenu"]["extendCMclasses"][]=array(
		"name" => "tx_categories_cm",
		"path" => PATH_txcategories."lib/class.tx_categories_cm.php"
	);
	
	//t3lib_extMgm::insertModuleFunction(
	//	'txcategoriesMain_txcategoriesFunc',
	//	'tx_categories_modfuncimport',
	//	PATH_txcategories.'mod_func_import/class.tx_categories_modfuncimport.php',
	//	'Import',
	//	'function'
	//);	

	//t3lib_extMgm::insertModuleFunction(
	//	'txcategoriesMain_txcategoriesImport',
	//	'tx_categories_importmodfunc1',
	//	t3lib_extMgm::extPath($_EXTKEY).'mod_import/importmodfunc1/class.tx_categories_importmodfunc1.php',
	//	'LLL:EXT:categories/mod_import/importmodfunc1/locallang.xml:title',
	//	'submodule'
	//);	

	//t3lib_extMgm::insertModuleFunction(
	//	'txcategoriesMain_txcategoriesImport',		
	//	'tx_categories_importmodfunc2',
	//	t3lib_extMgm::extPath($_EXTKEY).'mod_import/importmodfunc2/class.tx_categories_importmodfunc2.php',
	//	'LLL:EXT:categories/mod_import/importmodfunc2/locallang.xml:title',
	//	'submodule'
	//);
	
	t3lib_extMgm::addLLrefForTCAdescr('_MOD_txcategoriesMain_txcategoriesList','EXT:categories/locallang_csh_modlist.xml');
	t3lib_extMgm::addLLrefForTCAdescr('_MOD_txcategoriesMain_txcategoriesInfo','EXT:categories/locallang_csh_modinfo.xml');
	t3lib_extMgm::addLLrefForTCAdescr('_MOD_txcategoriesMain_txcategoriesFunc','EXT:categories/locallang_csh_modfunc.xml');
	t3lib_extMgm::addLLrefForTCAdescr('_MOD_txcategoriesMain_txcategoriesImport','EXT:categories/locallang_csh_modimport.xml');
	t3lib_extMgm::addLLrefForTCAdescr('tx_categories','EXT:categories/locallang_csh_tx_categories.xml');
	
}


$TCA['tx_categories'] = array (
	'ctrl' => array (
		'title'     => 'LLL:EXT:categories/locallang_db.xml:tx_categories',		
		'label'     => 'title',	
		'thumbnail' => 'media',
		'default_sortby' => 'ORDER BY title',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'editlock' => 'editlock',
		'prependAtCopy' => 'LLL:EXT:lang/locallang_general.php:LGL.prependAtCopy',
		'cruser_id' => 'cruser_id',
		'languageField' => 'sys_language_uid',    
		'transOrigPointerField' => 'l18n_parent',    
		'transOrigDiffSourceField' => 'l18n_diffsource',    		
		'default_sortby' => 'ORDER BY crdate',	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => PATH_txcategories.'tca.php',
		'iconfile' => PATH_txcategories_rel.'gfx/category_icon.gif',
		'canNotCollapse' => 1,
		//'mainpalette' => 1,
		'dividers2tabs' => 0,
	),
	'feInterface' => array (
		'fe_admin_fieldList' => 'title, description, synonyms',
	)
);




$tempColumns = Array (
	'tx_categories_mountpoints' => Array (		
		'exclude' => 1,		
		'label' => 'LLL:EXT:categories/locallang_db.xml:be_groups.tx_categories_mountpoints',		
		'config' => Array (
			'type' => 'select',	
			'foreign_table' => 'tx_categories',	
			'size' => 5,	
			'minitems' => 0,
			'maxitems' => 100,	
			'form_type' => 'user',
			'userFunc' => 'EXT:categories/lib/class.tx_categories_tce.php:tx_categories_tce->getSingleField_typeSelectCategoryTree',
			'treeName' => 'begroupstxcategories',
			'enableWarning' => 1,
			'autoSizeMax' => 20,	
			'selectedListStyle' => 'width:200px;',	
			'itemListStyle' => 'width:200px;height:250px;',
			'orderByFields' => 'title',						
			'wizards' => Array(
				'_PADDING' => 2,
				'_VERTICAL' => 1,
				'_POSITION' => 'left',
				'_VALIGN' => 'top',			
				'add' => Array(
					'type' => 'script',
					'title' => 'Create new record',
					'icon' => 'add.gif',
					'params' => Array(
						'table'=>'tx_categories',
						'pid' => $temp_categoriesfolder,
						'setValue' => 'prepend'
					),
					'script' => 'wizard_add.php',
				),
				'edit' => Array(
					'type' => 'popup',
					'title' => 'Edit',
					'script' => 'wizard_edit.php',
					'popup_onlyOpenIfSelected' => 1,
					'icon' => 'edit2.gif',
					'JSopenParams' => 'height=350,width=580,status=0,menubar=0,scrollbars=1',
				),
			),
		)
	),
);


t3lib_div::loadTCA('be_groups');
t3lib_extMgm::addTCAcolumns('be_groups',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('be_groups','tx_categories_mountpoints','','after:file_mountpoints');


$tempColumns['tx_categories_mountpoints']['label'] = 'LLL:EXT:categories/locallang_db.xml:be_users.tx_categories_mountpoints';


t3lib_div::loadTCA('be_users');
t3lib_extMgm::addTCAcolumns('be_users',$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes('be_users','tx_categories_mountpoints','','after:file_mountpoints');


$TCA['be_users']['columns']['options']['config']['items'][] = array(
	'Category Mounts',
	0
);
$TCA['be_users']['columns']['options']['config']['default'] = 7;

//Examples on how to exclude tables from categorization
//$GLOBALS['TCA']['sys_note']['ctrl']['EXT']['categories']['exclude'] = 1;
//$GLOBALS['TCA']['sys_template']['ctrl']['EXT']['categories']['exclude'] = 1;
//$GLOBALS['TCA']['tt_news']['ctrl']['EXT']['categories']['exclude'] = 1;
//$GLOBALS['TCA']['tt_news_cat']['ctrl']['EXT']['categories']['exclude'] = 1;

?>
