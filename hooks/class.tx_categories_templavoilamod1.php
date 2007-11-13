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
 * Contains hook functions for use with the templavoila page module
 *
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 */
/**
 * @author	Mads Brunn <mads@brunn.dk>
 * @package 	TYPO3
 * @subpackage 	categories
 */
 
class tx_categories_templavoilamod1{

	/**
	 * This function is called by a hook in the templavoila page module. 
	 * Used to add a link back to the Categories>List module if the page module
	 * is viewed from one of the Category modules. This is e.g. the case if a 
	 * pages has been created with the New element wizard in Categories>List
	 *
	 * @param	array		$params: contains parameters passed to the funtion from the calling object
	 * @param	pointer		$pObj: a reference back to the calling object (the page module)
	 * @return	string		html-string
	 */
	function renderTopToolbar($params,$pObj){

		global $BACK_PATH,$LANG;

		$LANG->includeLLFile('EXT:categories/locallang.xml');
		
		$content  = '';
		$content .= '<br /><img'.t3lib_iconWorks::skinImg($pObj->doc->backPath, PATH_txcategories_rel.'gfx/list.gif', '').' style="text-align:center; vertical-align: middle; border:0;" /> <strong><a href="#" onclick="top.goToModule(\'txcategoriesMain_txcategoriesList\');this.blur();return false;">'.$LANG->getLL('go_to_list_module').'</a></strong>';	
		
		$out = '
		
		<script src="'.$BACK_PATH.PATH_txcategories_rel.'res/jquery.js" type="text/javascript"></script>
		<script type="text/javascript">
		jQuery(function($) {


			if (
				top && 
				top.content && 
				top.content.nav_frame && 
				top.content.nav_frame.document && 
				top.content.nav_frame.document.body && 
				top.content.nav_frame.navFrameId && 
				top.content.nav_frame.navFrameId == "txcategoriesMain"
			)	{	
				
				// use this to add some content to the templavoila page module
				// if the navframe is the category tree
				
				$("body").prepend("'.t3lib_div::slashJS($content,0,'"').'");				
			}
		});
		</script>
		
		';
		
		
		return $out;

	
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/hooks/class.tx_categories_templavoilamod1.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/hooks/class.tx_categories_templavoilamod1.php']);
}

?>