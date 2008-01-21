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
 * Contains the tx_categories_befunc class with miscellaneous functions for use in the BE 
 *
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 */
/**
 * Miscellaneous functions for use with categories in the BE
 * Don't instantiate - call functions with "tx_categories_befunc::" prefixed the function name.
 *
 * @author	Mads Brunn <mads@brunn.dk>
 * @package 	TYPO3
 * @subpackage 	categories
 */
 
 
class tx_categories_befunc{

	
	/**
	 * Returns TRUE if the logged in BE user has access to a BE-module.
	 *
	 * @return	boolean		TRUE or FALSE
	 * @access	protected
	 */
	function userHasAccessToModule($modulename) {
		global $BE_USER;
		if (!t3lib_BEfunc::isModuleSetInTBE_MODULES($modulename)) return FALSE;
		if ($BE_USER->isAdmin()) return TRUE;
		return $BE_USER->check('modules', $modulename);
	}
	

	
	/**
	 * Get category rootline
	 *
	 * @return	array	the rootline from the category with uid=$id to the root
	 */
	function BEgetCategoryRootLine($id){
		
		
		
	}

	
	
	/**
	 * Checks if a category is within the current BE-users category mounts 
	 *
	 * @param	integer		Category ID to check
	 * @param	boolean		If set, then the function will exit with an error message.
	 * @return	integer		The page UID of a page in the rootline that matched a mount point
	 */
	function isInCategoryMount($id,$exitOnError=0){
		
		if($GLOBALS['BE_USER']->isAdmin()) return 1;

		$mounts = tx_categories_befunc::getCategoryMounts();


	}


	/**
	 * Returns the current BE-users category mount points
	 *
	 * @return	array		an array of category uid's
	 */
	function getCategoryMounts(){

		global $BE_USER;
		
		if(!isset($BE_USER->groupData['categorymounts'])){
		
			$categorymounts = $BE_USER->user['tx_categories_mountpoints'];
			if(($BE_USER->user['options']&4) == 4){
				foreach($BE_USER->userGroups as $groupid => $group){
					$categorymounts .= ','.$group['tx_categories_mountpoints'];
				}
			}
			if($BE_USER->isAdmin()){
				$categorymounts = '0,'.$categorymounts;
			}
			$BE_USER->groupData['categorymounts'] = t3lib_div::uniqueList($categorymounts);
			
			//if no mountpoint has been set for the current, she gets to see the whole category tree
			if(!$BE_USER->isAdmin() && ($BE_USER->groupData['categorymounts'] == '')){
				$BE_USER->groupData['categorymounts'] = '0';
			}
		}
		
		return (string)($BE_USER->groupData['categorymounts'])!='' ? explode(',',$BE_USER->groupData['categorymounts']) : Array();

	}
} 
 
 
 
//no XCLASS - this class is never instantiated
?>