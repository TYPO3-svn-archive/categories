<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007  Mads Brunn <mads@brunn.dk>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/


    // DEFAULT initialization of a module [BEGIN]
unset($MCONF);
require_once('conf.php');
require_once($BACK_PATH.'init.php');
require_once($BACK_PATH.'template.php');
require_once(PATH_txcategories.'lib/class.tx_categories_recordlist.php');
require_once(PATH_txcategories.'lib/class.tx_categories_clipboard.php');
$LANG->includeLLFile('EXT:categories/mod_list/locallang.xml');
require_once(PATH_t3lib.'class.t3lib_scbase.php');
$BE_USER->modAccess($MCONF,1);    // This checks permissions and exits if the users has no permission for entry.
    // DEFAULT initialization of a module [END]



/**
 * Module 'List' for the 'categories' extension.
 *
 * @author     Mads Brunn<mads@typoconsult.dk>
 * @package    TYPO3
 * @subpackage    tx_categories
 */
class  tx_categories_list extends t3lib_SCbase {

	var $id;				// Page Id for which to make the listing
	var $pointer;				// Pointer - for browsing list of records.
	var $imagemode;				// Thumbnails or not
	var $table;					// Which table to make extended listing for
	var $search_field;			// Search-fields
	var $search_levels;			// Search-levels
	var $showLimit;				// Show-limit
	var $returnUrl;				// Return URL	 
	 
	 
	 
	 /**
	 * Initializes the Module
	 * @return    void
	 */
	 function init()    {
		 global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;


			// Setting module configuration / page select clause
		$this->MCONF = $GLOBALS['MCONF'];

			// GPvars:
		$this->id = t3lib_div::_GP('id');
		$this->path = t3lib_div::_GP('path');
		$this->pointer = t3lib_div::_GP('pointer');
		$this->imagemode = t3lib_div::_GP('imagemode');
		$this->table = t3lib_div::_GP('table');
		$this->search_field = t3lib_div::_GP('search_field');
		$this->search_levels = t3lib_div::_GP('search_levels');
		$this->showLimit = t3lib_div::_GP('showLimit');
		$this->returnUrl = t3lib_div::_GP('returnUrl');

		$this->clear_cache = t3lib_div::_GP('clear_cache');
		$this->cmd = t3lib_div::_GP('cmd');
		$this->cmd_table = t3lib_div::_GP('cmd_table');
		 
		 parent::init();
		 
		 /*
		 if (t3lib_div::_GP('clear_all_cache'))    {
			 $this->include_once[] = PATH_t3lib.'class.t3lib_tcemain.php';
		 }
		 */
	 }
	 
	 /**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return    void
	 */
	 function menuConfig()    {
		 global $LANG;
		 $this->MOD_MENU = Array (
			 'recursive' => Array (
				 '0' => '',
				 '1' => $LANG->sL('LLL:EXT:cms/locallang_ttc.php:recursive.I.1'),
				 '2' => $LANG->sL('LLL:EXT:cms/locallang_ttc.php:recursive.I.2'),
				 '3' => $LANG->sL('LLL:EXT:cms/locallang_ttc.php:recursive.I.3'),
				 '4' => $LANG->sL('LLL:EXT:cms/locallang_ttc.php:recursive.I.4'),
				 '250' => $LANG->sL('LLL:EXT:cms/locallang_ttc.php:recursive.I.5'),				 
				),
			'bigControlPanel' => '',
			'clipBoard' => '',
		);
		
			// Loading module configuration:
		$this->modTSconfig = t3lib_BEfunc::getModTSconfig($this->id,'mod.'.$this->MCONF['name']);

		
			// Clean up settings:
		$this->MOD_SETTINGS = t3lib_BEfunc::getModuleData($this->MOD_MENU, t3lib_div::_GP('SET'), $this->MCONF['name']);
	
			
	 }
	 
	 
	 
	 
	 
	 
	 
