<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007  <>
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




/**
 * Addition of items to the clickmenu
 *
 * @author     <mads@brunn.dk>
 * @package    TYPO3
 * @subpackage    tx_categories
 */
 
require_once(PATH_txcategories.'lib/class.tx_categories_clipboard.php');
require_once(PATH_txcategories.'lib/class.tx_categories_befunc.php');
 
 
class tx_categories_cm {
	
	var $backRef;
	var $row;
	var $cField;
	var $ctable;


	
	/**
	 * [description]
	 * @param	[type]		[parameter description]
	 * @return	[type]		[output description]
	 */
	function main(&$backRef,$menuItems,$table,$uid)    {
		
		
		global $BE_USER,$TCA,$LANG,$TYPO3_CONF_VARS;


		$this->ctable = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['categories']['table'];

		
		$this->category = t3lib_div::_GP('category');
		
		$this->cField = tx_categories_div::getCategoryFieldName($table);
		
		if($this->category){	//if it's a real category
			$this->catrow = t3lib_BEfunc::getRecord($this->ctable,$this->category);
		}
		
		//mail('mads@noscan.typoconsult.dk','test',print_r($_REQUEST,TRUE));
		if(!$backRef->cmLevel){	//only level 0 in the context menu
			
			$this->backRef = &$backRef;
			$this->LOCAL_LANG = $this->includeLL(); 
			$this->initClipBoard();
	
	
			//for any table which can be categorized, add item "Create reference"
			if($uid > 0 && tx_categories_div::isTableAllowedForCategorization($table)){
				$menuItems['createreference'] = $this->DB_createReference($table,$uid);
			}
			
			
			if($table == 'pages' && $this->category > -1){
				//change link to web_layout / templavoila
				$menuItems['edit'] = $this->DB_editpage($table,$uid);
			
				//disable this in Categories>List
				unset($menuItems['pasteafter']);
				unset($menuItems['pasteinto']);
			}
			
			//if($table == 'tt_content' && $this->category > -1){
			//	$row = $this->getRow($table,$uid);
			//	$menuItems['view'] = $this->DB_view($row['pid'],$row['uid']);
			//}
			
			if($this->category > 0){ //if it's a real category
				$menuItems['deletereference'] = $this->DB_deleteReference($table,$uid);
			}
	
			
			if($table != $this->ctable && $this->category){
				$menuItems['new'] =  $this->DB_new($table,$uid);
			}
			
			if($table == $this->ctable){
				
				if($GLOBALS['BE_USER']->isAdmin()){
					$menuItems['import'] = $this->DB_import($table,$uid);
				}

				if($this->category > -1){ 
					unset($menuItems['history']);
					unset($menuItems['pasteafter']);
					unset($menuItems['spacer1']);
				}
				
				if($this->category > 0){
					$menuItems['new'] =  $this->DB_newrecordincategory($table,$uid);
				}
				
				//if user is allowed to create categories
				if($GLOBALS['BE_USER']->check('tables_modify',$table)){
					$menuItems['newsubcategory'] = $this->DB_newSubcategory($table,$uid);
				}
				
				
				$elements = $this->clipboardObj->elFromCategorizedTables();
				if(
					count($elements) &&
					$this->clipboardObj->clipData[$this->clipboardObj->current]['refMode'] == 'ref' &&
					$uid > 0
				){
					$menuItems['pastereference'] = $this->DB_pasteReference($table,$uid);
				}
				

			}
		}
		return $menuItems;
	}
	
