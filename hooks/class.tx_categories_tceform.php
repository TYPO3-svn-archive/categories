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
 * Contains the tx_categories_tceform class. Hook functions for tceform 
 *
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 */
/**
 * Hook functions for use in tceform
 *
 * @author	Mads Brunn <mads@brunn.dk>
 * @package 	TYPO3
 * @subpackage 	categories
 */

require_once(PATH_t3lib.'class.t3lib_transferdata.php');

class tx_categories_tceform {


	/**
	 * Hook used to transfer value to MM field with defVals
	 */
	function getMainFields_preProcess($table,&$row,$pObj){

		global $TYPO3_CONF_VARS,$TCA,$BE_USER;
		
		$ctable = $TYPO3_CONF_VARS['EXTCONF']['categories']['table'];
		$mm = $TYPO3_CONF_VARS['EXTCONF']['categories']['MM'];			
		
		//disabling the doc- and cache selector for categories 		
		if($table == $ctable)	$GLOBALS['TYPO3_CONF_VARS']['BE']['defaultPageTSconfig'] .= "\nmod.xMOD_alt_doc.disableDocSelector = 1\nmod.xMOD_alt_doc.disableCacheSelector = 1";

		if($BE_USER->workspace == 0){	//only in online workspace
			
			//here we should create a fake categories field in the tca
			if(tx_categories_div::isTableAllowedForCategorization($table)){
				
				$cfield = tx_categories_div::getCategoryFieldName($table);

				t3lib_div::loadTCA($table);
				if(!isset($TCA[$table]['columns'][$cfield])){
					include(PATH_txcategories.'tca/tx_categories_tca.inc');
				}
				$trData = t3lib_div::makeInstance('t3lib_transferData');
				$row[$cfield] = $trData->renderRecord_selectProc($row[$cfield],$TCA[$table]['columns'][$cfield],array(),$table,$row,$cfield);

				if(strstr($row['uid'],'NEW')){
					if($defVals = t3lib_div::_GP('defVals')){
						
						if(isset($defVals[$table][$cfield])){
							$parents = t3lib_div::intExplode(',',$defVals[$table][$cfield]);
							if(count($parents)){
								$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
												'*',
												$ctable,
												'uid IN ('.implode(',',$parents).')'.
												t3lib_BEfunc::deleteClause($ctable)
											);
											
								$tmp = array();
								while($tmprow = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
									$tmp[] = $tmprow['uid'].'|'.rawurlencode($tmprow['title']);						
								}
								if(count($tmp)){
									$row[$cfield] = implode(',',$tmp);	
								}
							}
						}
					}
				}	

			}
		}
	}
}



?>