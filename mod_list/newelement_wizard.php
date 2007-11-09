<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Mads Brunn (mads@brunn.dk)
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
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
* 
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * New database item menu
 *
 * This script lets users choose a new database element to create.
 * Includes a wizard mode for visually pointing out the position of new pages
 *
 *
 * @author	Mads Brunn <mads@brunn.dk>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 */



unset($MCONF);
include('conf.php');
require($BACK_PATH.'init.php');
require($BACK_PATH.'template.php');
$LANG->includeLLFile('EXT:lang/locallang_misc.xml');
$LANG->includeLLFile('EXT:categories/mod_list/locallang.xml');
require_once(PATH_t3lib.'class.t3lib_scbase.php');




/**
 * Script class for 'tx_categories_newelement'
 *
 * @author	Mads Brunn <mads@brunn.dk>
 * @package TYPO3
 * @subpackage categories
 */
class tx_categories_newelement extends t3lib_SCbase{
	var $catinfo;
	var $web_list_modTSconfig;
	var $allowedNewTables;
	var $web_list_modTSconfig_pid;
	var $allowedNewTables_pid;	
	var $code;
	var $R_URI;
	var $hookObjectsArr = array();

		// Internal, static: GPvar
	var $id;			// see init()
	var $returnUrl;		// Return url.

		// Internal
	var $perms_clause;	// see init()
	var $doc;			// see init()
	var $content;		// Accumulated HTML output


	/**
	 * Constructor function for the class
	 *
	 * @return	void
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TYPO3_CONF_VARS;

		$this->initHooks();		
		
			// name might be set from outside
		if (!$this->MCONF['name']) {
			$this->MCONF = $GLOBALS['MCONF'];
		}

		// Setting GPvars:
		$this->id = intval(t3lib_div::_GP('id'));	// The category id
		$this->backPath = $BACK_PATH;
		$this->returnUrl = t3lib_div::_GP('returnUrl');
		$this->returnEditConf = t3lib_div::_GP('returnEditConf');
		
		$this->R_URI=$this->returnUrl;
		$this->perms_clause = $GLOBALS['BE_USER']->getPagePermsClause(16);
		$this->menuConfig();
		$this->catinfo = tx_categories_div::getCategoryInfo($this->id);
		
		
		//hook for doing stuff after initialization
		foreach($this->hookObjectsArr as $hookObj)	{
			if (method_exists($hookObj, 'newElementPostInit')) {
				$hookObj->newElementPostInit($this);
			}
		}
		
		$this->handleIncomingParams();
	}

	
	/**
	 * Initializes the internal MOD_MENU array setting and unsetting items based on various conditions. It also merges in external menu items from the global array TBE_MODULES_EXT (see mergeExternalItems())
	 * Then MOD_SETTINGS array is cleaned up (see t3lib_BEfunc::getModuleData()) so it contains only valid values. It's also updated with any SET[] values submitted.
	 * Also loads the modTSconfig internal variable.
	 *
	 * @return	void
	 * @see init(), $MOD_MENU, $MOD_SETTINGS, t3lib_BEfunc::getModuleData(), mergeExternalItems()
	 */
	function menuConfig()	{
		global $TCA;
			// page/be_user TSconfig settings and blinding of menu-items
		$this->modTSconfig = t3lib_BEfunc::getModTSconfig($this->id,'mod.'.$this->MCONF['name']);
		
		if(is_array($TCA)){
			foreach($TCA as $t => $c){
				$this->MOD_MENU[$t.'_create_pid'] = '';
			}
		}
		
		
			// CLEANSE 'function' SETTINGS
		$this->MOD_SETTINGS = t3lib_BEfunc::getModuleData($this->MOD_MENU, t3lib_div::_GP('SET'), $this->MCONF['name'], $this->modMenu_type, $this->modMenu_dontValidateList, $this->modMenu_setDefaultList);
	
	}	
	
	
	
	
	/**
	 * Main processing, creating the list of new record tables to select from
	 *
	 * @return	void
	 */
	function main()	{
		global $BE_USER,$LANG,$TYPO3_CONF_VARS,$BACK_PATH;
		
		//hook for doing stuff before rendering the page
		foreach($this->hookObjectsArr as $hookObj)	{
			if (method_exists($hookObj, 'newElementPreRender')) {
				$hookObj->newElementPreRender($this);
			}
		}		
		
			// Create instance of template class for output
		$this->doc = t3lib_div::makeInstance('mediumDoc');
		$this->doc->backPath = $BACK_PATH;
		$this->doc->styleSheetFile2 = PATH_txcategories_rel.'res/stylesheet.css';
		$this->doc->docType= 'xhtml_trans';
		$this->doc->JScode='';

			// Creating content
		$this->content = $this->doc->startPage($LANG->getLL('newrecordtitle'));
		$this->content .= $this->doc->header($LANG->getLL('newrecordincategory'));
		$this->content .= $this->doc->getHeader('tx_categories',$this->catinfo,$this->catinfo['_thePath'],1).'<br />';
		$this->content .= $this->regularNew();
		
			// Create go-back link.
		if ($this->R_URI)	{
			$this->content .= '<br />
				<a href="'.htmlspecialchars($this->R_URI).'" class="typo3-goBack">'.
				'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/goback.gif','width="14" height="14"').' alt="" />'.
				$LANG->getLL('goBack',1).
				'</a>';
		}
		
