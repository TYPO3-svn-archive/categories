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
 * Contains the tx_categories_perms class with functions for 
 * calculating allowed categories for be-users 
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 */
/**
 * Functions permission-related stuff
 *
 * @author	Mads Brunn <mads@brunn.dk> 
 * @package 	TYPO3
 * @subpackage 	categories
 */

class tx_categories_perms{
	
	/**
	 * Checks if a category is within the users category mounts 
	 *
	 * @param	integer		Category ID to check
	 * @param	boolean		If set, then the function will exit with an error message.
	 * @return	integer		The page UID of a page in the rootline that matched a mount point
	 */
	function isInCategoryMount($id,$exitOnError=0){
		
		if($GLOBALS['BE_USER']->isAdmin()) return 1;
		
		$mounts = tx_categories_perms::getCategoryMounts();

		$id = intval($id);

		$ok = FALSE;
		
		while($uid > 0){
			$res = $GLOBALS['TYPO3_DB']->execSELECTquery(
											'tx_categories.*',
											'tx_categories INNER JOIN tx_categories_mm mm ON mm.localtable="tx_categories" uid_foreign=tx_categories.uid AND uid_local='.$uid,
											'1=1 '.t3lib_BEfunc::deleteClause('tx_categories')
										);
		}
	}
	
	
	function getCategoryMounts(){
		
	 	static $mounts = array();
		
		
	}


}

//No XCLASS - this class is never instantiated
?>