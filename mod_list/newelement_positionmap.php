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
 * New database item menu
 *
 * This script lets users choose a new database element to create.
 * Includes a wizard mode for visually pointing out the position of new pages
 *
 * $Id: db_new.php 1777 2006-11-02 11:26:40Z mundaun $
 * Revised for TYPO3 3.6 November/2003 by Kasper Skaarhoj
 * XHTML compliant
 *
 * @author	Mads Brunn <mads@typoconsult.dk>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 */



unset($MCONF);
include('conf.php');
require($BACK_PATH.'init.php');
require($BACK_PATH.'template.php');
$LANG->includeLLFile('EXT:lang/locallang_misc.xml');
$LANG->includeLLFile('EXT:categories/mod_list/locallang.xml');
require_once(PATH_t3lib.'class.t3lib_scbase.php');
require_once(PATH_txcategories.'lib/class.tx_categories_positionmap.php');




/**
 * Script class for 'tx_categories_modulepositionmap'
 *
 * @author	Mads Brunn <mads@typoconsult.dk>
 * @package TYPO3
 * @subpackage categories
 */
class tx_categories_modulepositionmap extends t3lib_SCbase{
	var $catinfo;
	var $R_URI;

		// Internal, static: GPvar
	var $id;			// see init()
	var $returnUrl;			// Return url.

	var $doc;			// see init()
	var $content;			// Accumulated HTML output


	/**
	 * Constructor function for the class
	 *
	 * @return	void
	 */
	function init()	{
		global $BE_USER,$LANG,$BACK_PATH,$TYPO3_CONF_VARS,$CLIENT;
		
			// name might be set from outside
		if (!$this->MCONF['name']) {
			$this->MCONF = $GLOBALS['MCONF'];
		}

		// Setting GPvars:
		$this->id = intval(t3lib_div::_GP('id'));	// The category id
		$this->ajax = t3lib_div::_GP('ajax');
		$this->backPath = $BACK_PATH;
		$this->returnUrl = t3lib_div::_GP('returnUrl');
		$this->R_URI=$this->returnUrl;
		$this->catinfo = tx_categories_div::getCategoryInfo($this->id);
		
		
		debug($this->returnUrl);
		
		$this->positionmap = t3lib_div::makeInstance('tx_categories_positionmap');
		$this->positionmap->init();
		$this->positionmap->treeName = 'txcategoriespositionmap';
		$this->positionmap->thisScript = 'newelement_positionmap.php';
		$this->positionmap->createInTable = t3lib_div::_GP('table');
		$this->positionmap->categoryId = $this->id;
		$this->positionmap->returnUrl = $this->returnUrl;
		
		
		if (!$this->ajax) {
				// Create template object:
			$this->doc = t3lib_div::makeInstance('mediumDoc');
			$this->doc->docType = 'xhtml_trans';
			$this->doc->styleSheetFile2 = PATH_txcategories_rel.'res/stylesheet.css';
			
			$this->doc->backPath = $BACK_PATH;
				// Adding javascript code for AJAX (prototype), drag&drop and the pagetree
			$this->doc->JScode  = '
			<script type="text/javascript" src="'.$this->backPath.'contrib/prototype/prototype.js"></script>
			<script type="text/javascript" src="'.$this->backPath.'tree.js"></script>'."\n";

			$this->doc->JScode .= $this->doc->wrapScriptTags('
			// setting prefs for pagetree and drag & drop
			Tree.thisScript    = "'.$this->positionmap->thisScript.'";
			');

		}
		
	}

	/**
	 * Main processing, creating the list of new record tables to select from
	 *
	 * @return	void
	 */
	function main()	{
		global $BE_USER,$LANG,$TYPO3_CONF_VARS,$BACK_PATH,$CLIENT;

			// Produce browse-tree:
		$tree = $this->positionmap->getBrowsableTree();

			// Output only the tree if this is an AJAX call:
		if ($this->ajax) {
			$this->content = $LANG->csConvObj->utf8_encode($tree, $LANG->charSet);
			return;
		}
			// Start page:
		$this->content = $this->doc->startPage('newrecordtitle');
		$this->content .= $this->doc->header($LANG->getLL('newrecordincategory'));
		//debug($this->positionmap);
		$this->content .= '<p><strong>'.$LANG->getLL('insertnewrecordin').'</strong></p><br />';

			// Outputting page tree:
		$this->content.= $tree;
		
			// Create go-back link.
		if ($this->R_URI)	{
			$this->content .= '<br />
				<a href="'.htmlspecialchars($this->R_URI).'" class="typo3-goBack">'.
				'<img'.t3lib_iconWorks::skinImg($this->doc->backPath,'gfx/goback.gif','width="14" height="14"').' alt="" />'.
				$LANG->getLL('goBack',1).
				'</a>';
		}		
		
	}



	/**
	 * Outputting the accumulated content to screen
	 *
	 * @return	void
	 */
	function printContent()	{
			// If we handle an AJAX call, send headers:
		if ($this->ajax) {
			
			header('X-JSON: ('.($this->positionmap->ajaxStatus?'true':'false').')');
			header('Content-type: text/html; charset=utf-8');
			// If it's the regular call to fully output the tree:
		} else {
			$this->content.= $this->doc->endPage();
			$this->content = $this->doc->insertStylesAndJS($this->content);
		}
		echo $this->content;
	}
	

	

	/**
	 * Links the string $code to a create-new form for a record in $table created on page $pid
	 *
	 * @param	string		Link string
	 * @param	string		Table name (in which to create new record)
	 * @param	integer		PID value for the "&edit['.$table.']['.$pid.']=new" command (positive/negative)
	 * @param	boolean		If $addContentTable is set, then a new contentTable record is created together with pages
	 * @return	string		The link.
	 */
	function linkWrap($code,$table,$pid,$addContentTable=0)	{
		
		global $BACK_PATH,$TYPO3_CONF_VARS;
		
		
		/**
		 * TODO:
		 * We need to cache the last used pid for a record so that the user will
		 * not need to select the position of the record every time.
		 */
		
		
		if($table == 'tx_categories'){
			$pid = tx_categories_div::getPid();
		}

		$cField = '*';	
		
		$params = '&edit['.$table.']['.$pid.']=new'.
			($table=='pages'
				&& $GLOBALS['TYPO3_CONF_VARS']['SYS']['contentTable']
				&& isset($GLOBALS['TCA'][$GLOBALS['TYPO3_CONF_VARS']['SYS']['contentTable']])
				&& $addContentTable	?
				'&edit['.$GLOBALS['TYPO3_CONF_VARS']['SYS']['contentTable'].'][prev]=new&returnNewPageId=1'	:
				''
			).'&defVals=&defVals['.$table.']['.$cField.']='.$this->id.'&returnEditConf=1';
		$onClick = t3lib_BEfunc::editOnClick($params,$BACK_PATH,$this->returnUrl);
		return '<a href="#" onclick="'.htmlspecialchars($onClick).'">'.$code.'</a>';
	}

	
}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/mod_positionmap/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/mod_positionmap/index.php']);
}





// Make instance:
$SOBE = t3lib_div::makeInstance('tx_categories_modulepositionmap');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();
?>