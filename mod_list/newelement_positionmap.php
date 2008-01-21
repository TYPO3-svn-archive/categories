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
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
* 
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * New database item in category
 *
 * This script lets users choose a new database element to create in a category
 * Includes a wizard mode for visually pointing out the position of new pages
 *
 *
 * @author	Mads Brunn <mads@typoconsult.dk>
 */


unset($MCONF);
include('conf.php');
require($BACK_PATH.'init.php');
require($BACK_PATH.'template.php');
require_once (PATH_txcategories.'mod_list/class.tx_categories_modulepositionmap.php');

// Make instance:
$SOBE = t3lib_div::makeInstance('tx_categories_modulepositionmap');
$SOBE->init();
$SOBE->initPage();
$SOBE->main();
$SOBE->printContent();
?>