	function initClipBoard(){

		$this->clipboardObj = t3lib_div::makeInstance('tx_categories_clipboard');
		$this->clipboardObj->backPath = $GLOBALS['BACK_PATH'];
		$this->clipboardObj->initializeClipboard();
		//$this->t3libClipboardObj->lockToNormal();

		$CB = t3lib_div::_GET('CB');
		$this->clipboardObj->setCmd($CB);	

		if (isset ($CB['setRefMode'])) {
			switch ($CB['setRefMode']) {
				case 'copy' : $this->clipboardObj->clipData['normal']['refMode'] = 'copy'; break;
				case 'cut':  $this->clipboardObj->clipData['normal']['refMode'] = 'cut'; break;
				case 'ref': $this->clipboardObj->clipData['normal']['refMode'] = 'ref'; break;
				default: unset ($this->clipboardObj->clipData['normal']['refMode']); break;
			}
		}				
		$this->clipboardObj->cleanCurrent();	// Clean up pad
		$this->clipboardObj->endClipboard();	// Save the clipboard content		
	}
	
	
	function DB_newSubcategory($table,$uid){
		global $LANG,$LOCAL_LANG;

		$editOnClick='';
		$loc='top.content'.(!$this->backRef->alwaysContentFrame?'.list_frame':'');
		
		$editOnClick='if('.$loc.'){'.$loc.".location.href=top.TS.PATH_typo3+'".
			($this->backRef->listFrame?
				"alt_doc.php?returnUrl='+top.rawurlencode(".$this->backRef->frameLocation($loc.'.document').")+'&edit[".$table."][".tx_categories_div::getPid()."]=new&defVals[tx_categories][".$this->cField."]=".$uid."'":
				'db_new.php?id='.intval($uid)."'").
			';}';

		return $this->backRef->linkItem(
						$LANG->getLLL('newsubcategory',$this->LOCAL_LANG),
						$this->backRef->excludeIcon('<img'.t3lib_iconWorks::skinImg($this->backRef->PH_backPath.PATH_txcategories_rel,'gfx/category_icon.gif','width="14" height="12"').' alt="" />'),
						$editOnClick.'return hideCM();'
					);
	}
	
	
	function DB_deleteReference($table,$uid){
		global $LANG;
		
		$title_prep = htmlspecialchars(t3lib_div::fixed_lgd_cs($this->catrow['title'],$GLOBALS['BE_USER']->uc['titleLen']));
		$label = sprintf($LANG->getLLL('deletereference',$this->LOCAL_LANG),$title_prep);
		$confirmlabel = sprintf($LANG->getLLL('confirmdeletefromcategory',$this->LOCAL_LANG),htmlspecialchars($this->catrow['title']));
		
		$editOnClick = '';
		$loc = 'top.content'.($this->backRef->listFrame && !$this->backRef->alwaysContentFrame ?'.list_frame':'');
		if($GLOBALS['BE_USER']->jsConfirmation(2))	{
			$conf = $loc.' && confirm('.$GLOBALS['LANG']->JScharCode($confirmlabel).')';
		} else {
			$conf = $loc;
		}
		$editOnClick = 'if('.$conf.'){'.$loc.'.location.href=top.TS.PATH_typo3+\''.$this->deleteUrl($table,$uid,0).'&redirect=\'+top.rawurlencode('.$this->backRef->frameLocation($loc.'.document').'); hideCM();}';

		return $this->backRef->linkItem(
						$label,
						$this->backRef->excludeIcon('<img'.t3lib_iconWorks::skinImg($this->backRef->PH_backPath.PATH_txcategories_rel,'gfx/delref.gif','width="12" height="12"').' alt="" />'),
						$editOnClick
					);
		
	}
	
	
	
	function deleteUrl($table,$uid,$setRedirect=1){
		$rU = $this->backRef->PH_backPath.PATH_txcategories_rel.'tce_categories.php?'.
			($setRedirect ? 'redirect='.rawurlencode(t3lib_div::linkThisScript(array('CB'=>''))) : '').
			'&vC='.$GLOBALS['BE_USER']->veriCode().
			'&prErr=1&uPT=1'.
			'&data['.$table.']['.$uid.'][*]=-'.$this->category;
		return $rU;		
	}	
	
	
	function pasteUrl($table,$uid,$setRedirect=1){

		$rU = $this->backRef->PH_backPath.PATH_txcategories_rel.'tce_categories.php?'.
			($setRedirect ? 'redirect='.rawurlencode(t3lib_div::linkThisScript(array('CB'=>''))) : '').
			'&vC='.$GLOBALS['BE_USER']->veriCode().
			'&prErr=1&uPT=1'.
			'&CB[paste]='.rawurlencode($table.'|'.$uid).
			'&CB[pad]='.$this->clipboardObj->current;
			
		return $rU;		
	}
	
	
	
	
	
