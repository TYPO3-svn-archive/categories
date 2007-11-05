<?php

require_once(t3lib_extMgm::extPath('categories').'lib/class.tx_categories_div.php');

class tx_categories_tcemain {
	
	var $errLevel = 1;


	/*******************************************
	 *
	 * processDatamap
	 *
	 *******************************************/
	 
	 /**
	  * Hook function that does some preprocessing on the raw data array 
	  * 
	  */
	 
	function processDatamap_preProcessFieldArray(&$incomingFieldArray, $table, $id, &$pObj){
		
		
		global $LANG,$TCA,$TYPO3_CONF_VARS;
		
		$ctable = $TYPO3_CONF_VARS['EXTCONF']['categories']['table'];
		$mm = $TYPO3_CONF_VARS['EXTCONF']['categories']['MM'];		
		
		$LANG->includeLLFile("EXT:categories/locallang.xml");	

		$cfield = tx_categories_div::getCategoryFieldName($table);

		
		// Create a fake category field in the TCA
		if(tx_categories_div::isTableAllowedForCategorization($table)){ //if the table isn't excluded from categorization
			
			
			t3lib_div::loadTCA($table);
			if(!isset($TCA[$table]['columns'][$cfield])){
				include(PATH_txcategories.'tca/tx_categories_tca.inc');
			}
			
			//here we store the create-pid for this table
			$moduledata = $GLOBALS['BE_USER']->getModuleData('txcategoriesnewelement');
			if(	
				t3lib_div::testInt($incomingFieldArray['pid']) &&
				(!isset($moduledata[$table.'_create_pid'])
				|| $moduledata[$table.'_create_pid'] != $incomingFieldArray['pid'])
			){

					$pid_row = t3lib_BEfunc::getRecord('pages',$incomingFieldArray['pid']);
				
					if(tx_categories_div::isTableAllowedForThisPage($pid_row,$table)){		//check if table is allowed for this page
						$moduledata[$table.'_create_pid'] = $incomingFieldArray['pid'];
						$GLOBALS['BE_USER']->pushModuleData('txcategoriesnewelement',$moduledata);
						$GLOBALS['BE_USER']->writeUC();
					}
					
			}
			
		}
		
		
		if($table == $ctable){

			//if the user is allowed to create categories, we don't bother if the storage page is in the users web mount or not
			if($pObj->BE_USER->check('tables_modify',$table)){
				$pObj->isInWebMount_Cache[tx_categories_div::getPid()] = TRUE;
			}
		
			//forcing pid to a certain value
			$incomingFieldArray['pid'] = tx_categories_div::getPid();
			
			//Prevent a user from making illegal relations which otherwise would result in endless loops
			if(t3lib_div::testInt($id)){
				$parents = t3lib_div::trimExplode(",",$incomingFieldArray[$cfield],1);

				//checking if someone is trying to make the category a parent of itself
				if(in_array($id,$parents)){
					$parents = t3lib_div::removeArrayEntryByValue($parents,$id); 
					$incomingFieldArray[$cfield] = implode(',',$parents);
					
					$LANG->includeLLFile("EXT:categories/locallang.xml");					
					$err_mess = $LANG->getLL("illegal_parent_msg");
					$pObj->log($table,$id,2,0,$this->errLevel,$err_mess,0,array(),$id,'');
				}
				
				if(count($parents)){
					$childs = tx_categories_div::getSubCategoriesAsUidList($id,999,1,0);
					$childs = explode(',',$childs);

					$illegal_parents = array();
					foreach($parents as $parent){
						if(in_array($parent,$childs)){
							$illegal_parents[] = $parent;
						}
					}
					
					if(count($illegal_parents)){
						foreach($illegal_parents as $illegal_parent){
							$parents = t3lib_div::removeArrayEntryByValue($parents,$illegal_parent);
						}
						$incomingFieldArray[$cfield] = implode(',',$parents);	
						
						$err_mess = $LANG->getLL("illegal_parent2_msg");
						$pObj->log($table,$id,2,0,$this->errLevel,$err_mess,0,array(),$id,'');						
					}
				}
			}
		}
	}

	
	function processDatamap_postProcessFieldArray($status, $table, $id, &$fieldArray, $pObj){
		
		global $TYPO3_CONF_VARS;
		
		$ctable = $TYPO3_CONF_VARS['EXTCONF']['categories']['table'];
		$mm = $TYPO3_CONF_VARS['EXTCONF']['categories']['MM'];		
		$cfield = tx_categories_div::getCategoryFieldName($table);
		
		// if a category record has changed we need 
		// to make sure that the category tree is updated
		if($table == $ctable){
			if(count($fieldArray)){
				t3lib_BEfunc::getSetUpdateSignal('updatePageTree');
			}
		}
		
		// At this point the MM-relations have been created so now we 
		// unset the "star"-field in order to avoid any sql-errors
		unset($fieldArray[$cfield]);

	}
	