		//hook for doing stuff after rendering
		foreach($this->hookObjectsArr as $hookObj)	{
			if (method_exists($hookObj, 'newElementPostRender')) {
				$hookObj->newElementPostRender($this);
			}
		}	
		
	}


	/**
	 * Create a regular new element (pages and records)
	 *
	 * @return	void
	 */
	function regularNew()	{
		global $BE_USER,$LANG,$BACK_PATH,$TCA;
		
		$out = array();

		$doNotShowFullDescr = FALSE;

			// Slight spacer from header:
		$out[] = '<img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/ol/halfline.gif','width="18" height="8"').' alt="" /><br />';

			// Initialize array for accumulating table rows:
		$tRows = array();
			// New tables in this category
		if (is_array($TCA))	{
			foreach($TCA as $t => $v)	{

				if($createOnPage = $this->getCreatePage($t,$v)){
					if (
						$this->isTableAllowedForThisCategory($this->catinfo, $t)
						&& $BE_USER->check('tables_modify',$t)
						&& tx_categories_div::isTableAllowedForCategorization($t)
					){
						
						//hook for doing stuff after rendering
						$_params['ok'] = TRUE;
						foreach($this->hookObjectsArr as $hookObj)	{
							if (method_exists($hookObj, 'newElementAllowTable')) {
								$hookObj->newElementAllowTable($_params,$t,$this->catinfo,$this);
							}
						}
						
						if($_params['ok']){
							
							$tRows[] = $this->createRow($t,$v,$createOnPage);
							
						}
					}
				}
			}
		}


			// Make table:
		$out[] = '
			<table border="0" cellpadding="0" cellspacing="0" id="typo3-newRecord">
				<tr>
					<td><img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/ol/line.gif','width="18" height="16"').' alt="" /></td>
					<td><strong>'.$LANG->getLL('createin').'</strong></td>
					<td> </td>
				</tr>
			'.implode('',$tRows).'
			</table>
		';
		
		return implode("\n",$out);

	}

	/**
	 * Ending page output and echo'ing content to browser.
	 *
	 * @return	void                             
	 */
	function printContent()	{

		$this->content.= $this->doc->endPage();
		$this->content = $this->doc->insertStylesAndJS($this->content);
		echo $this->content;
		
	}
	
	
	function createRow($table,$conf,$createPageRow){
		
		global $BACK_PATH,$LANG;
		
		$allowchangepid = FALSE;
		
		$out = array();
		$out[] = '<tr>';
		$out[] = '<td>';
		$out[] = '<img'.t3lib_iconWorks::skinImg($BACK_PATH,'gfx/ol/join.gif','width="18" height="16"').' alt="" />';
		
		$lnktxt = t3lib_iconWorks::getIconImage($table,array(),$BACK_PATH,'');
		$lnktxt .= $LANG->sL($conf['ctrl']['title'],1);
		$out[] = $this->linkWrap($lnktxt,$table,$createPageRow['uid']);
		$out[] = '</td>';
		$out[] = '<td>';
		
		
		if($createPageRow['uid'] == 0){	//if rootlevel
			$icon = '<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/i/_icon_website.gif','width="18" height="16"').' alt="" />';	
		} else {
			$icon = t3lib_iconWorks::getIconImage('pages',$createPageRow,$BACK_PATH,'align="top" class="c-recIcon" title="UID: '.$row['uid'].'"');
		}
		$txt = t3lib_BEfunc::getRecordTitle('pages',$createPageRow);

		if($createPageRow['uid']!=0  && $table!='tx_categories'){	//if we are allowed to change the pid
			$txt .= '<em>('.$LANG->getLL('change').')</em>';
			$allowchangepid = TRUE;
		}
		
		$item = $icon.$txt;
		
		if($allowchangepid){	//link to positionmap if pid is changeable
			$item = $this->linkToPositionmap($item,$table,$conf);
		}

		$out[] = $item;
		
		$out[] = '</td>';
		$out[] = '<td>'.t3lib_BEfunc::cshItem($table,'',$BACK_PATH,'',$doNotShowFullDescr).'</td>';
		$out[] = '</tr>';
		
		return implode("",$out);
		
	}
	
	
	/**
	 * Return a page row where a record from table $table can be created or false if no valid page could be found
	 *
	 * @param	string	$table: The table in which we want to create a record 
	 * @param	array	$conf: TCA-configuration for tabel $table
	 * @return	mixed	array if a valid page was found, otherwise FALSE
	 */
	
	function getCreatePage($table,$conf){

		//if table is the category table
		if($table == 'tx_categories') {
			return t3lib_BEfunc::getRecord('pages',tx_categories_div::getPid());
		}
			
		//If table can only exist at rootlevel
		if($conf['ctrl']['rootLevel']) return array('title' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'],'uid'=>0);
		
		//if a pid has been cached
		$cachedCreateRecordPid = $this->MOD_SETTINGS[$table.'_create_pid'];
		if(
			t3lib_div::testInt($cachedCreateRecordPid) &&
			intval($cachedCreateRecordPid) > 0
		){
			//check if the user still has access to the record
			if($GLOBALS['BE_USER']->isInWebMount(intval($cachedCreateRecordPid))){
				
					if($pageRow = t3lib_BEfunc::getRecord('pages',$cachedCreateRecordPid,'*',' AND (doktype NOT IN(199,255)) AND pages.uid NOT IN('.tx_categories_div::getPid().')')){
					if(tx_categories_div::isTableAllowedForThisPage($pageRow,$table)){
						return $pageRow;
					}
				}
				
			}
		}
		
		
		//okay, let's try to find a page where we can insert the record..
		
		//this query may result in zero rows (e.g. if no page records have been created yet). What to do then?
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','pages','1=1'.t3lib_BEfunc::deleteClause('pages').' AND '.$this->perms_clause.' AND (pages.doktype NOT IN(199,255)) AND pages.uid NOT IN('.tx_categories_div::getPid().')');
		while($pageRow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){

			if(
				$GLOBALS['BE_USER']->isInWebMount($pageRow['uid']) 
				&& tx_categories_div::isTableAllowedForThisPage($pageRow,$table)
			){
				
				return $pageRow;
				
			}
			
		}
		
		return false;

	}
	
	

	function linkToPositionmap($item,$table,$conf){
		global $LANG;
		return '<a href="'.htmlspecialchars($this->doc->backPath . PATH_txcategories_rel . 'mod_list/newelement_positionmap.php?id='.$this->id.'&table='.$table.'&returnUrl='.rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI'))).'">'.$item.'</a>';
	}
	

	/**
	 * Links the string $code to a create-new form for a record in $table created on page $pid
	 *
	 * @param	string		Link string
	 * @param	string		Table name (in which to create new record)
	 * @param	integer		PID value for the "&edit['.$table.']['.$pid.']=new" command (positive/negative)
	 * @param	boolean		If $addContentTable is set, then a new contentTable record is created together with pages
	 * @return	string		The link.
	 */
	function linkWrap($code,$table,$pid,$addContentTable=0)	{
		
		global $BACK_PATH,$TYPO3_CONF_VARS;

		$cField = '*';	
		
		$params = '&edit['.$table.']['.$pid.']=new'.
			($table=='pages'
				&& $GLOBALS['TYPO3_CONF_VARS']['SYS']['contentTable']
				&& isset($GLOBALS['TCA'][$GLOBALS['TYPO3_CONF_VARS']['SYS']['contentTable']])
				&& $addContentTable	?
				'&edit['.$GLOBALS['TYPO3_CONF_VARS']['SYS']['contentTable'].'][prev]=new&returnNewPageId=1'	:
				''
			).'&defVals=&defVals['.$table.']['.$cField.']='.$this->id.'&returnEditConf=1';
		$onClick = t3lib_BEfunc::editOnClick($params,$BACK_PATH,t3lib_div::linkThisScript());
		return '<a href="#" onclick="'.htmlspecialchars($onClick).'">'.$code.'</a>';
	}

	function isTableAllowedForThisCategory($catrow,$table){

		
		/*
		 * TODO:
		 * Implement some kind of checking if it allowable
		 * to categorize records from this table with this
		 * category
		 */
		 
		 return TRUE;
		
	}
	
	
	function handleIncomingParams(){
		
		if($this->returnEditConf && $this->returnUrl){ //we are on return from typo3/alt_doc.php
			//debug($this->returnUrl);
			header('Location:'.t3lib_div::locationHeaderUrl($this->returnUrl));
			exit;
			
		}

	}
	
	
	function initHooks(){

		if(is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['categories']['ext/categories/mod_list/newelement_wizard.php'])){

			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['categories']['ext/categories/mod_list/newelement_wizard.php'] as $classRef){
			
				$this->hookObjectsArr[] = &t3lib_div::getUserObj($classRef);

			}
		}

	}

}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/mod_list/newelement_wizard.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/mod_list/newelement_wizard.php']);
}





// Make instance:
$SOBE = t3lib_div::makeInstance('tx_categories_newelement');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();
?>