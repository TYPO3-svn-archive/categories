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
 * Contains the tx_categories_tce class with functions for rendering special
 * tce-fields 
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

class tx_categories_tce{

	/**
	 * Generates a tce-field that displays categories in a hierarchial tree
	 * 
	 * @param	array	$PA: configuration array. Contains the index 'pObj' which is a reference to the parent tceform object
	 * @param	object	$fobj: The tceform object
	 * @return	string HTML for the field
	 */
	function getSingleField_typeSelectCategoryTree(&$PA,$fobj){
		global $TCA,$LANG,$TYPO3_CONF_VARS;	
		
		$table = $PA['table'];
		$field = $PA['field'];
		$row = $PA['row'];
			// Field configuration from TCA:
		$config = $PA['fieldConf']['config'];
		
			// "Extra" configuration; Returns configuration for the field based on settings found in the "types" fieldlist. See http://typo3.org/documentation/document-library/doc_core_api/Wizards_Configuratio/.
		$specConf = $PA['pObj']->getSpecConfFromString($PA['extra'], $PA['fieldConf']['defaultExtras']);
		
			// Getting the selector box items from the system
		$selItems = $PA['pObj']->addSelectOptionsToItemArray($PA['pObj']->initItemArray($PA['fieldConf']),$PA['fieldConf'],$PA['pObj']->setTSconfig($table,$row),$field);
		$selItems = $PA['pObj']->addItems($selItems,$PA['fieldTSConfig']['addItems.']);
		if ($config['itemsProcFunc']) $selItems = $PA['pObj']->procItems($selItems,$PA['fieldTSConfig']['itemsProcFunc.'],$config,$table,$row,$field);
		
		$nMV_label = isset($PA['fieldTSConfig']['noMatchingValue_label']) ? $this->sL($PA['fieldTSConfig']['noMatchingValue_label']) : '[ '.$PA['pObj']->getLL('l_noMatchingValue').' ]';		


		$disabled = '';
		if($PA['pObj']->renderReadonly || $config['readOnly'])  {
			$disabled = ' disabled="disabled"';
		}

			// Setting this hidden field (as a flag that JavaScript can read out)
		if (!$disabled) {
			$item.= '<input type="hidden" name="'.$PA['itemFormElName'].'_mul" value="'.($config['multiple']?1:0).'" />';
		}

			// Set max and min items:
		$maxitems = t3lib_div::intInRange($config['maxitems'],0);
		if (!$maxitems)	$maxitems=100000;
		$minitems = t3lib_div::intInRange($config['minitems'],0);

			// Register the required number of elements:
		$PA['pObj']->requiredElements[$PA['itemFormElName']] = array($minitems,$maxitems,'imgName'=>$table.'_'.$row['uid'].'_'.$field);

			// Get "removeItems":
		$removeItems = t3lib_div::trimExplode(',',$PA['fieldTSConfig']['removeItems'],1);

			// Perform modification of the selected items array:
		$itemArray = t3lib_div::trimExplode(',',$PA['itemFormElValue'],1);
		foreach($itemArray as $tk => $tv) {
			$tvP = explode('|',$tv,2);
			$evalValue = rawurldecode($tvP[0]);
			$isRemoved = in_array($evalValue,$removeItems)  || ($config['form_type']=='select' && $config['authMode'] && !$GLOBALS['BE_USER']->checkAuthMode($table,$field,$evalValue,$config['authMode']));
			if ($isRemoved && !$PA['fieldTSConfig']['disableNoMatchingValueElement'] && !$config['disableNoMatchingValueElement'])	{
				$tvP[1] = rawurlencode(@sprintf($nMV_label, $evalValue));
			} elseif (isset($PA['fieldTSConfig']['altLabels.'][$evalValue])) {
				$tvP[1] = rawurlencode($this->sL($PA['fieldTSConfig']['altLabels.'][$evalValue]));
			}
			$itemArray[$tk] = implode('|',$tvP);
		}
		
		$itemsToSelect = '';

		if(!$disabled) {

				// Building the Iframe with the tree-script inside
			$selector_itemListStyle = isset($config['itemListStyle']) ? ' style="'.htmlspecialchars($config['itemListStyle']).'"' : ' style="'.$this->defaultMultipleSelectorStyle.'"';
			$size = intval($config['size']);
			$size = $config['autoSizeMax'] ? t3lib_div::intInRange(count($itemArray)+1,t3lib_div::intInRange($size,1),$config['autoSizeMax']) : $size;
			$frameid= str_replace(array('][','[',']'),array('_','_',''),$PA['itemFormElName']).'_treeframe';
			
			$params = array();
			$params['itemFormElName'] = $PA['itemFormElName']; 
			$params['treeName'] = $config['treeName'];
			
			if(trim($config['rootIds'])){
				$params['rootIds'] = trim($config['rootIds']);
			}
			$params = t3lib_div::implodeArrayForUrl('',$params,'',1);
			$params = substr_replace($params,'?',0,1); 
			
			$url = PATH_txcategories_rel.'mod_browsecat/index.php'.$params;
			
			$itemsToSelect = '<iframe src="'.$url.'" name="'.$frameid.'"'.$selector_itemListStyle.'></iframe>';
			
		}

			// Pass to "dbFileIcons" function:
		$params = array(
			'size' => $size,
			'autoSizeMax' => t3lib_div::intInRange($config['autoSizeMax'],0),
			'style' => isset($config['selectedListStyle']) ? ' style="'.htmlspecialchars($config['selectedListStyle']).'"' : ' style="'.$PA['pObj']->defaultMultipleSelectorStyle.'"',
			'dontShowMoveIcons' => ($maxitems<=1),
			'maxitems' => $maxitems,
			'info' => '',
			'headers' => array(
				'selector' => $PA['pObj']->getLL('l_selected').':<br />',
				'items' => $PA['pObj']->getLL('l_items').':<br />'
			),
			'noBrowser' => 1,
			'thumbnails' => $itemsToSelect,
			'readOnly' => $disabled
		);
		$item.= $PA['pObj']->dbFileIcons($PA['itemFormElName'],'','',$itemArray,'',$params,$PA['onFocus']);

		
		if (!$disabled) {
			$altItem = '<input type="hidden" name="'.$PA['itemFormElName'].'" value="'.htmlspecialchars($PA['itemFormElValue']).'" />';
			$item = $PA['pObj']->renderWizards(array($item,$altItem),$config['wizards'],$table,$row,$field,$PA,$PA['itemFormElName'],$specConf);
		}
		
		if($config['enableWarning']){

			//debug($config);
			//we are wrapping the whole field in a div with an id to access it from js
			//$id = t3lib_div::shortMD5('MP:'.$table.':'.$row['uid']);
			//$item = '<div id="'.$id.'">'.$item.'</div>';			
			
			
			$PA['pObj']->additionalCode_pre['tx_categories'] = '<script type="text/javascript" src="'.PATH_txcategories_rel.'res/jquery.js"></script>';


			$js = array();
			if($TCA[$table]['ctrl']['dividers2tabs']){
				//$dyntabid = 'DTM-'.t3lib_div::shortMD5('TCEforms:'.$table.':'.$row['uid']);
				//$dyntabswitchjs = array();
				//$dyntabswitchjs[] = 'var dyntabid = $j("input[@name=\''.$PA['itemFormElName'].'_list\']").parents(".c-tablayer").attr("id");';

				//$dyntabswitchjs[] = 'var dyntabid = jQuery("input[@name=\''.$PA['itemFormElName'].'_list\']").html();';
				//$dyntabswitchjs[] = 'alert(dyntabid)';
				//$dyntabswitchjs[] = 'var id_parts = dyntabid.split("-");';
				//$dyntabswitchjs[] = 'DTM_activate(id_parts[0]+"-"+id_parts[1],id_parts[2],0);';
				//$dyntabswitchjs = implode("\n",$dyntabswitchjs);
			}

			$js[] = '
			function tx_categories_checkSubmit(){
				var $j = jQuery.noConflict();
				formObj = setFormValue_getFObj("'.$PA['itemFormElName'].'");
			
				fObj = formObj["'.$PA['itemFormElName'].'_list"];			
				if(fObj){
						//alert(fObj.length);
						if(fObj.length < 1){
						'.$dyntabswitchjs.'
						if(!confirm('.$LANG->JScharCode($LANG->sL($config['warnings']['noCategoriesSelected'])).')){
							return false;
						}
					}
				}
				return true;
			}
			';	

			$PA['pObj']->additionalCode_pre['tx_categories_2'] = '<script type="text/javascript">
			
				'.implode("\n",$js).'
				
			</script>';
			
			//$PA['pObj']->additionalJS_submit[] = implode("\n",$js);
			//$PA['pObj']->additionalJS_submit[] = 'tx_categories_checkSubmit();OK=0';
			$PA['pObj']->additionalJS_submit[] = 'return false;';			
		}
		
		return $item;
	}