	function processDatamap_afterDatabaseOperations($status, $table, $id, $fieldArray, $pObj){

		
		
	}		
	
	
	/*******************************************
	 *
	 * processCmdmap
	 *
	 *******************************************/	
	
	function processCmdmap_preProcess($command, $table, $id, $value, $pObj){
		
		global $TYPO3_CONF_VARS;
		
		$ctable = $TYPO3_CONF_VARS['EXTCONF']['categories']['table'];
		$mm = $TYPO3_CONF_VARS['EXTCONF']['categories']['MM'];	
		
		if($table == $ctable && $command == 'delete'){
			
			/*
			 * TODO:
			 * Check if the current category has subcategories and that
			 * the user is allowed to delete sub categories recursively
			 */
		}
		
	}

	
	function processCmdmap_postProcess($command, $table, $id, $value, $pObj){

		global $TYPO3_CONF_VARS;
		
		$ctable = $TYPO3_CONF_VARS['EXTCONF']['categories']['table'];
		$mm = $TYPO3_CONF_VARS['EXTCONF']['categories']['MM'];			
		
		// This is used to ensure that whenever a record is copied 
		// any relations to categories are copied as well
		// we need to do it this way because the category field (the * field)
		// does not exist in the tca and therefore will not take it
		// into account automatically
		if($command == 'copy'){
			if(!empty($pObj->copyMappingArray)){
				foreach($pObj->copyMappingArray as $t => $mappings){
					foreach($mappings as $oldId => $newId){
						$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$mm,'uid_local='.$oldId.' AND localtable="'.$t.'"');
						while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
							
							$res2 = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$mm,'uid_local='.$newId.' AND uid_foreign='.$row['uid_foreign'].' AND localtable="'.$t.'"');
							if(!$GLOBALS['TYPO3_DB']->sql_num_rows($res2)){
								$GLOBALS['TYPO3_DB']->exec_INSERTquery($mm,array('uid_local'=>$newId,'uid_foreign'=>$row['uid_foreign'],'localtable'=>$t));
							}
						}
					}
				}
			}
		}
		
		// We need to make sure that the category tree gets updated
		if($table == $ctable && $command == 'delete'){
			t3lib_BEfunc::getSetUpdateSignal('updatePageTree');
			
			/* 
			 * TODO:
			 * Check if sub categories should be deleted too
			 *
			 */
		}
		
	}


	/*******************************************
	 *
	 * moveRecord
	 *
	 *******************************************/	
	
	function moveRecord_firstElementPostProcess($table, $uid, $destPid, $moveRec, $updateFields, $pObj){
	
	}
	
	
	function moveRecord_afterAnotherElementPostProcess($table, $uid, $destPid, $origDestPid, $moveRec, $updateFields, $pObj){
	
	}
	
	
	/*******************************************
	 *
	 * clear_cache
	 *
	 *******************************************/	
	
	function clearPageCacheEval($params,$pObj){

		/* 
		 * TODO:
		 * Assess if cache should be cleared when updating the category tree
		 *
		 */
	}
	
	
	
	/*******************************************
	 *
	 * clear_cacheCmd
	 *
	 *******************************************/		
	
	
	
	function clearCachePostProc($params,$pObj){

		/* 
		 * TODO:
		 * Assess if cache should be cleared when updating the category tree
		 *
		 */
		
	
	}
	
	

}


?>