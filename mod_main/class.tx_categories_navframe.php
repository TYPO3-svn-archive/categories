<?php


require_once(PATH_txcategories.'lib/class.tx_categories_navtree.php');

class tx_categories_navframe{
	
	var $doHighlight = 1;
	var $ajax = 0;
	
	function init(){
		global $BE_USER,$LANG,$BACK_PATH,$TYPO3_CONF_VARS,$CLIENT,$TCA;
		
		
		if(!is_object($LANG)){
			
			require_once(PATH_typo3.'sysext/lang/lang.php');
			$GLOBALS['LANG'] = t3lib_div::makeInstance('language');
			$GLOBALS['LANG']->init($BE_USER->uc['lang']);
		
		}
		
		$this->backPath = $BACK_PATH;
		
		//Compatibility with version 4.1. As of TYPO3 version 4.2 a common ajax interface is used
		if(!t3lib_div::compat_version('4.2')){
			$this->ajax = t3lib_div::_GP('ajax');
		}
		
		$this->cMR = t3lib_div::_GP('cMR');
		$this->currentSubScript = t3lib_div::_GP('currentSubScript');		
		$GLOBALS['TBE_TEMPLATE']->backPath = $BACK_PATH;	

		t3lib_div::loadTCA($TYPO3_CONF_VARS['EXTCONF']['categories']['table']);
		
		//only display records from the default language in the tree
		$clause = ' AND (
				'.$TCA[$TYPO3_CONF_VARS['EXTCONF']['categories']['table']]['ctrl']['languageField'].'<=0
				OR 
				'.$TCA[$TYPO3_CONF_VARS['EXTCONF']['categories']['table']]['ctrl']['transOrigPointerField'].' = 0
			)';
		
		$this->treeObject = t3lib_div::makeInstance('tx_categories_navtree');
		$this->treeObject->table = $TYPO3_CONF_VARS['EXTCONF']['categories']['table'];
		$this->treeObject->mm = $TYPO3_CONF_VARS['EXTCONF']['categories']['MM'];

		$this->treeObject->MOUNTS = tx_categories_befunc::getCategoryMounts();
		$this->treeObject->init($clause);
		$this->treeObject->backPath = $this->backPath;
		$this->treeObject->expandAll = 0;
		$this->treeObject->expandFirst = 0;
		$this->treeObject->fieldArray = array('uid','title','hidden','php_tree_stop'); // those fields will be filled to the array $treeObject->tree

		//TODO: make this configurable with User TS config
		$this->treeObject->ext_IconMode = 1; 

		$this->treeObject->ext_showCategoryId = $BE_USER->getTSConfigVal('options.categoryTree.showCategoryIdWithTitle');
		
