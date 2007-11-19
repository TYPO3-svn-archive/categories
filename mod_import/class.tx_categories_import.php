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


require_once(PATH_t3lib.'class.t3lib_scbase.php');
 
class  tx_categories_import extends t3lib_SCbase {
	var $pageinfo;
	var $doPrintContent = TRUE;	

	/**
	* Initializes the Module
	* @return    void
	*/
	function init()    {
		if (!$this->MCONF['name']) {
			$this->MCONF = $GLOBALS['MCONF'];
		}
		$this->id = intval(t3lib_div::_GP('id'));
		$this->CMD = t3lib_div::_GP('CMD');
		$this->menuConfig();
		$this->handleExternalFunctionValue();
	}


	/**
	 * Adds items to the ->MOD_MENU array. Used for the function menu selector.
	 *
	 * @return    void
	 */
	function menuConfig()    {
		global $LANG;
		$this->MOD_MENU = Array (
			'function' => Array (
				'' => '',
			)
		);
		parent::menuConfig();
	}


	
	/**
	* Main function of the module. Write the content to $this->content
	* If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	*
	* @return    [type]        ...
	*/
	function main()    {
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		
		$this->catinfo = tx_categories_div::getCategoryInfo($this->id);
		
		// Draw the header.
		$this->doc = t3lib_div::makeInstance('mediumDoc');
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
			</script>
		';
		

		# It's very important that this called BEFORE you start 
		# adding something to $this->content
		# Otherwise some submodules may not work as expected
		# $this->disableOutput() may be called from a submodule
		# It is then the responsibility of the submodule to
		# echo out all the content of the module
		
		$this->content .= $this->doc->sectionBegin();		
		$this->extObjContent();
		$this->content .= $this->doc->sectionEnd();		
		
		
	}

	/**
	* Prints out the module HTML
	*
	* @return    void
	*/
	function printContent()    {
		if($this->doPrintContent){

			$out  = $this->getStartPageHTML();
			$out .= $this->content;
			$out .= $this->getEndPageHTML();
			echo $out;
			
		}
	}
	
	
	function disableOutput(){
		$this->doPrintContent = FALSE;
	}
		
	function enableOutput(){
		$this->doPrintContent = TRUE;
	}
				
	
	function getStartPageHTML(){
		global $LANG;
		
		$out = array();
		$out[] = $this->doc->startPage($LANG->getLL('title'));
		$out[] = $this->doc->header($LANG->getLL('title'));

		
		$headerSection = $this->doc->getHeader('tx_categories',$this->catinfo,$this->catinfo['_thePath'],1).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_pre($this->catinfo['_thePath'],50);

		
		$out[] = $this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
		$out[] = $this->doc->spacer(5);

		if(empty($this->MOD_MENU['function'])){
			$out[] = $this->doc->icons(1);
			$out[] = nl2br($LANG->getLL('no_submodules_available'));
			
			if(tx_categories_befunc::userHasAccessToModule('txcategoriesMain_txcategoriesList')){
				$out[] = '<br /><br /><img'.t3lib_iconWorks::skinImg($this->doc->backPath, PATH_txcategories_rel.'gfx/list.gif', '').' style="text-align:center; vertical-align: middle; border:0;" /> <strong><a href="#" onclick="top.goToModule(\'txcategoriesMain_txcategoriesList\');this.blur();return false;">'.$LANG->getLL('go_to_list_module').'</a></strong>';
			}
		}
		$out[] = $this->doc->sectionEnd();
		
		return implode("\n",$out);
		
	}

	function getEndPageHTML(){
		global $LANG,$BE_USER;

		$out = array();	

		$out[] = '<br /><br />';
		$out[] = $this->doc->divider(1);
		$out[] = t3lib_BEfunc::cshItem('_MOD_txcategoriesMain_txcategoriesImport', 'aboutimportmodule', $this->doc->backPath,'|'.$LANG->getLL('about_import_module', 1));
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
