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

require_once(PATH_t3lib.'class.t3lib_extobjbase.php');
$LANG->includeLLFile('EXT:categories/mod_func_import/locallang.xml');


class tx_categories_modfuncimport extends t3lib_extobjbase {
	var $function_key = 'import';

	/**
	 * Initialize.
	 * Calls parent init function and then the handleExternalFunctionValue() function from the parent class
	 *
	 * @param	object		A reference to the parent (calling) object (which is probably an instance of an extension class to t3lib_SCbase)
	 * @param	array		The configuration set for this module - from global array TBE_MODULES_EXT
	 * @return	void
	 * @see t3lib_extobjbase::handleExternalFunctionValue(), t3lib_extobjbase::init()
	 */
	function init(&$pObj,$conf)	{
			// OK, handles ordinary init. This includes setting up the menu array with ->modMenu
		parent::init($pObj,$conf);

			// Making sure that any further external classes are added to the include_once array. Notice that inclusion happens twice in the main script because of this!!!
		$this->handleExternalFunctionValue();
	}

	/**
	 * Modifies parent objects internal MOD_MENU array, adding items this module needs.
	 *
	 * @return	array		Items merged with the parent objects.
	 * @see t3lib_extobjbase::init()
	 */
	function modMenu()	{
		global $LANG;

		$modMenuAdd = array(
			$this->function_key => array()
		);

		$modMenuAdd[$this->function_key] = $this->pObj->mergeExternalItems($this->pObj->MCONF['name'],$this->function_key,$modMenuAdd[$this->function_key]);
		$modMenuAdd[$this->function_key] = t3lib_BEfunc::unsetMenuItems($this->pObj->modTSconfig['properties'],$modMenuAdd[$this->function_key],'menu.'.$this->function_key);

		return $modMenuAdd;
	}

	/**
	 * Creation of the main content. Calling extObjContent() to trigger content generation from the sub-sub modules
	 *
	 * @return	string		The content
	 * @see t3lib_extobjbase::extObjContent()
	 */
	function main()	{
		global $SOBE,$LANG;

		$out = array();
		$menu= $LANG->getLL('select_import',1).': '.t3lib_BEfunc::getFuncMenu($this->pObj->id,'SET[import]',$this->pObj->MOD_SETTINGS['import'],$this->pObj->MOD_MENU['import']);
		$out[] = $this->pObj->doc->section('','<span class="nobr">'.$menu.'</span>');
		$out[] = $this->pObj->doc->spacer(5);
		$out[] = $this->extObjContent();
		$out[] = '<br />'.t3lib_BEfunc::cshItem('_MOD_txcategoriesMain_txcategoriesFunc', 'aboutimportmodule', $this->pObj->doc->backPath,'|'.$LANG->getLL('about_import_module', 1));

		return implode("\n",$out);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/mod_func_import/class.tx_categories_modfuncimport.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/mod_func_import/class.tx_categories_modfuncimport.php']);
}



?>