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
 * Contains the tx_categories_fe class with miscellaneous functions for use in the frontend 
 *
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 */
/**
 * Miscellaneous functions for use with categories in the frontend
 *
 * @author	Mads Brunn <mads@brunn.dk> 
 * @package 	TYPO3
 * @subpackage 	categories
 */

class tx_categories_fe{

	var $rootLine = array();
	var $id = NULL;
	var $path = '';

	function tx_categories_fe(){

		$this->setId();
		$this->setRootline();
		$this->setPath();
	
	}
	
	function setId(){
		if($id = t3lib_div::_GP('cId')){
			$this->id = $id;
		}
	}
	
	function setRootline(){
	
	
	}

	function setPath(){
		
	}
}


?>