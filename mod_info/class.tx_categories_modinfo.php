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




class  tx_categories_modinfo extends t3lib_SCbase {

	 
	 /**
	 * Initializes the Module
	 * @return    void
	 */
	 function init()    {
			// name might be set from outside
		if (!$this->MCONF['name']) {
			$this->MCONF = $GLOBALS['MCONF'];
		}
		$this->id = intval(t3lib_div::_GP('id'));
		$this->CMD = t3lib_div::_GP('CMD');
		$this->menuConfig();
		$this->handleExternalFunctionValue();
	 }
	 

	 	 
	 
	 
	 /**
	 * Main function of the module. Write the content to $this->content
	 * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
	 *
	 * @return    [type]        ...
	 */
	 function main()    {
		global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
		
		$GLOBALS['TYPO3_DB']->debugOutput = TRUE;
		$this->catinfo = tx_categories_div::getCategoryInfo($this->id);

		
		if($this->id) {
		
			// Draw the header.
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;
			$this->doc->form='<form action="'.t3lib_div::linkThisScript().'" method="post">';
			
			
				// Add JavaScript functions to the page:
			$this->doc->JScode=$this->doc->wrapScriptTags('
	
				function jumpToUrl(URL)	{	//
					window.location.href = URL;
					return false;
				}
				if (top.fsMod) top.fsMod.recentIds["txcategoriesMain"] = '.intval($this->id).';
	
			');
	
				// Setting up the context sensitive menu:
			$CMparts=$this->doc->getContextMenuCode();
			$this->doc->bodyTagAdditions = $CMparts[1];
			$this->doc->JScode .= $CMparts[0];
			$this->doc->postCode .= $CMparts[2];
	
			
			$headerSection = $this->doc->getHeader('tx_categories',$this->catinfo,$this->catinfo['_thePath'],1).'<br />'.$LANG->sL('LLL:EXT:lang/locallang_core.xml:labels.path').': '.t3lib_div::fixed_lgd_pre($this->catinfo['_thePath'],50);
	
			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->section('',$this->doc->funcMenu($headerSection,t3lib_BEfunc::getFuncMenu($this->id,'SET[function]',$this->MOD_SETTINGS['function'],$this->MOD_MENU['function'])));
			$this->content.=$this->doc->divider(5);

			if(empty($this->MOD_MENU['function'])){
				$this->content .= $this->doc->icons(1);
				$this->content .= nl2br($LANG->getLL('no_submodules_available'));
				if(tx_categories_befunc::userHasAccessToModule('txcategoriesMain_txcategoriesList')){
					$this->content .= '<br /><br /><img'.t3lib_iconWorks::skinImg($this->doc->backPath, PATH_txcategories_rel.'gfx/list.gif', '').' style="text-align:center; vertical-align: middle; border:0;" /> <strong><a href="#" onclick="top.goToModule(\'txcategoriesMain_txcategoriesList\');this.blur();return false;">'.$LANG->getLL('go_to_list_module').'</a></strong>';
				}
			}

			$this->extObjContent();
			
			$this->content .= '<br /><br />';
			$this->content .= t3lib_BEfunc::cshItem('_MOD_txcategoriesMain_txcategoriesInfo', 'aboutinfomodule', $this->doc->backPath,'|'.$LANG->getLL('about_info_module', 1));
			
			
			
		} else {

			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->backPath = $BACK_PATH;

			$this->content.=$this->doc->startPage($LANG->getLL('title'));
			$this->content.=$this->doc->header($LANG->getLL('title'));
			$this->content.=$this->doc->section('','<br />'.$LANG->getLL('click_on_a_category_in_the_tree'),0,1);
			$this->content.=$this->doc->spacer(5);
			$this->content.=$this->doc->spacer(10);
		}
	 }

	 
	 /**
	 * Prints out the module HTML
	 *
	 * @return    void
	 */
	function printContent()    {		 
		$this->content.=$this->doc->endPage();
		echo $this->content;
	}
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/mod_info/class.tx_categories_modinfo.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/mod_info/class.tx_categories_modinfo.php']);
}


?>