	/**
	 * Adding CM element for Clipboard "Insert reference"
	 *
	 * @param	string		Table name
	 * @param	integer		UID for the current record.
	 * @return	array		Item array, element in $menuItems
	 * @internal
	 */	
	function DB_pasteReference($table,$uid){
		
		$editOnClick = '';
		$loc = 'top.content'.($this->backRef->listFrame && !$this->backRef->alwaysContentFrame ?'.list_frame':'');
		if($GLOBALS['BE_USER']->jsConfirmation(2))	{
			$conf = $loc.' && confirm('.$GLOBALS['LANG']->JScharCode('Are you sure you wish to insert the reference into this category').')';
		} else {
			$conf = $loc;
		}
		$editOnClick = 'if('.$conf.'){'.$loc.'.location.href=top.TS.PATH_typo3+\''.$this->pasteUrl($table,$uid,0).'&redirect=\'+top.rawurlencode('.$this->backRef->frameLocation($loc.'.document').'); hideCM();}';

		return $this->backRef->linkItem(
			'Insert reference',
			$this->backRef->excludeIcon('<img'.t3lib_iconWorks::skinImg($this->backRef->PH_backPath.PATH_txcategories_rel,'gfx/clip_pastesubref.gif','width="12" height="12"').' alt="" />'),
			$editOnClick.'return false;'
		);	
	}
	
	/**
	 * Adding CM element for Clipboard "Create reference"
	 *
	 * @param	string		Table name
	 * @param	integer		UID for the current record.
	 * @return	array		Item array, element in $menuItems
	 * @internal
	 */
	function DB_createReference($table,$uid) {
		
		if ($this->clipboardObj->current=='normal')	{
			$isSel = $this->clipboardObj->isSelected($table,$uid);
		}		


		$baseArray = array();
		if ($this->backRef->listFrame)	{
			$baseArray['reloadListFrame'] = ($this->backRef->alwaysContentFrame ? 2 : 1);
		}

		$CB =	array(
				'el'=>array(
					rawurlencode($table.'|'.$uid) => 1
				)
			);
		$baseArray['CB'] = $CB;
		$baseArray['CB']['setRefMode'] = 'ref';
		$link = t3lib_div::linkThisScript($baseArray);	
		
		return $this->backRef->linkItem(
			'Create reference',
			$this->backRef->excludeIcon('<img'.t3lib_iconWorks::skinImg($this->backRef->PH_backPath.PATH_txcategories_rel,'gfx/clip_ref.gif','width="12" height="12"').' alt="" />'),
			"top.loadTopMenu('".$link."');return false;"
		);
		
	}


