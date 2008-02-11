<?php
 

$_fieldname = tx_categories_div::getCategoryFieldName($table);

$tempColumns = Array (
	 $_fieldname => Array (		
		'exclude' => 0,	
		
		//The category field should not be shown for localization of the record
		//this makes sence because you usually don't want to categorize a localization different
		
		'l10n_mode' => 'exclude',	

		//but we still want the field to be displayed (TODO: readonly display must be implemented)
		//'l10n_display' => 'defaultAsReadonly',
		
		
		'label' => 'LLL:EXT:categories/locallang_db.xml:general.tx_categories',		
		'config' => Array (
			'type' => 'select',	
			'foreign_table' => 'tx_categories',	
			'foreign_table_where' => ' ORDER BY tx_categories.title',
			'size' => 10,	
			'minitems' => 0,
			'maxitems' => 100,	
			'MM' => 'tx_categories_mm',	
			'MM_match_fields' => array(
				'localtable' => $table
			),	
			'form_type' => 'user',

			//This function displays the normal hierarchial view of the categories
			'userFunc' => 'EXT:categories/lib/class.tx_categories_tce.php:tx_categories_tce->getSingleField_typeSelectCategoryTree',


			//This function displays a searchbox which autosuggest functionality
			//'userFunc' => 'EXT:categories/lib/class.tx_categories_tce.php:tx_categories_tce->getSingleField_typeFreeVocabulary',

			
			'treeView' => 1,
			'treeName' => str_replace('_','',$table).'txcategories',
			'enableWarning' => $TYPO3_CONF_VARS['EXTCONF']['categories']['setup']['enable_warnings'],
			'warnings' => array(
				'noCategoriesSelected' => 'LLL:EXT:categories/locallang_db.xml:pages.warning_on_no_categories_selected'	
			),
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
);


t3lib_div::loadTCA($table);
t3lib_extMgm::addTCAcolumns($table,$tempColumns,1);
t3lib_extMgm::addToAllTCAtypes($table,'*;;;;1-1-1');



?>