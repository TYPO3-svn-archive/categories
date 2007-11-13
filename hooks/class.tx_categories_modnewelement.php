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
 * Contains hook functions for use with the New element wizard in 
 * Categories>List module
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

require_once(PATH_txcategories.'lib/class.tx_categories_befunc.php');

class tx_categories_modnewelement{

	/**
	 * Hook used to redirect to templavoila's page module if we have created a new page
	 */
	function newElementPostInit(&$pObj){
			
		if($pObj->returnEditConf){

			$editconf = unserialize($pObj->returnEditConf);
			if(is_array($editconf)){
				if(isset($editconf['pages'])){
					
					$command = current($editconf['pages']);
					$id = key($editconf['pages']);

					if($command == 'edit'){ // a new page has been created
						
						//if templavoila is loaded we redirect to templavoila page module
						if(
							t3lib_extMgm::isLoaded('templavoila')
							&& tx_categories_befunc::userHasAccessToModule('web_txtemplavoilaM1')
						){
						
							$retUrl = t3lib_extMgm::siteRelPath('templavoila').'mod1/index.php?id='.$id;
							
						} elseif(
							t3lib_extMgm::isLoaded('cms')
							&& tx_categories_befunc::userHasAccessToModule('web_layout')
						){
							//falling back to the traditional pagemodule
							$retUrl = t3lib_extMgm::siteRelPath('cms').'layout/db_layout.php?id='.$id;
							
						}
						
						if(trim($retUrl)){

							$absUrl = t3lib_div::getIndpEnv('TYPO3_SITE_URL').$retUrl;
							
							header('Location:' . t3lib_div::locationHeaderUrl($absUrl));
							exit;
						}
					}
					
				}
			}
		}
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/hooks/class.tx_categories_modnewelement.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/hooks/class.tx_categories_modnewelement.php']);
}

?>
