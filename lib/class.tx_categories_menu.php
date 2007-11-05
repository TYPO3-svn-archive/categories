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
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Contains the tx_categories_menu class with miscellaneous functions for 
 * creating category menu's in the frontend 
 *
 * Use it by extending it
 *
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 */
/**
 *
 * @author	Mads Brunn <mads@brunn.dk>
 * @package 	TYPO3
 * @subpackage 	categories
 */



require_once(PATH_tslib.'class.tslib_pibase.php');

class tx_categories_menu extends tslib_pibase{

	var $prefixId = 'tx_categories';		
	var $scriptRelPath = 'lib/class.tx_categories_menu.php';	
	var $extKey = 'categories';	// The extension key.
	var $catRootLine = array();
	var $categoryTable = 'tx_categories';
	var $enableFields = '';
	
	/**
	 * Generates menu array for use in a HMENU
	 *
	 */
	function tree($content,$conf){
	
		//debug($GLOBALS['TSFE']->applicationData);

		$this->conf =  $conf;
		
		$this->lcObj = t3lib_div::makeInstance('tslib_cObj');

		//check if we are getting the rootid of the menu from typoscript
		if($this->conf['rootId.']){
			$this->conf['rootId'] = $this->cObj->stdWrap($this->conf['rootId'],$this->conf['rootId.']);
		}
		//check if the menu should be expanded
		if($this->conf['expand.']){
			$this->conf['expand'] = $this->cObj->stdWrap($this->conf['expand'],$this->conf['expand.']);
		}
		
		$this->enableFields = $this->cObj->enableFields('tx_categories');
		$this->rootId = $this->conf['rootId'] ? $this->conf['rootId'] :0;
		$this->expand = $this->conf['expand'] ? $this->conf['expand'] :0;
		$this->setCatRootline();
		$menuItemsArray = array(); 
		$path = array();
		$path[] = $this->rootId;
		$this->getMenuTree($this->rootId,$menuItemsArray,$path,$this->expand);
		return $menuItemsArray;

	}
	
	
	
	function rootline($content,$conf){
		$this->conf =  $conf;

		//check if we are getting the rootid of the menu from typoscript
		if($this->conf['rootId.']){
			$this->conf['rootId'] = $this->cObj->stdWrap($this->conf['rootId'],$this->conf['rootId.']);
		}
	}

	
	
	
	
	
	/**
	 * Calculating the rootline so we know which part of the menu is active
	 */
	function setCatRootline(){
		$path = isset($this->piVars['path']) ? $this->piVars['path'] : '';
		if($path){
			$parts = t3lib_div::trimExplode("_",$path,1);
			if(count($parts)){
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_categories','uid IN ('.implode(",",$parts).')'.$this->enableFields);
				while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
					$this->catRootLine[$row['uid']] = $row;				
				}				
			}					
		}
	}	
	
	
	/**
	* Generating the complete menu array. Called recursively
	*/
	function getMenuTree($uid,&$menuItemsArray,$path,$expand=0){

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
								'tx_categories.*',
								'tx_categories '.($uid?'INNER':'LEFT').' JOIN tx_categories_mm mm ON tx_categories.uid=mm.uid_local AND mm.localtable="tx_categories" '.($uid? 'AND mm.uid_foreign='.$uid : ''),
								($uid?'1=1':'mm.uid_foreign IS NULL').$this->enableFields
							);
		
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
			$oldpath = $path;
			$path[] = $row['uid'];
			$this->getElementAndSub($row,$menuItemsArray,$path,$expand);
			$path = $oldpath;
		}		
	}
	
	
	/**
	 * Getting a single element in the menu and possibly it's subelements if the element is active
	 * @param array the category record
	 * @param array the complete menu (passed by reference)
	 */
	function getElementAndSub($item,&$menuItemsArray,$path,$expand){
		
		$additionalParams = '';
		if($this->conf['additionalParams'] || $this->conf['additionalParams.']){ // adding specific params
			$this->lcObj->data = $item;
			$additionalParams .= $this->lcObj->stdWrap($this->conf['additionalParams'],$this->conf['additionalParams.']);
		} else {
			$additionalParams .= '&'.$this->prefixId.'[cid]='.$item['uid'];
		}
		if(count($path)){
			//hardcoded path param. Mostly for allowing realurl to create a nice path
			$additionalParams .= '&'.$this->prefixId.'[path]='.implode('_',$path);
		}

		$url = $this->cObj->typolink(
						'',
						array(
							'parameter' => $GLOBALS['TSFE']->id,
							'additionalParams' => $additionalParams,
							'returnLast' => 'url',
							'useCacheHash' => 1,
						)
					);

		//pushing a new item on the menu
		$i = array_push($menuItemsArray,$item)-1;
		$menuItemsArray[$i]['title'] = $item['title'];
		$menuItemsArray[$i]['_OVERRIDE_HREF'] = $url;
		if(isset($this->catRootLine[$item['uid']]) || $expand > 0){

			if($item['uid'] == $this->piVars['cid']){
				$menuItemsArray[$i]['ITEM_STATE'] = 'CUR';
			} else {
				$menuItemsArray[$i]['ITEM_STATE'] = 'ACT';
			}
			$temp_menuItemsArray = array();			
			$this->getMenuTree($item['uid'],$temp_menuItemsArray,$path,$expand-1);
			$menuItemsArray[$i]['_SUB_MENU'] = $temp_menuItemsArray;

		}
	}
}





?>
