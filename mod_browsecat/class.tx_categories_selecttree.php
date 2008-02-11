<?php

require_once(PATH_txcategories.'lib/class.tx_categories_treeview.php');

class tx_categories_selecttree{
	
	var $ajax = 0;
	
	
	function init(){
		global $BE_USER,$LANG,$BACK_PATH,$TYPO3_CONF_VARS,$CLIENT,$TYPO3_DB,$TCA;

		if(!is_object($LANG)){
			require_once(PATH_typo3.'sysext/lang/lang.php');
			$GLOBALS['LANG'] = t3lib_div::makeInstance('language');
			$GLOBALS['LANG']->init($BE_USER->uc['lang']);
		}
		
		//retriving some GET-params
		$this->itemFormElName = t3lib_div::_GP('itemFormElName');
		$this->treeName = t3lib_div::_GP('treeName');
		$this->rootIds = t3lib_div::_GP('rootIds');
		
		
		$this->backPath = $BACK_PATH;
		
		
		//Compatibility with version 4.1. As of TYPO3 version 4.2 a common ajax interface is used
		if(!t3lib_div::compat_version('4.2')){
			$this->ajax = t3lib_div::_GP('ajax');
		}
		
		$GLOBALS['TBE_TEMPLATE']->backPath = $BACK_PATH;

		t3lib_div::loadTCA($TYPO3_CONF_VARS['EXTCONF']['categories']['table']);
		
		//only display records from the default language in the tree
		$clause = ' AND (
				'.$TCA[$TYPO3_CONF_VARS['EXTCONF']['categories']['table']]['ctrl']['languageField'].'<=0
				OR 
				'.$TCA[$TYPO3_CONF_VARS['EXTCONF']['categories']['table']]['ctrl']['transOrigPointerField'].' = 0
			)';		
		
		//initializing tree class
		$this->treeObject = t3lib_div::makeInstance('tx_categories_treeview');
		
		$this->treeObject->table = $TYPO3_CONF_VARS['EXTCONF']['categories']['table'];
		$this->treeObject->mm = $TYPO3_CONF_VARS['EXTCONF']['categories']['MM'];
		$this->treeObject->rootIds = $this->rootIds;
		$this->treeObject->TCEforms_itemFormElName = $this->itemFormElName;
		$this->treeObject->treeName = $this->treeName;		
		$this->treeObject->thisScript = 'index.php';

		$this->treeObject->MOUNTS = tx_categories_befunc::getCategoryMounts();		
		$this->treeObject->init($clause);
		$this->treeObject->backPath = $BACK_PATH;
		$this->treeObject->expandAll = 0;
		$this->treeObject->expandFirst = 0;
		$this->treeObject->fieldArray = array('uid','title','hidden'); // those fields will be filled to the array $treeObject->tree
		$this->treeObject->ext_IconMode = 0; 
		$this->treeObject->title = $LANG->sL($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['categories']['rootname']);

		//computing additional params for ajax
		$params = array();
		$params['itemFormElName'] = $this->itemFormElName; 
		$params['treeName'] = $this->treeName;
		
		
		//TODO: figure out if the rootid's are within the current BE_USERs mountpoint
		if(trim($this->rootIds)){
			$params['rootIds'] = $this->rootIds;
		}
		$getparams = t3lib_div::implodeArrayForUrl('',$params,'',1);
		$this->treeObject->additionalParams = $getparams;
		
		$this->treeObject->ajaxCall = $this->ajax;		
		
	}
	
	
	
	function initPage(){
		global $BE_USER,$LANG,$BACK_PATH,$TYPO3_CONF_VARS,$CLIENT,$TYPO3_DB,$TCA;
		
		//initializing the doc object
		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->docType='xhtml_trans';
		$this->doc->backPath = $BACK_PATH;		

		$this->doc->styleSheetFile2 = PATH_txcategories_rel.'res/stylesheet.css';
		$this->doc->JScode .= '<script type="text/javascript" src="'.$this->doc->backPath.'contrib/prototype/prototype.js"></script>';
		$this->doc->JScode .= '<script type="text/javascript" src="'.$this->doc->backPath.(t3lib_div::compat_version('4.2')?'js/':'').'tree.js"></script>';
		
		$ajaxScript = $this->backPath.'ajax.php';

		if(!t3lib_div::compat_version('4.2')){
			$ajaxScript = 'index.php';
		} 
		
		$this->doc->JScode .= $this->doc->wrapScriptTags('
			// setting prefs for pagetree and drag & drop
			Tree.thisScript    = "'.$ajaxScript.'";
			//4.2 ajax interface
			Tree.ajaxID = "tx_categories_selecttree::expandCollapse";			
		');			
	}
	
	
	
	function main(){
		global $BE_USER,$LANG,$BACK_PATH,$TYPO3_CONF_VARS,$CLIENT,$TCA;
			// Produce browse-tree:
		$tree = $this->treeObject->getBrowsableTree();

		if ($this->ajax) {
			$this->content = $LANG->csConvObj->utf8_encode($tree, $LANG->charSet);
			return;
		}
			// Start page:
		$this->content = $this->doc->startPage('TYPO3 Category Tree');			
		$this->content .= $tree;
		$this->content.= $this->doc->endPage();	
	}
	
	
	
	function printContent(){
		if ($this->ajax) {
			header('X-JSON: ('.($this->treeObject->ajaxStatus?'true':'false').')');
			header('Content-type: text/html; charset=utf-8');
		}
		echo $this->content;
	}


	/**
	 * AJAX-call in TYPO3 4.2
	 * Makes the AJAX call to expand or collapse the category tree.
	 * Called by typo3/ajax.php
	 * 
	 * @param	array		$params: additional parameters (not used here)
	 * @param	TYPO3AJAX	&$ajaxObj: reference of the TYPO3AJAX object of this request
	 * @return	void
	 */
	function ajaxExpandCollapse($params, &$ajaxObj) {
		global $LANG;

		$this->init();
		$this->ajax = 1;
		$this->treeObject->ajaxCall = 1;
		
		//hardcoded backpath
		$this->treeObject->backPath =  '../../../../typo3/';
		$tree = $this->treeObject->getBrowsableTree();
		if (!$this->treeObject->ajaxStatus) {
			$ajaxObj->setError($tree);
		} else	{
			$ajaxObj->addContent('tree', $tree);
		}
	}		
}


// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/mod_browsecat/class.tx_categories_selecttree.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/mod_browsecat/class.tx_categories_selecttree.php']);
}



?>