	/**
	 * Generates a tce-field that displays a search function with autosuggest functionality
	 * 
	 * @param	array	$PA: configuration array. Contains the index 'pObj' which is a reference to the parent tceform object
	 * @param	object	$fobj: The tceform object
	 * @return	string HTML for the field
	 */
	function getSingleField_typeFreeVocabulary(&$PA,$fobj){
		global $TCA,$LANG,$TYPO3_CONF_VARS;	
		
		
		$LANG->includeLLFile('EXT:categories/locallang.xml');
		
		
		$table = $PA['table'];
		$field = $PA['field'];
		$row = $PA['row'];
			// Field configuration from TCA:
		$config = $PA['fieldConf']['config'];
		
			// "Extra" configuration; Returns configuration for the field based on settings found in the "types" fieldlist. See http://typo3.org/documentation/document-library/doc_core_api/Wizards_Configuratio/.
		$specConf = $PA['pObj']->getSpecConfFromString($PA['extra'], $PA['fieldConf']['defaultExtras']);
		
			// Getting the selector box items from the system
		$selItems = $PA['pObj']->addSelectOptionsToItemArray($PA['pObj']->initItemArray($PA['fieldConf']),$PA['fieldConf'],$PA['pObj']->setTSconfig($table,$row),$field);
		$selItems = $PA['pObj']->addItems($selItems,$PA['fieldTSConfig']['addItems.']);
		if ($config['itemsProcFunc']) $selItems = $PA['pObj']->procItems($selItems,$PA['fieldTSConfig']['itemsProcFunc.'],$config,$table,$row,$field);
		
		$nMV_label = isset($PA['fieldTSConfig']['noMatchingValue_label']) ? $this->sL($PA['fieldTSConfig']['noMatchingValue_label']) : '[ '.$PA['pObj']->getLL('l_noMatchingValue').' ]';		


		$disabled = '';
		if($PA['pObj']->renderReadonly || $config['readOnly'])  {
			$disabled = ' disabled="disabled"';
		}

			// Setting this hidden field (as a flag that JavaScript can read out)
		if (!$disabled) {
			$item.= '<input type="hidden" name="'.$PA['itemFormElName'].'_mul" value="'.($config['multiple']?1:0).'" />';
		}

			// Set max and min items:
		$maxitems = t3lib_div::intInRange($config['maxitems'],0);
		if (!$maxitems)	$maxitems=100000;
		$minitems = t3lib_div::intInRange($config['minitems'],0);

			// Register the required number of elements:
		$PA['pObj']->requiredElements[$PA['itemFormElName']] = array($minitems,$maxitems,'imgName'=>$table.'_'.$row['uid'].'_'.$field);

			// Get "removeItems":
		$removeItems = t3lib_div::trimExplode(',',$PA['fieldTSConfig']['removeItems'],1);

			// Perform modification of the selected items array:
		$itemArray = t3lib_div::trimExplode(',',$PA['itemFormElValue'],1);
		foreach($itemArray as $tk => $tv) {
			$tvP = explode('|',$tv,2);
			$evalValue = rawurldecode($tvP[0]);
			$isRemoved = in_array($evalValue,$removeItems)  || ($config['form_type']=='select' && $config['authMode'] && !$GLOBALS['BE_USER']->checkAuthMode($table,$field,$evalValue,$config['authMode']));
			if ($isRemoved && !$PA['fieldTSConfig']['disableNoMatchingValueElement'] && !$config['disableNoMatchingValueElement'])	{
				$tvP[1] = rawurlencode(@sprintf($nMV_label, $evalValue));
			} elseif (isset($PA['fieldTSConfig']['altLabels.'][$evalValue])) {
				$tvP[1] = rawurlencode($this->sL($PA['fieldTSConfig']['altLabels.'][$evalValue]));
			}
			$itemArray[$tk] = implode('|',$tvP);
		}
		$itemsToSelect = '';

		if(!$disabled) {
			$PA['pObj']->additionalCode_pre['tx_categories'] = '<script type="text/javascript" src="'.PATH_txcategories_rel.'res/jquery.js"></script>';
			$PA['pObj']->additionalCode_pre['tx_categories_filtercombo'] = '<script type="text/javascript" src="'.PATH_txcategories_rel.'res/jquery.filtercombo.js"></script>';
			$PA['pObj']->additionalCode_pre['tx_categories_freevoc'] = '
<script type="text/javascript">

var $j = jQuery.noConflict();
$j(function($) {
	$j("input[@name=\''.$PA['itemFormElName'].'_suggest\']").filterCombo("http://" + top.location.host + "/'.PATH_txcategories_siterel.'mod_freevoc/index.php","select[@name=\''.$PA['itemFormElName'].'_sel\']", {cacheLength:100});
})
</script>';			
			

			
$itemsToSelect .= '<input name="'.$PA['itemFormElName'].'_suggest" style="width:194px;margin-left:1px;background-repeat:no-repeat;background-position:center right;)" autocomplete="off" /><br />';
			
			$styleAttrValue = '';

				// Put together the selector box:
			$selector_itemListStyle = isset($config['itemListStyle']) ? ' style="'.htmlspecialchars($config['itemListStyle']).'"' : ' style="'.$PA['pObj']->defaultMultipleSelectorStyle.'"';
			$size = intval($config['size']);
			$size = $config['autoSizeMax'] ? t3lib_div::intInRange(count($itemArray)+1,t3lib_div::intInRange($size,1),$config['autoSizeMax']) : $size;
			if ($config['exclusiveKeys'])	{
				$sOnChange = 'setFormValueFromBrowseWin(\''.$PA['itemFormElName'].'\',this.options[this.selectedIndex].value,this.options[this.selectedIndex].text,\''.$config['exclusiveKeys'].'\'); ';
			} else {
				$sOnChange = 'setFormValueFromBrowseWin(\''.$PA['itemFormElName'].'\',this.options[this.selectedIndex].value,this.options[this.selectedIndex].text); ';
			}
			$sOnChange .= implode('',$PA['fieldChangeFunc']);
			$itemsToSelect .= '
				<select name="'.$PA['itemFormElName'].'_sel"'.
							$PA['pObj']->insertDefStyle('select').
							($size ? ' size="'.$size.'"' : '').
							' onchange="'.htmlspecialchars($sOnChange).'"'.
							$PA['onFocus'].
							$selector_itemListStyle.'>
					
				</select>';
		}

			// Pass to "dbFileIcons" function:
		$params = array(
			'size' => $size,
			'autoSizeMax' => t3lib_div::intInRange($config['autoSizeMax'],0),
			'style' => isset($config['selectedListStyle']) ? ' style="'.htmlspecialchars($config['selectedListStyle']).'"' : ' style="'.$PA['pObj']->defaultMultipleSelectorStyle.'"',
			'dontShowMoveIcons' => ($maxitems<=1),
			'maxitems' => $maxitems,
			'info' => '',
			'headers' => array(
				'selector' => $PA['pObj']->getLL('l_selected').':<br />',
				//'items' => $PA['pObj']->getLL('l_items').':<br />'
				'items' => $LANG->getLL('lookup_category_items_label').':<br />'				
			),
			'noBrowser' => 1,
			'thumbnails' => $itemsToSelect,
			'readOnly' => $disabled
		);
		$item.= $PA['pObj']->dbFileIcons($PA['itemFormElName'],'','',$itemArray,'',$params,$PA['onFocus']);

		
		if (!$disabled) {
			$altItem = '<input type="hidden" name="'.$PA['itemFormElName'].'" value="'.htmlspecialchars($PA['itemFormElValue']).'" />';
			$item = $PA['pObj']->renderWizards(array($item,$altItem),$config['wizards'],$table,$row,$field,$PA,$PA['itemFormElName'],$specConf);
		}
		
		if($config['enableWarning']){


			
			//we are wrapping the whole field in a div with an id to access it from js
			$id = t3lib_div::shortMD5('MP:'.$table.':'.$row['uid']);
			$item = '<div id="'.$id.'">'.$item.'</div>';			
			
			
			$PA['pObj']->additionalCode_pre['tx_categories'] = '<script type="text/javascript" src="'.PATH_txcategories_rel.'res/jquery.js"></script>';
			
			$js = array();
			if($TCA[$table]['ctrl']['dividers2tabs']){
				$dyntabid = 'DTM-'.t3lib_div::shortMD5('TCEforms:'.$table.':'.$row['uid']);
				$dyntabswitchjs = array();
				$dyntabswitchjs[] = 'var dyntabid = $("#'.$id.'").parents(".c-tablayer").attr("id");';
				$dyntabswitchjs[] = 'var id_parts = dyntabid.split("-");';
				$dyntabswitchjs[] = 'DTM_activate(id_parts[0]+"-"+id_parts[1],id_parts[2],0);';
				$dyntabswitchjs = implode("\n",$dyntabswitchjs);
			}

			
			
			$js[] = '
			formObj = setFormValue_getFObj(\''.$PA['itemFormElName'].'\');
			fObj = formObj[\''.$PA['itemFormElName'].'_list\'];			
			if(fObj){

				//alert(fObj.length);
				if(fObj.length < 1){
					
					'.$dyntabswitchjs.'
					
					if(!confirm('.$LANG->JScharCode($LANG->sL($config['warnings']['noCategoriesSelected'])).')){
						return false;			
					}
				}
			}
			';			
			$PA['pObj']->additionalJS_submit[] = implode("\n",$js);
		}
		
		return $item;
		
	}
	
	
	function jsSelector($id){
		
		return str_replace(
				array(
					'[',
					']'
				),
				array(
					'\\\[',
					'\\\]'
				),
				
				$id
			);
	}
	
}
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/lib/class.tx_categories_tce.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/lib/class.tx_categories_tce.php']);
}
?>