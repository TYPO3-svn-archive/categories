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
 * Contains the tx_categories_div class with miscellaneous functions for use in backend and frontend
 *
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 */
/**
 * Miscellaneous functions for use with categories in backend and frontend scripts
 * Don't instantiate - call functions with "tx_categories_div::" prefixed the function name.
 *
 * @author	Mads Brunn <mads@brunn.dk>
 * @package 	TYPO3
 * @subpackage 	categories
 */

class tx_categories_div{
	
	
	/**
	 * Returns a comma-separated list of subcategories
	 *
	 * @param	string / array	$catUidList: Array or commaseparated list of category uids for which to find subcategories
	 * @param	integer		$recursive: Number of levels to descend
	 * @param	boolean		$show_hidden: If this flag is set hidden categories are included in the list
	 * @param	boolean		$include_self: If this flag is set the categories in first argument will be included in the return value
	 * @return	string		comma-separated list of category uid's
	 */
	function getSubCategoriesAsUidList($catUidList,$recursive=0,$show_hidden=0,$include_self=0){
		
		if($recursive < 1) return $catUidList;
		$category_list = array();
		if(!is_array($catUidList)){
			$catUidList = t3lib_div::intExplode(",",$catUidList);
		}
		while(list(,$catUid) = each($catUidList)){
			tx_categories_div::_getSubCategories($catUid,$category_list,$recursive,$show_hidden,$include_self);
		}
		return trim(implode(',',array_unique($category_list)));
	}


	/**
	 * Helper function: finds subcategories recursively
	 * 
	 * @param	integer		$catUid: uid of the category record to find subcategories for
	 * @param	array		$categorylist: array of previously found category uids
	 * @param	integer		$recursive: number of levels to descend to in the category tree
	 * @param	boolean		$show_hidden: if this flag is set, hidden category records will be included
	 */
	function _getSubCategories($catUid,&$categorylist,$recursive,$show_hidden=0,$include_self=0){

		
		$ctable = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['categories']['table'];
		$mm = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['categories']['MM'];
		
		if($include_self && $catUid > 0){
			$categorylist[$catUid] = $catUid;
		}
		if($recursive < 1) return 0;
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'*',
						$ctable.' '.($catUid?'INNER':'LEFT').' JOIN '.$mm.' mm 
						 ON '.$ctable.'.uid = mm.uid_local '.($catUid ? 'AND mm.uid_foreign='.intval($catUid):'').' AND mm.localtable="tx_categories"',
						 
						($catUid?'':'mm.uid_foreign IS NULL AND ').' '.$ctable.'.deleted=0'.(!$show_hidden ? ' AND '.$ctable.'.hidden=0': ''),
						'',
						'title',
						''
					);
		