		$this->treeObject->title = $GLOBALS['LANG']->sL($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['categories']['rootname']);	
		
		
		$this->treeObject->thisScript = 'navframe.php';

		
		$this->treeObject->treeName = 'txcategoriesnavtree';
		$this->treeObject->showDefaultTitleAttribute = TRUE;
		$this->treeObject->ajaxCall = $this->ajax;
			
	}
	
	
	function initPage(){
		global $BE_USER,$LANG,$BACK_PATH,$TYPO3_CONF_VARS,$CLIENT,$TCA;
		
		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->docType='xhtml_trans';		
		$this->doc->backPath = $BACK_PATH;
		
		$CMparts=$this->doc->getContextMenuCode();
		$this->doc->bodyTagAdditions = $CMparts[1];
		
		
		$ajaxScript = $this->backPath.'ajax.php';
		
		//Compatibility with version 4.1. As of TYPO3 version 4.2 prototype.js is automatically included in BE-scripts
		if(!t3lib_div::compat_version('4.2')){
			$this->doc->JScode .= '<script type="text/javascript" src="'.$this->backPath.'contrib/prototype/prototype.js"></script>';
			$ajaxScript = 'navframe.php';
		} 
			
		$this->doc->JScode .= '<script type="text/javascript" src="'.$this->backPath.(t3lib_div::compat_version('4.2')?'js/':'').'tree.js"></script>';
							
		$hlClass = 'active';
		
		$this->doc->JScode .= $this->doc->wrapScriptTags(
			($this->currentSubScript?'top.currentSubScript=unescape("'.rawurlencode($this->currentSubScript).'");':'').'
			// setting prefs for pagetree and drag & drop
			Tree.thisScript    = "'.$ajaxScript.'";
			'.($this->doHighlight ? 'Tree.highlightClass = "'.$hlClass.'";' : '').'
			//4.2 ajax interface
			Tree.ajaxID = "tx_categories_navframe::expandCollapse";

			DragDrop.changeURL = "'.$this->backPath.'alt_clickmenu.php";
			DragDrop.backPath  = "'.t3lib_div::shortMD5(''.'|'.$GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']).'";
			DragDrop.table     = "'.$TYPO3_CONF_VARS['EXTCONF']['categories']['table'].'";

			// Function, loading the list frame from navigation tree:
			function jumpTo(id, linkObj, highlightID, bank, path)	{ //
				var theUrl = top.TS.PATH_typo3 + top.currentSubScript + "?id=" + id + "&path=" + path;
				top.fsMod.currentBank = bank;

				if (top.condensedMode) top.content.location.href = theUrl;
				else                   parent.list_frame.location.href=theUrl;

				'.($this->doHighlight ? '
				//alert("highlightID="+highlightID+"\nbank="+bank+"\npath="+path);
				//alert("id="+highlightID + "_" + bank + "_" + path);
				Tree.highlightActiveItem("txcategoriesMain", highlightID + "_" + bank + "_" + path);' : '').'
				'.(!$GLOBALS['CLIENT']['FORMSTYLE'] ? '' : 'if (linkObj) linkObj.blur(); ').'
				return false;
			}
			'.($this->cMR?"jumpTo(top.fsMod.recentIds['txcategoriesMain'],'');":'').'
			');	
		
		$this->doc->JScode .= $CMparts[0];
		
		$this->doc->JScode .= $this->doc->wrapScriptTags('
		
			navFrameId = "txcategoriesMain";
		
		');
		
		
		$this->doc->postCode .= $CMparts[2];
		
		$this->doc->postCode .= t3lib_BEfunc::getSetUpdateSignal(); 
		
		
	}
	
	
	function main(){
		
		global $BE_USER,$LANG,$BACK_PATH,$TYPO3_CONF_VARS,$CLIENT,$TCA;
		
		
			// Produce browse-tree:
		$tree = $this->treeObject->getBrowsableTree();

			//if ajax, we return immediately after rendering the tree
		if ($this->ajax) {
			$this->content = $LANG->csConvObj->utf8_encode($tree, $LANG->charSet);
			return;
		}
			
			// Start page:
		$this->content = $this->doc->startPage('TYPO3 Category Tree');			
		$this->content .= $tree;
			
				// Outputting refresh-link
		$refreshUrl = t3lib_div::getIndpEnv('REQUEST_URI');
		
		
		//$this->content .= t3lib_div::view_array(get_defined_constants());
		
		$this->content.= '
			<p class="c-refresh">
				<a href="'.htmlspecialchars($refreshUrl).'">'.
				'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/refresh_n.gif','width="14" height="14"').' title="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.refresh',1).'" alt="" />'.
				'</a><a href="'.htmlspecialchars($refreshUrl).'">'.
				$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.refresh',1).'</a>
			</p>
			<br />';
			

			// Adding javascript for drag & drop activation and highlighting
		$this->content .=$this->doc->wrapScriptTags('
			'.($this->doHighlight ? 'Tree.highlightActiveItem("",top.fsMod.navFrameHighlightedID["txcategoriesMain"]);' : '').'
			'.(!$this->doc->isCMlayers() ? 'Tree.activateDragDrop = false;' : 'Tree.registerDragDropHandlers();')
		);			
				
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
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/mod_main/class.tx_categories_navframe.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/mod_main/class.tx_categories_navframe.php']);
}









?>