	function DB_newrecordincategory($table,$uid){
		$url = PATH_txcategories_rel."mod_newelement/index.php?id=".$uid;
		return $this->backRef->linkItem(
                    'New',
                    $this->backRef->excludeIcon('<img'.t3lib_iconWorks::skinImg($this->backRef->PH_backPath,'gfx/new_el.gif','width="11" height="12"').' />'),
                    $this->backRef->urlRefForCM($url),
                    1    // Disables the item in the top-bar. Set this to zero if you with the item to appear in the top bar!
                );
		
	}
	
	
	/**
	 * Adding CM element for regular Create new element
	 *
	 * @param	string		Table name
	 * @param	integer		UID for the current record.
	 * @return	array		Item array, element in $menuItems
	 * @internal
	 */
	function DB_new($table,$uid)	{
		$editOnClick='';
		$loc='top.content'.(!$this->backRef->alwaysContentFrame?'.list_frame':'');
		$editOnClick='if('.$loc.'){'.$loc.".location.href=top.TS.PATH_typo3+'".
			($this->backRef->listFrame?
				"alt_doc.php?returnUrl='+top.rawurlencode(".$this->backRef->frameLocation($loc.'.document').")+'&edit[".$table."][-".$uid."]=new&defVals[".$table."][".$this->cField."]=".$this->category."'":
				'db_new.php?id='.intval($uid)."'").
			';}';

		return $this->backRef->linkItem(
			$this->backRef->label('new'),
			$this->backRef->excludeIcon('<img'.t3lib_iconWorks::skinImg($this->backRef->PH_backPath,'gfx/new_'.($table=='pages'&&$this->listFrame?'page':'el').'.gif','width="'.($table=='pages'?'13':'11').'" height="12"').' alt="" />'),
			$editOnClick.'return hideCM();'
		);
	}	
	
	
	function DB_import($table,$uid){

		$url = PATH_txcategories_rel."mod_import/index.php?id=".$uid;
		return $this->backRef->linkItem(
			'Import',
			$this->backRef->excludeIcon('<img src="'.$this->backRef->PH_backPath.PATH_txcategories_rel.'gfx/import.gif" width="12" height="12" border="0" align="top" />'),
			$this->backRef->urlRefForCM($url),
			1    
		);		
	}
	
	
	function DB_editpage($table,$uid){
		
		$path = '';
		
		if(
			t3lib_extMgm::isLoaded('templavoila')
			&& tx_categories_befunc::userHasAccessToModule('web_txtemplavoilaM1')
		){
			
			$path = t3lib_extMgm::extRelPath('templavoila').'mod1/index.php';	
		
		} elseif(
			
			t3lib_extMgm::isLoaded('cms')
			&& tx_categories_befunc::userHasAccessToModule('web_layout')		
		
		){
			
			$path = t3lib_extMgm::extRelPath('cms').'layout/db_layout.php';
			
		}
		
		return $this->backRef->linkItem(
			'Edit',
			$this->backRef->excludeIcon('<img src="'.$this->backRef->PH_backPath.'gfx/edit_page.gif" width="12" height="12" alt="" />'),
			'top.fsMod.recentIds[\'web\']='.$uid.';top.content.list_frame.location=top.getModuleUrl(top.TS.PATH_typo3+"'.$path.'?"+\'&id=\'+top.rawurlencode(top.fsMod.recentIds[\'web\']));',
			1
		);
		
	}
	
	/**
	 * Adding CM element for View Page
	 *
	 * @param	integer		Page uid (PID)
	 * @param	string		Anchor, if any
	 * @return	array		Item array, element in $menuItems
	 * @internal
	 */
	function DB_view($id,$anchor='')	{
		return $this->backRef->linkItem(
			$this->backRef->label('view'),
			$this->backRef->excludeIcon('<img'.t3lib_iconWorks::skinImg($this->backRef->PH_backPath,'gfx/zoom.gif','width="12" height="12"').' alt="" />'),
			t3lib_BEfunc::viewOnClick($id,$this->backRef->PH_backPath,t3lib_BEfunc::BEgetRootLine($id),$anchor).'return hideCM();'
		);
	}	
	

	/**
	* Reads the [extDir]/locallang.xml and returns the $LOCAL_LANG array found in that file.
	*
	* @return    [type]        ...
	*/
	function includeLL()    {
		 global $LANG;

		 $LOCAL_LANG = $LANG->includeLLFile('EXT:categories/locallang.xml',FALSE);
		 return $LOCAL_LANG;
	}
	
	
	function getRow($table,$uid){
		if(is_array($this->row)){
			return $this->row;	
		}
		$this->row =  t3lib_BEfunc::getRecord($table,$uid);
		return $this->row;
	}


}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/class.tx_categories_cm.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/class.tx_categories_cm.php']);
}

?>