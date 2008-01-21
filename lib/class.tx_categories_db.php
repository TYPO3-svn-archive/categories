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
 * Contains the tx_categories_db class which is handling most db-related stuff for categories
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

class tx_categories_db{
	

	/**
	 * Returns a resultset with the child categories for category with $uid
	 * NOTICE: This method does not check for deleted or hidden records. 
	 *
	 * @param	integer		$uid: uid of the category
	 * @param	string		$selectFields: fields to select
	 * @param	string		$andWhere: Additional SQL-filter - must start with ' AND' 
	 * @param	string		$orderBy: ORDER BY clause
	 * @return	pointer		MySQL result pointer / DBAL object
	 */
	function exec_SELECTquery_getChildren($uid,$selectFields='*',$andWhere='',$orderBy=''){
		global $TYPO3_CONF_VARS;
		
		return $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					$selectFields,	
					$TYPO3_CONF_VARS['EXTCONF']['categories']['table'].' '.($uid ? 'INNER' : 'LEFT').' JOIN '.$TYPO3_CONF_VARS['EXTCONF']['categories']['MM'].' mm 
					ON '.$GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['categories']['table'].'.uid=mm.uid_local '.($uid ? 'AND mm.uid_foreign='.$uid : '').' AND mm.localtable="'.$TYPO3_CONF_VARS['EXTCONF']['categories']['table'].'"',
					($uid ? '1=1':'mm.uid_foreign IS NULL').' '.$andWhere,
					'',
					$orderBy							
				);
	}

	
	
	/**
	 * Returns the number of child categories for the category with $uid
	 * NOTICE: This method does not check for deleted or hidden records. 	 
	 *
	 * @param	integer		$uid: uid of the category
	 * @param	string		$andWhere: Additional SQL-filter - must start with ' AND' 
	 * @return	integer
	 */
	function countChildren($uid,$andWhere=''){
		
		global $TYPO3_CONF_VARS,$TYPO3_DB;

		$res = $TYPO3_DB->exec_SELECTquery(
					'count(*)',	
					$TYPO3_CONF_VARS['EXTCONF']['categories']['table'].' '.($uid ? 'INNER' : 'LEFT').' JOIN '.$TYPO3_CONF_VARS['EXTCONF']['categories']['MM'].' mm 
					ON '.$TYPO3_CONF_VARS['EXTCONF']['categories']['table'].'.uid=mm.uid_local '.($uid ? 'AND mm.uid_foreign='.$uid : '').' AND mm.localtable="'.$TYPO3_CONF_VARS['EXTCONF']['categories']['table'].'"',
					($uid ? '1=1':'mm.uid_foreign IS NULL').' '.$andWhere,
					'',
					''							
				);
				
		if($row = $TYPO3_DB->sql_fetch_row($res)){
			return $row[0];
		}
		
		return 0;
	}	
	
	
	function getCategory($uid,$selectFields='*',$andWhere=''){

		global $TYPO3_CONF_VARS,$TYPO3_DB;

		$res = $TYPO3_DB->exec_SELECTquery(
							$selectFields,
							$TYPO3_CONF_VARS['EXTCONF']['categories']['table'],
							'uid='.$uid.' '.$andWhere
						);
		
	}
	
}

//no XCLASS - this class is never instantiated
?>