	 /**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return    [type]        ...
	 */
	 function main()    {
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

		//debug($_REQUEST);
		
		$this->catinfo = tx_categories_div::getCategoryInfo($this->id,$this->path);

		// Draw the header.
		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->docType='xhtml_trans';
		$this->doc->backPath = $BACK_PATH;
		
			// Initialize the dblist object:
		$dblist = t3lib_div::makeInstance('tx_categories_recordlist');
		$dblist->backPath = $BACK_PATH;
		$dblist->thumbs = $BE_USER->uc['thumbnailsByDefault'];
		$dblist->returnUrl=$this->returnUrl;
		$dblist->parentTable = $TYPO3_CONF_VARS['EXTCONF']['categories']['table'];
		$dblist->mm = $TYPO3_CONF_VARS['EXTCONF']['categories']['MM']; 
		$dblist->script = 'index.php';
		$dblist->allFields = ($this->MOD_SETTINGS['bigControlPanel'] || $this->table) ? 1 : 0;
		
		// no localization view (yet)
		//$dblist->localizationView = $this->MOD_SETTINGS['localization'];
		$dblist->showClipboard = 1;
		$dblist->disableSingleTableView = $this->modTSconfig['properties']['disableSingleTableView'];
		$dblist->listOnlyInSingleTableMode = $this->modTSconfig['properties']['listOnlyInSingleTableView'];
		$dblist->hideTables = $this->modTSconfig['properties']['hideTables'];
		$dblist->clickTitleMode = $this->modTSconfig['properties']['clickTitleMode'];
		$dblist->alternateBgColors=$this->modTSconfig['properties']['alternateBgColors']?1:0;
		$dblist->allowedNewTables = t3lib_div::trimExplode(',',$this->modTSconfig['properties']['allowedNewTables'],1);
		$dblist->newWizards=$this->modTSconfig['properties']['newWizards']?1:0;


			// Clipboard is initialized:
		$dblist->clipObj = t3lib_div::makeInstance('tx_categories_clipboard');		// Start clipboard
		$dblist->clipObj->initializeClipboard();	// Initialize - reads the clipboard content from the user session
		$dblist->clipObj->backPath = $dblist->backPath;
			// Clipboard actions are handled:
		$CB = t3lib_div::_GET('CB');	// CB is the clipboard command array
		if ($this->cmd=='setCB') {
				// CBH is all the fields selected for the clipboard, CBC is the checkbox fields which were checked. By merging we get a full array of checked/unchecked elements
				// This is set to the 'el' array of the CB after being parsed so only the table in question is registered.
			$CB['el'] = $dblist->clipObj->cleanUpCBC(array_merge((array)t3lib_div::_POST('CBH'),(array)t3lib_div::_POST('CBC')),$this->cmd_table);
		}
		if (!$this->MOD_SETTINGS['clipBoard'])	$CB['setP']='normal';	// If the clipboard is NOT shown, set the pad to 'normal'.
		$dblist->clipObj->setCmd($CB);		// Execute commands.
		$dblist->clipObj->cleanCurrent();	// Clean up pad
		$dblist->clipObj->endClipboard();	// Save the clipboard content

			// This flag will prevent the clipboard panel in being shown.
			// It is set, if the clickmenu-layer is active AND the extended view is not enabled.
		$dblist->dontShowClipControlPanels = $CLIENT['FORMSTYLE'] && !$this->MOD_SETTINGS['bigControlPanel'] && $dblist->clipObj->current=='normal' && !$BE_USER->uc['disableCMlayers'] && !$this->modTSconfig['properties']['showClipControlPanelsDespiteOfCMlayers'];

		
		//echo t3lib_div::view_array($dblist->clipObj->clipData);
		
			// Deleting records...:
			// Has not to do with the clipboard but is simply the delete action. The clipboard object is used to clean up the submitted entries to only the selected table.
		if ($this->cmd=='delete')	{
			$items = $dblist->clipObj->cleanUpCBC(t3lib_div::_POST('CBC'),$this->cmd_table,1);
			if (count($items))	{
				$cmd=array();
				reset($items);
				while(list($iK)=each($items))	{
					$iKParts = explode('|',$iK);
					$cmd[$iKParts[0]][$iKParts[1]]['delete']=1;
				}
				$tce = t3lib_div::makeInstance('t3lib_TCEmain');
				$tce->stripslashes_values=0;
				$tce->start(array(),$cmd);
				$tce->process_cmdmap();

				if (isset($cmd['pages']))	{
					t3lib_BEfunc::getSetUpdateSignal('updatePageTree');
				}
				
				$tce->printLogErrorMessages(t3lib_div::getIndpEnv('REQUEST_URI'));
				
			}
		}

			// Initialize the listing object, dblist, for rendering the list:
		$this->pointer = t3lib_div::intInRange($this->pointer,0,100000);
		$dblist->start($this->id,$this->table,$this->pointer,$this->search_field,$this->search_levels,$this->showLimit,$this->path);
		$dblist->setDispFields();

			// Render the page header:
		$dblist->writeTop($this->catinfo);

			// Render the list of tables:
		$dblist->generateList();

			// Write the bottom of the page:
		$dblist->writeBottom();

			// Add JavaScript functions to the page:
		$this->doc->JScode=$this->doc->wrapScriptTags('
			function jumpToUrl(URL)	{	//
				window.location.href = URL;
				return false;
			}
			function jumpExt(URL,anchor)	{	//
				var anc = anchor?anchor:"";
				window.location.href = URL+(T3_THIS_LOCATION?"&returnUrl="+T3_THIS_LOCATION:"")+anc;
				return false;
			}
			function jumpSelf(URL)	{	//
				window.location.href = URL+(T3_RETURN_URL?"&returnUrl="+T3_RETURN_URL:"");
				return false;
			}

			function setHighlight(id)	{	//
				top.fsMod.recentIds["txcategoriesMain"]=id;
				top.fsMod.navFrameHighlightedID["txcategoriesMain"]="row"+id+"_"+top.fsMod.currentBank;	// For highlighting

				if (top.content && top.content.nav_frame && top.content.nav_frame.refresh_nav)	{
					top.content.nav_frame.refresh_nav();
				}
			}
			'.$this->doc->redirectUrls(PATH_txcategories_rel.'mod_list/'.$dblist->listURL()).'
			'.$dblist->CBfunctions().'
			function editRecords(table,idList,addParams,CBflag)	{	//
				window.location.href="'.$BACK_PATH.'alt_doc.php?returnUrl='.rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI')).
					'&edit["+table+"]["+idList+"]=edit"+addParams;
			}
			function editList(table,idList)	{	//
				var list="";

					// Checking how many is checked, how many is not
				var pointer=0;
				var pos = idList.indexOf(",");
				while (pos!=-1)	{
					if (cbValue(table+"|"+idList.substr(pointer,pos-pointer))) {
						list+=idList.substr(pointer,pos-pointer)+",";
					}
					pointer=pos+1;
					pos = idList.indexOf(",",pointer);
				}
				if (cbValue(table+"|"+idList.substr(pointer))) {
					list+=idList.substr(pointer)+",";
				}

				return list ? list : idList;
			}

			if (top.fsMod) top.fsMod.recentIds["txcategoriesMain"] = '.intval($this->id).';
		');

			// Setting up the context sensitive menu:
		$CMparts=$this->doc->getContextMenuCode();
		$this->doc->bodyTagAdditions = $CMparts[1];
		$this->doc->JScode.=$CMparts[0];
		$this->doc->postCode.= $CMparts[2];
		
		$this->content.=$this->doc->startPage($LANG->getLL('title'));
		
		
		
		$this->content.= '<form action="'.htmlspecialchars($dblist->listURL()).'" method="post" name="dblistForm">';
		
		$this->content.= $dblist->HTMLcode;
		$this->content.= '<input type="hidden" name="cmd_table" /><input type="hidden" name="cmd" /></form>';


			// If a listing was produced, create the page footer with search form etc:
		if ($dblist->HTMLcode)	{

				// Making field select box (when extended view for a single table is enabled):
			if ($dblist->table)	{
				$this->content.=$dblist->fieldSelectBox($dblist->table);
			}

				// Adding checkbox options for extended listing and clipboard display:
			$this->content.='

					<!--
						Listing options for clipboard and thumbnails
					-->
					<div id="typo3-listOptions">
						<form action="" method="post">';

			$this->content.=t3lib_BEfunc::getFuncCheck($this->id,'SET[bigControlPanel]',$this->MOD_SETTINGS['bigControlPanel'],'index.php',($this->table?'&table='.$this->table:'')).' '.$LANG->getLL('largeControl',1).'<br />';
			if ($dblist->showClipboard)	{
				$this->content.=t3lib_BEfunc::getFuncCheck($this->id,'SET[clipBoard]',$this->MOD_SETTINGS['clipBoard'],'index.php',($this->table?'&table='.$this->table:'')).' '.$LANG->getLL('showClipBoard',1).'<br />';
			}
			
			/*
			 *	no localization view in category list module (let's not overcomplicate things)			
			 */
			
			//$this->content.=t3lib_BEfunc::getFuncCheck($this->id,'SET[localization]',$this->MOD_SETTINGS['localization'],'index.php',($this->table?'&table='.$this->table:'')).' '.$LANG->getLL('localization',1).'<br />';
			
			
			$this->content.='
						</form>
					</div>';

					
				// Printing clipboard if enabled:
			if ($this->MOD_SETTINGS['clipBoard'] && $dblist->showClipboard)	{
				$this->content.= $dblist->clipObj->printClipboard();
			}

				// Link for creating new records:
			if (!$this->modTSconfig['properties']['noCreateRecordsLink'] && $this->id > 0) 	{
			 	$this->content.='
			 		<!--
			 			Link for creating a new record:
			 		-->
			 		<div id="typo3-newRecordLink">
			 		<a href="'.htmlspecialchars($this->doc->backPath . PATH_txcategories_rel . 'mod_list/newelement_wizard.php?id='.$this->id.'&returnUrl='.rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI'))).'">'.
			 					'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/new_el.gif','width="11" height="12"').' alt="" />'.
			 					$LANG->getLL('newRecordGeneral',1).
			 					'</a>
			 		</div>';
			}

				// Search box:
			$this->content.=$dblist->getSearchBox();

				// ShortCut:
			if ($BE_USER->mayMakeShortcut())	{
				$this->content.='<br/>'.$this->doc->makeShortcutIcon('id,imagemode,pointer,table,search_field,search_levels,showLimit,sortField,sortRev',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']);
			}
		}
		
		
		
		// ShortCut
		//if ($BE_USER->mayMakeShortcut())    {
		//	$this->content.=$this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
		//}
		
		$this->content.=$this->doc->spacer(10);
	 }
	 
	 /**
	 * Prints out the module HTML
	 *
	 * @return    void
	 */
	function printContent()    {		 
		$this->content.=$this->doc->endPage();
		echo $this->content;
	}
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/mod_list/index.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/mod_list/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_categories_list');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE) include_once($INC_FILE);
$SOBE->main();
$SOBE->printContent();

?>