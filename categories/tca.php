<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');




$TCA['tx_categories'] = array (
	'ctrl' => $TCA['tx_categories']['ctrl'],
	'interface' => array (
		'showRecordFieldList' => 'title,description,synonyms'
	),
	'feInterface' => $TCA['tx_categories']['feInterface'],
	'columns' => array (
		'sys_language_uid' => array (        
			'exclude' => 1,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l18n_parent' => array (        
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 1,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_categories',
				'foreign_table_where' => 'AND tx_categories.pid=###CURRENT_PID### AND tx_categories.sys_language_uid IN (-1,0)',
			)
			),
		'l18n_diffsource' => array (        
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'editlock' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_tca.php:editlock',
			'config' => array(
				'type' => 'check',
			),
		),
		'hidden' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'title' => Array (		
			'exclude' => 0,		
			'label' => 'LLL:EXT:categories/locallang_db.xml:tx_categories.title',		
			'config' => Array (
				'type' => 'input',	
				'size' => '48',	
				'eval' => 'required',
			)
		),
		'alias' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:categories/locallang_db.xml:tx_categories.alias',		
			'config' => Array (
				'type' => 'input',	
				'size' => '10',	
				'eval' => 'nospace,alphanum_x,lower,unique',
				'max' => '32'
			)
		),	
		'description' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:categories/locallang_db.xml:tx_categories.description',		
			'config' => Array (
				'type' => 'text',
				'cols' => '48',
				'rows' => '5',
			)
		),
	
		'media' => Array (        
			'exclude' => 1,        
			'label' => 'LLL:EXT:categories/locallang_db.xml:tx_categories.media',        
			'config' => Array (
				'type' => 'group',
				'internal_type' => 'file',
				'allowed' => '',    
				'disallowed' => 'php,php3',
				'max_size' => 1000,    
				'uploadfolder' => 'uploads/tx_categories',
				'show_thumbs' => 1,    
				'size' => 3,    
				'minitems' => 0,
				'maxitems' => 100,
			)
		),	
		
		'synonyms' => Array (		
			'exclude' => 1,		
			'label' => 'LLL:EXT:categories/locallang_db.xml:tx_categories.synonyms',		
			'config' => Array (
				'type' => 'text',
				'cols' => '48',
				'rows' => '3',
			)
		),	
		
		'*' => Array (		
			'exclude' => 0,	

			//The category field should not be shown for localization of the record
			//this makes sence because you usually don't want to categorize a localization differently
			'l10n_mode' => 'exclude',	

			//but we still want the field to be displayed (TODO: implement readonly display for category field)
			//'l10n_display' => 'defaultAsReadonly',
			
			
			'label' => 'LLL:EXT:categories/locallang_db.xml:tx_categories.parents',		
			'config' => Array (
	
				'type' => 'select',
				'foreign_table' => 'tx_categories',
				'MM' => 'tx_categories_mm',	
				'MM_match_fields' => array(
					'localtable' => 'tx_categories'
				),	
				'size' => 3,	
				'minitems' => 0,
				'maxitems' => 100,
				'form_type' => 'user',
				'userFunc' => 'EXT:categories/lib/class.tx_categories_tce.php:tx_categories_tce->getSingleField_typeSelectCategoryTree',
				'treeName' => 'categoriestxcategories',
				'selectedListStyle' => 'width:200px;',
				'itemListStyle' => 'width:200px;height:150px;',				
				'autoSizeMax' => 20,	
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
							'pid' => tx_categories_div::getPid(),
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
		
		'related' => Array (		
			'exclude' => 1,	
			
			//The related categories field should not be shown for localization of the record
			//this makes sence because you usually don't want to have a different set of related items for localizations
			'l10n_mode' => 'exclude',	
			
			//but we still want the field to be displayed (TODO: implement readonly display for category fields
			//'l10n_display' => 'defaultAsReadonly',
			
			
			'label' => 'LLL:EXT:categories/locallang_db.xml:tx_categories.related',		
			'config' => Array (
				'type' => 'select',
				'foreign_table' => 'tx_categories',
				'MM' => 'tx_categories_related_category_mm',
				'size' => 3,	
				'minitems' => 0,
				'maxitems' => 100,
				'form_type' => 'user',
				'userFunc' => 'EXT:categories/lib/class.tx_categories_tce.php:tx_categories_tce->getSingleField_typeSelectCategoryTree',
				'treeName' => 'relatedcategoriestxcategories',
				'autoSizeMax' => 20,	
				'orderByFields' => 'title',	
				'selectedListStyle' => 'width:200px;',
				'itemListStyle' => 'width:200px;height:150px;',				
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
							'pid' => tx_categories_div::getPid(),
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
			
		'php_tree_stop' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:categories/locallang_db.xml:tx_categories.php_tree_stop',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
	),
	'types' => array (
		'0' => array('showitem' => 'title;;alias;;2-2-2,--palette--;;language;;,description;;advanced;;,synonyms;;;;3-3-3,media,--div--;LLL:EXT:categories/locallang_db.xml:tab_relations;;;,*,related')
	),
	'palettes' => array (
		'alias' => array('showitem' => 'hidden,alias'),
		'language' => array('showitem' => 'sys_language_uid,l18n_parent,l18n_diffsource'),
		'advanced' => array('showitem' => 'editlock,php_tree_stop')		
	)
);



//configuring dummy text wizards
if(t3lib_extMgm::isLoaded('lorem_ipsum')){
		// Create wizard configuration:
	$wizConfig = array(
		'type' => 'userFunc',
		'userFunc' => 'EXT:lorem_ipsum/class.tx_loremipsum_wiz.php:tx_loremipsum_wiz->main',
		'params' => array()
	);

	$TCA['tx_categories']['columns']['title']['config']['wizards']['tx_loremipsum'] = array_merge($wizConfig,array('params'=>array('type' => 'title')));
	$TCA['tx_categories']['columns']['description']['config']['wizards']['tx_loremipsum'] = array_merge($wizConfig,array('params'=>array('type' => 'paragraph','endSequence' => '10','add' => TRUE)));
}	












?>