<?php

unset($MCONF);
include ('conf.php');
include ($BACK_PATH.'init.php');
include ($BACK_PATH.'template.php');


require_once(PATH_txcategories.'lib/class.tx_categories_navtree.php');


class tx_categories_navframe{
	
	var $doHighlight = 1;
	
	function init(){
		global $BE_USER,$LANG,$BACK_PATH,$TYPO3_CONF_VARS,$CLIENT,$TCA;
		
		$this->backPath = $BACK_PATH;
		
		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->docType='xhtml_trans';		
		
		$this->doc->backPath = $BACK_PATH;
		$this->ajax = t3lib_div::_GP('ajax');
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
		$this->treeObject->backPath = $this->doc->backPath;
		$this->treeObject->expandAll = 0;
		$this->treeObject->expandFirst = 0;
		$this->treeObject->fieldArray = array('uid','title','hidden','php_tree_stop'); // those fields will be filled to the array $treeObject->tree

		//TODO: make this configurable with User TS config
		$this->treeObject->ext_IconMode = 1; 

		$this->treeObject->ext_showCategoryId = $BE_USER->getTSConfigVal('options.categoryTree.showCategoryIdWithTitle');
		
		$this->treeObject->title = $LANG->sL($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['categories']['rootname']);	
		$this->treeObject->thisScript = 'class.tx_categories_navframe.php';
		$this->treeObject->treeName = 'txcategoriesnavtree';
		$this->treeObject->showDefaultTitleAttribute = TRUE;
	
		if(!$this->ajax){
			$CMparts=$this->doc->getContextMenuCode();
			$this->doc->bodyTagAdditions = $CMparts[1];
			
			//As of TYPO3 version 4.2 prototype.js is automatically included in BE-scripts
			if(!t3lib_div::compat_version('4.2')){
				$this->doc->JScode .= '<script type="text/javascript" src="'.$this->backPath.'contrib/prototype/prototype.js"></script>';
			}
			
			$this->doc->JScode .= '<script type="text/javascript" src="'.$this->backPath.'tree.js"></script>';
								
			$hlClass = 'active';
			
			$this->doc->JScode .= $this->doc->wrapScriptTags(
				($this->currentSubScript?'top.currentSubScript=unescape("'.rawurlencode($this->currentSubScript).'");':'').'
				// setting prefs for pagetree and drag & drop
				Tree.thisScript    = "class.tx_categories_navframe.php";
				'.($this->doHighlight ? 'Tree.highlightClass = "'.$hlClass.'";' : '').'
	
				DragDrop.changeURL = "'.$this->backPath.'alt_clickmenu.php";
				DragDrop.backPath  = "'.t3lib_div::shortMD5(''.'|'.$GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']).'";
				DragDrop.table     = "'.$TYPO3_CONF_VARS['EXTCONF']['categories']['table'].'";
	
				// Function, loading the list frame from navigation tree:
				function jumpTo(id, linkObj, highlightID, bank, path)	{ //
					var theUrl = top.TS.PATH_typo3 + top.currentSubScript + "?id=" + id;
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
			
/*
			$this->doc->inDocStylesArray['tx_categories_navframe'] = '
			
				UL.tree { list-style: none; margin: 0 0 10px 0; padding: 0; }
				UL.tree A		{ text-decoration: none; }
				UL.tree A.pm	{ cursor: pointer; }
				UL.tree IMG		{ vertical-align: middle; }
				UL.tree UL		{ list-style: none; margin: 0; padding: 0; padding-left: 17px; }
				UL.tree UL LI	{ list-style: none; margin: 0; padding: 0; line-height: 10px; white-space: nowrap; }
				UL.tree UL LI.expanded UL	{ background: transparent url(\'../../../../typo3/gfx/ol/line.gif\') repeat-y top left; }
				UL.tree UL LI.last		{ background: transparent url(\'../../../../typo3/gfx/ol/joinbottom.gif\') no-repeat top left; }
				UL.tree UL LI.last > UL	{ background: none; }
				UL.tree UL LI.active	{ background-color: #ebebeb !important; }
				UL.tree UL LI.active UL	{ background-color: #f7f3ef; }
				#dragIcon { z-index: 1; position: absolute; visibility: hidden; filter: alpha(opacity=50); -moz-opacity:0.5; opacity:0.5; white-space: nowrap; }			
			
			';
*/

		}			
			
	}
	
	
	
	function main(){
		
		global $LANG;
		
		
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
	
}




// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/mod_main/class.tx_categories_navframe.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/mod_main/class.tx_categories_navframe.php']);
}

	// Make instance:
$SOBE = t3lib_div::makeInstance('tx_categories_navframe');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();









?>