		while($CATEGORY = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
			tx_categories_div::_getSubCategories($CATEGORY['uid'],$categorylist,$recursive-1,$show_hidden,1);
		}
	}
	
	
	/**
	 * Returns a commaseparated list of category uid's for a record
	 *
	 * @param	integer		$uid: uid of the record for which to find categories
	 * @param	string		$mm_table: MM-relation table to use for looking up the uids.
	 * @return	string		if categories are found, a comma-separated list of uid's, otherwise false
	 */
	function getCategoriesForRecord($uid,$table){

		$mm = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['categories']['MM'];		
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid_foreign',$mm,'uid_local='.$uid.' AND localtable="'.$table.'"');
		$tmp = array();
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res)){
			$tmp[$row[0]] = $row[0];
		}
		if(count($tmp)){
			return implode(",",$tmp);	
		} else {
			return FALSE;	
		}
	}
	
	/**
	 * Checks whether a category has subcategories
	 *
	 * @param	integer		$catUid: uid of the category for which to check for subcategories
	 * @return	boolean		TRUE if the category has subcategories, otherwise FALSE
	 */
	function hasSubCategories($catUid,$count_hidden=0){

		$ctable = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['categories']['table'];
		$mm = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['categories']['MM'];
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'COUNT(*)',
						$ctable.' INNER JOIN '.$mm.' mm ON '.$ctable.'.uid = mm.uid_local AND mm.uid_foreign='.intval($catUid).' AND mm.localtable="'.$ctable.'"',
						'deleted=0'.(!$count_hidden ? ' AND hidden=0': ''),
						'',
						'title',
						''
					);
		if($row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res)){
			if($row[0] > 0) return TRUE;
		} 
		return FALSE;
	}	
	
	/**
	 * Returns the path (visually) of a category $uid, fx. "/First category/Second category/Another category"
	 * Each part of the path will be limited to $titleLimit characters
	 * Deleted pages are filtered out.
	 * Usage: 15
	 *
	 * @param	integer		Category uid for which to create record path
	 * @param	string		$clause is additional where clauses, eg. "
	 * @param	integer		Title limit
	 * @param	integer		Title limit of Full title (typ. set to 1000 or so)
	 * @return	mixed		Path of record (string) OR array with short/long title if $fullTitleLimit is set.
	 */
	function getCategoryPath($catUid, $clause='', $titleLimit=0, $fullTitleLimit=0)	{

		$ctable = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['categories']['table'];
		$mm = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['categories']['MM'];
		
		if (!$titleLimit) { $titleLimit=1000; }

		$loopCheck = 100;
		$output = $fullOutput = '/';
		while ($catUid!=0 && $loopCheck>0)	{
			$loopCheck--;

			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
						'uid,pid,title, mm.uid_foreign AS parent',
						$ctable.' LEFT JOIN '.$mm.' mm ON mm.uid_local='.$ctable.'.uid AND mm.localtable="'.$ctable.'"',
						$ctable.'.uid='.intval($catUid).t3lib_BEfunc::deleteClause($ctable).
							(strlen(trim($clause)) ? ' AND '.$clause : '')
					);
					
			if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))	{
				$catUid = $row['parent'];
				$output = '/'.t3lib_div::fixed_lgd_cs(strip_tags($row['title']),$titleLimit).$output;
				if ($fullTitleLimit)	$fullOutput = '/'.t3lib_div::fixed_lgd_cs(strip_tags($row['title']),$fullTitleLimit).$fullOutput;
			} else {
				break;
			}
		}

		if ($fullTitleLimit)	{
			return array($output, $fullOutput);
		} else {
			return $output;
		}
	}	
	
	
	function getRecordPath($uid,$table){

		$ctable = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['categories']['table'];
		$mm = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['categories']['MM'];		
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
							$ctable.'.uid',
							$ctable.' INNER JOIN '.$mm.' mm ON mm.uid_foreign='.$ctable.'.uid AND mm.uid_local='.$uid.' AND localtable="'.$table.'"',
							'deleted=0'
						);
		if($row = $GLOBALS['TYPO3_DB']->sql_fetch_row($res)){
			return tx_categories_div::getCategoryPath($row[0]);
		}
	}
	
	/**
	 * Generates an array with info about a category
	 * @param	integer		$uid:uid of the tx_categories record
	 * @return	array		array with info about the category
	 */
	function getCategoryInfo($uid){
		$catinfo = array();
		
		$ctable = $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['categories']['table'];		
		
		if($uid){
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$ctable,'uid='.$uid.t3lib_BEfunc::deleteClause($ctable));
			
			if($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
				$catinfo = $row;
				list($catinfo['_thePath'],$catinfo['_thePathFull'])  = tx_categories_div::getCategoryPath($uid,'',30,1000);
			}
		}
		return $catinfo;
	}

	
	
	/**     
	 * Returns the pid of the Category folder.     
	 * This pid has to be used for storage of tx_category records.     
	 *     
	 * @return    integer        Current/default category folder pid for storage.     
	 */    
	function getPid() {
	 	static $pid = 0;

		if(!$pid AND is_object($GLOBALS['TYPO3_DB'])) {

			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid', 'pages', 'doktype=254 and module="categories" AND deleted=0');
			if ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res))    {                
				$pid = $row['uid'];
			} else {
				tx_categories_div::createPid();
			}
			
	 	}
	 	return $pid;
	}
	 
	 
	/**
	 * Creates category storage folder in database
	 */
	function createPid($pid=0) {

		$fields_values = array();
		$fields_values['pid'] = $pid;
		$fields_values['sorting'] = 29999;
		$fields_values['perms_user'] = 31;
		$fields_values['perms_group'] = 31;
		$fields_values['perms_everybody'] = 31;
		$fields_values['title'] = 'Categories';
		$fields_values['doktype'] = 254;
		$fields_values['module'] = 'categories';
		$fields_values['crdate'] = time();
		$fields_values['tstamp'] = time();
		return $GLOBALS['TYPO3_DB']->exec_INSERTquery('pages', $fields_values);
	 }   
	 
	 
	 
	/**
	 * Returns true if the tablename $checkTable is allowed to be created on the page with record $pid_row
	 *
	 * @param	array		Record for parent page.
	 * @param	string		Table name to check
	 * @return	boolean		Returns true if the tablename $checkTable is allowed to be created on the page with record $pid_row
	 * @see typo3/db_new.php:SC_db_new->isTableAllowedForThisPage
	 */
	function isTableAllowedForThisPage($pid_row, $checkTable)	{
		global $TCA, $PAGES_TYPES;
		if (!is_array($pid_row))	{
			if ($GLOBALS['BE_USER']->user['admin'])	{
				return true;
			} else {
				return false;
			}
		}
			// be_users and be_groups may not be created anywhere but in the root.
		if ($checkTable=='be_users' || $checkTable=='be_groups')	{
			return false;
		}
			// Checking doktype:
		$doktype = intval($pid_row['doktype']);
		if (!$allowedTableList = $PAGES_TYPES[$doktype]['allowedTables'])	{
			$allowedTableList = $PAGES_TYPES['default']['allowedTables'];
		}
		if (strstr($allowedTableList,'*') || t3lib_div::inList($allowedTableList,$checkTable))	{		// If all tables or the table is listed as a allowed type, return true
			return true;
		}
	}		 
	
	/**
	 * Checks if a table is allowed to be categorized
	 *
	 * @param	string		$table: table to check
	 * @return	boolean		true if table is allowed to be categorized
	 */
	function isTableAllowedForCategorization($table){
		
		global $TCA;
		
		//if the table is the category table, we return immediately. 
		if($table == $GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['categories']['table']) return TRUE;
		
		if($TCA[$table]['ctrl']['EXT']['categories']['exclude']) return FALSE;

		return TRUE;		
	}
	 

	/**
	 * Returns correct fieldname for the category field in table $table
	 * 
	 * @param	string		$table: table for which a category fieldname should be generated
	 * @return	string		fieldname to be used in the TCA
	 */
	function getCategoryFieldName($table){
		
		return '*';
		
		//TODO:
		//return t3lib_div::shortMD5('tx_categories'.$table);
		//requires change in t3lib_TCEmain
	}
	
	
}

//no XCLASS - doesn't make sense as this class is never instantiated
?>
