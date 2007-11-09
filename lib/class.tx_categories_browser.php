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
 * Contains the tx_categories_browser class which is used in various tceform wizards
 * NOTE: work in progress
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

require_once(PATH_typo3.'class.browse_links.php');

class tx_categories_browser extends browse_links{


	var $mode;
	var $pObj;
	

	function tx_categories_browser(){
	
		//default constructor
	}


	function isValid($mode,$pObj){
	
		if($mode == 'categories' || $mode == 'recordsincategories') return TRUE;
		
		
		return FALSE;
	
	}

	
	function render($mode,$pObj){
		
		$this->mode = $mode;
		
		
		$out = array();
		
		switch($this->mode){

			case 'categories':
				$out[] = $this->main_categories();
				break;
				
			case 'recordsincategories':
				$out[] = $this->main_recordsincategories();
				break;
				
			default:
				break;
		}
		return implode("\n",$out);
	
	}
	
	function main_categories(){
		$out = array();
		
		return implode("\n",$out);
	}
	
	function main_recordsincategories(){
		$out = array();
		
		return implode("\n",$out);
	}
	
	
	
	/**
	 * Creates a listing of records from the given list of tables in the category
	 */
	function listCategory($tables){
		
		$out = array();
		
		s
		
		
		return implode("\n",$out);
		
	}
	
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/lib/class.tx_categories_browser.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/lib/class.tx_categories_browser.php']);
}
?>
