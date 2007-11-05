<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Mads Brunn <mads@brunn.dk>
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
 * Module extension (addition to submodule menu) 'Importing the OIO Subject Scheme' for the 'categories' extension.
 *
 * @author Mads Brunn <mads@brunn.dk>
 */



require_once(PATH_t3lib.'class.t3lib_extobjbase.php');
require_once(PATH_txcategories.'lib/xmlparser.php');

class tx_categories_importmodfunc2 extends t3lib_extobjbase {
	
	var $prefixId = 'tx_categories_importmodfunc2';
	var $broaderItemBank = array();
	var $relatedItemBank = array();

	/**
	 * Returns the module menu
	 *
	 * @return	Array with menuitems
	 */

	function modMenu()	{

		return array(
				$this->prefixId.'_url' => '',
				$this->prefixId.'_startingpoint' => '',
			);
	}

	/**
	 * Main method of the module
	 *
	 * @return	HTML
	 */
	function main()	{
		// Initializes the module. Done in this function because we may need to re-initialize if data is submitted!
		global $SOBE,$BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		
		if(t3lib_div::_GP('doImport')){

			$GLOBALS['TYPO3_DB']->sql_query('TRUNCATE tx_categories');
			$GLOBALS['TYPO3_DB']->sql_query('TRUNCATE tx_categories_mm');
			$GLOBALS['TYPO3_DB']->sql_query('TRUNCATE tx_categories_related_category_mm');

			$this->doImport();
		}

		$out = array();
		$out[] = $LANG->getLL('label.url').'<input type="text" name="SET['.$this->prefixId.'_url]" size="48" value="'.$this->getVar('url').'" /><br />';
		$out[] = $LANG->getLL('label.startingpoint').'<input type="text" name="SET['.$this->prefixId.'_startingpoint]" size="10" value="'.$this->getVar('startingpoint').'" />';
		$out[] = '<br /><br /><input type="submit" name="doImport" value="'.$LANG->getLL('label.submit').'" />';
		$content = $this->pObj->doc->section($LANG->getLL("title"),implode("\n",$out),0,1);
		return $content;

	}
	
	
	function doImport(){
		global $LANG;
		
		$data = $this->getData();
		
		
		//echo t3lib_div::view_array($data);
		
		
		if(is_array($data) && isset($data['HierarchicalControlledList'])){
			$this->pObj->disableOutput();	
			error_reporting(0);
			echo $this->pObj->doc->startPage($LANG->getLL('title'));
			echo $this->pObj->getStartPageHTML();
			echo $this->pObj->getProgressBarHTML();	
			flush();
			$this->import($data['HierarchicalControlledList']);

			print_r($this->relatedItemBank);
			//import related
			foreach($this->relatedItemBank as $k => $ids){
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_categories','orig_id IN("'.implode('","',$ids).'")');
				while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
					$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_categories_related_category_mm',array('uid_local' => $k,'uid_foreign' => $row['uid']));
				}
			}
			echo '<br /><br /><a href="'.t3lib_div::linkThisScript(array('SET[submodule]'=>'tx_categories_importmodfunc2')).'">'.$LANG->getLL('backlink').'</a>';
			echo $this->pObj->getEndPageHTML();
		}
	}

	//http://www.esd.org.uk/standards/ipsv/ipsvhierarchy.xml

	function getData(){
		$xml = t3lib_div::getUrl($this->getVar('url'));
		return XML_unserialize($xml);
	}

	function import($data){
		
		static $parentid = 0;
		static $global_counter = 0;
		

		
		
		if(is_array($data['Item'])){
			
			$num_elements = count($data['Item']);
			$counter = 0;
			
			foreach($data['Item'] as $k => $v){
		
				if(!is_numeric($k)) continue;
				
				$global_counter++;
				
				//let's lookup if this term already exists
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_categories','orig_id="'.trim($data['Item'][$k.' attr']['Id']).'"');
				
				if($existing_category = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
					$insertid = $existing_category['uid'];
				} else {
				
					$insertArray = array();
					$insertArray['pid'] = tx_categories_div::getPid();
					$insertArray['title'] = trim($v['Name']);
					$insertArray['description'] = trim($v['ScopeNotes']);
					$insertArray['orig_id'] = trim($data['Item'][$k.' attr']['Id']);
					if($parentid > 0){
						$insertArray['parents'] = 1;
					}
					$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_categories',$insertArray);
					$insertid = $GLOBALS['TYPO3_DB']->sql_insert_id();
				}
				
				if($parentid > 0) {
					
					$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_categories_mm','uid_foreign='.$parentid.' AND uid_local='.$insertid.' AND localtable="tx_categories"');

					if($GLOBALS['TYPO3_DB']->sql_num_rows($res) < 1){
						$mminsert = array();
						$mminsert['uid_foreign'] = $parentid;
						$mminsert['uid_local'] = $insertid;
						$mminsert['localtable'] = 'tx_categories';						
						$GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_categories_mm',$mminsert);
					}
				}
				
				//if(isset($v['RelatedItem'])){
				//	$firstkey = key($v['RelatedItem']);
				//	$keyparts = t3lib_div::trimExplode(" ",$firstkey,1);
				//	$realkey = $keyparts[0];
				//	if(!is_numeric($realkey)){
				//		$tmp = array(
				//			'0' => $v['RelatedItem'],
				//			'0 attr' => $v['RelatedItem attr']
				//		);
				//		unset($v['RelatedItem']);
				//		unset($v['RelatedItem attr']);
				//		$v['RelatedItem'] = $tmp;
				//	}
				//
				//	foreach($v['RelatedItem'] as $rkey => $related){
				//		$this->relatedItemBank[$insertid][] = $v['RelatedItem'][$rkey.' attr']['Id'];
				//	}
				//}
				
				
				
				

				
				if(isset($v['Item'])){
					
					$firstkey = key($v['Item']);
					$keyparts = t3lib_div::trimExplode(" ",$firstkey,1);
					$realkey = $keyparts[0];
					if(!is_numeric($realkey)){
						$tmp = array(
							'0' => $v['Item'],
							'0 attr' => $v['Item attr']
						);
						unset($v['Item']);
						unset($v['Item attr']);
						$v['Item'] = $tmp;
					}
					
					$tmp = array( 'Item' => $v['Item'] );
					
					$oldparentid = $parentid;
					$parentid = $insertid;
					$this->import($tmp);
					$parentid = $oldparentid;
				}
				
				if($parentid == 0){
					$counter++;
					$percentDone = intval (($counter / $num_elements) * 100);
					
					$out = array();
					$out[] = '<script type="text/javascript">';
					$out[] = '	document.getElementById(\'progress-bar\').style.width = \''.$percentDone.'%\';';
					$out[] = '	document.getElementById(\'progress-bar\').style.display = \'block\';';
			
					if($percentDone < 100){
						$out[] = '	document.getElementById(\'transparent-bar\').style.width = \''.(100-$percentDone).'%\';';
					} else {
						$out[] = '	document.getElementById(\'transparent-bar\').style.display = \'none\'';
					}
					$out[] =  '	document.getElementById(\'progress-message\').innerHTML = \'Importing '.$global_counter.' elements\';';
					$out[] =  '</script>';
					
					echo implode("\n",$out);
					
					flush();
					sleep(1);					
				}
			}
		}
	}



	function getVar($variable){
		return $this->pObj->MOD_SETTINGS[$this->prefixId.'_'.$variable];		
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/###EXTENSION_KEY###/modfunc1/class.tx_###EXTENSION_KEY###_modfunc1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/###EXTENSION_KEY###/modfunc1/class.tx_###EXTENSION_KEY###_modfunc1.php']);
}

?>