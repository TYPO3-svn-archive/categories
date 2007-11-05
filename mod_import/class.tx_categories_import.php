<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007  <mads@brunn.dk>
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
 * Module 'Import' for the 'categories' extension.
 *
 * @author     Mads Brunn <mads@brunn.dk>
 * @package    TYPO3
 * @subpackage    tx_categories
 */

require_once(PATH_txcategories.'lib/xmlparser.php');
require_once(PATH_t3lib.'class.t3lib_scbase.php');
 
class  tx_categories_import extends t3lib_SCbase {
	var $pageinfo;
	var $doPrintContent = TRUE;	

	/**
	* Initializes the Module
	* @return    void
	*/
	function init()    {
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		
			// name might be set from outside
		if (!$this->MCONF['name']) {
			$this->MCONF = $GLOBALS['MCONF'];
		}
		$this->CMD = t3lib_div::_GP('CMD');
		$this->menuConfig();
		$this->handleExternalFunctionValue('submodule');
		
		/*
		if (t3lib_div::_GP('clear_all_cache'))    {
			$this->include_once[] = PATH_t3lib.'class.t3lib_tcemain.php';
		}
		*/
	}

	/**
	* Adds items to the ->MOD_MENU array. Used for the function menu selector.
	*
	* @return    void
	*/
	function menuConfig()    {
		global $LANG;
		$this->MOD_MENU = Array (
			'submodule' => Array (
				'' => ''
			)
		);
		$this->modTSconfig = t3lib_BEfunc::getModTSconfig($this->id,'mod.'.$this->MCONF['name']);
		$this->MOD_MENU['submodule'] = $this->mergeExternalItems($this->MCONF['name'],'submodule',$this->MOD_MENU['submodule']);
		$this->MOD_MENU['submodule'] = t3lib_BEfunc::unsetMenuItems($this->modTSconfig['properties'],$this->MOD_MENU['submodule'],'menu.submodule');
		$this->MOD_SETTINGS = t3lib_BEfunc::getModuleData($this->MOD_MENU, t3lib_div::_GP('SET'), $this->MCONF['name'], $this->modMenu_type, $this->modMenu_dontValidateList, $this->modMenu_setDefaultList);
		
		//parent::menuConfig();
	}

	/**
	* Main function of the module. Write the content to $this->content
	* If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	*
	* @return    [type]        ...
	*/
	function main()    {
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		
		
		// Draw the header.
		$this->doc = t3lib_div::makeInstance('bigDoc');
		$this->doc->backPath = $BACK_PATH;
		$this->doc->form='<form action="" method="POST">';
		
		// JavaScript
		$this->doc->JScode = '
			<script language="javascript" type="text/javascript">
			script_ended = 0;
			function jumpToUrl(URL)    {
				document.location = URL;
			}
			</script>
		';
		$this->doc->postCode='
			<script language="javascript" type="text/javascript">
			script_ended = 1;
			if (top.fsMod) top.fsMod.recentIds["web"] = 0;
			</script>
		';

		# It's very important that this called BEFORE you start 
		# adding something to $this->content
		# Otherwise some submodules may not work as expected
		$modulecontent = $this->moduleContent();	
		$this->content .= $this->getStartPageHTML();
		
		/*
		 * Content from submodules
		 * A submodule may have set the doPrintContent variable to FALSE
		 * In that case it's the job of the submodule to output all the HTML
		 */
		$this->content .= $modulecontent;
		$this->content .= $this->getEndPageHTML();
	

	}

	/**
	* Prints out the module HTML
	*
	* @return    void
	*/
	function printContent()    {
		if($this->doPrintContent){
			echo $this->content;
		}
	}


	
	/**
	* Generates the module content
	*
	* @return    void
	*/
	function moduleContent()    {
		
	
		global $BACK_PATH,$LANG;
		
		$content = '';
		
		//if(is_object($this->extObj)){
		if($this->MOD_SETTINGS['submodule'] != ''){
			if (is_callable(array($this->extObj, 'main'))){
				$content = $this->extObj->main();
			}
		} else {

			$out = array();	
			if(count($this->MOD_MENU['submodule'])){
				$out[] = $this->formatLocalizedStr($LANG->getLL('select_submodule'));				
			} else {
				$out[] = $this->formatLocalizedStr($LANG->getLL('no_importfunctions_installed'));
			}
			$out[] = t3lib_BEfunc::cshItem('_MOD_txcategoriesMain_txcategoriesImport', 'aboutimportmodule', $this->doc->backPath,'<hr/>|'.$LANG->getLL('about_import_module', 1));
			$content = $this->doc->section($LANG->getLL('select_import_function_header'),implode("\n",$out),FALSE,TRUE);

		}
		$content .= $this->doc->sectionEnd();			
		return $content;
		
	}
	
	
	function disableOutput(){
		$this->doPrintContent = FALSE;
	}
		
	function enableOutput(){
		$this->doPrintContent = TRUE;
	}
				
	
	function formatLocalizedStr($str){

		return nl2br(str_replace(
				array(
					'###ICON1###',	
					'###ICON2###',	
					'###ICON3###',						
				),
				array(
					$this->doc->icons(1),
					$this->doc->icons(2),
					$this->doc->icons(3),						
				),
				$str	
			));		
		
	}
	
	
	function getStartPageHTML(){
		global $LANG;
		
		$out = array();
		$out[] = $this->doc->startPage($LANG->getLL('title'));
		$out[] = $this->doc->header($LANG->getLL('title'));
		$out[] = $this->doc->spacer(5);
		if(count($this->MOD_MENU['submodule']) > 1) {
			$out[] =  $this->doc->section('',$this->doc->funcMenu('hest',$LANG->getLL('select_wizard').t3lib_BEfunc::getFuncMenu($this->id,'SET[submodule]',$this->MOD_SETTINGS['submodule'],$this->MOD_MENU['submodule'])));
		}
		$out[] = $this->doc->divider(5);
		
		return implode("\n",$out);
		
	}

	function getEndPageHTML(){
		global $LANG,$BE_USER;

		$out = array();		
		// ShortCut
		if ($BE_USER->mayMakeShortcut())    {
			$out[] = $this->doc->spacer(20).$this->doc->section('',$this->doc->makeShortcutIcon('id',implode(',',array_keys($this->MOD_MENU)),$this->MCONF['name']));
		}
		
		$out[] = $this->doc->spacer(10);
		$out[] = $this->doc->endPage();
		return implode("\n",$out);
	}

	function getProgressBarHTML(){
		return '
				<div style="width:100%; height:20px; border: 1px solid black;">
					<div id="progress-bar" style="float: left; width: 0%; height: 20px; background-color:green;display:none;">&nbsp;</div>
					<div id="transparent-bar" style="float: left; width: 100%; height: 20px; background-color:'.$this->doc->bgColor2.';">&nbsp;</div>
				</div>
				<br />
				<p id="progress-message">
					Initializing... Please wait...
				</p>
				<p id="progress-message2">

				</p>				
				
			';
	}	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/mod_import/class.tx_categories_import.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/mod_import/class.tx_categories_import.php']);
}


?>
