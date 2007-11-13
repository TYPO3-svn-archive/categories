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
 * Contains the tx_categories_treebase class which extends the t3lib_treeview class. 
 * This is supposed to be the base class for all category tree classes. It's not
 * supposed to be instatiated directly
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

require_once(PATH_t3lib.'class.t3lib_treeview.php');

/*
	Including the class with db functions for categories
*/
require_once(PATH_txcategories.'lib/class.tx_categories_db.php');

class tx_categories_treebase extends t3lib_treeview {

	var $additionalParams = '';
	var $domIdPrefix = 'categories';
	
	
	/**
	 * Wrap the plus/minus icon in a link
	 *
	 * @param	string		HTML string to wrap, probably an image tag.
	 * @param	string		Command for 'PM' get var
	 * @return	string		Link-wrapped input string
	 * @access private
	 */
	function PMiconATagWrap($icon, $cmd, $isExpand = true)	{
		if ($this->thisScript) {
				// activate dynamic ajax-based tree
			$js = htmlspecialchars('Tree.load(\''.$cmd.$this->additionalParams.'\', '.intval($isExpand).', this);');
			return '<a class="pm" onclick="'.$js.'">'.$icon.'</a>';
		} else {
			return $icon;
		}
	}



	/**
	 * Will create and return the HTML code for a browsable tree
	 * Is based on the mounts found in the internal array ->MOUNTS (set in the constructor)
	 *
	 * @return	string		HTML code for the browsable tree
	 */
	function getBrowsableTree() {

			// Get stored tree structure AND updating it if needed according to incoming PM GET var.
		$this->initializePositionSaving();

			// Init done:
		$titleLen = intval($this->BE_USER->uc['titleLen']);
		$treeArr = array();
		
			// Traverse mounts:
		foreach($this->MOUNTS as $idx => $uid)  {

				// Set first:
			$this->bank = $idx;
			$isOpen = $this->stored[$idx][$uid] || $this->expandFirst;

				// Save ids while resetting everything else.
			$curIds = $this->ids;
			$this->reset();
			$this->ids = $curIds;

				// Set PM icon for root of mount:
			$cmd = $this->bank.'_'.($isOpen? "0_" : "1_").$uid.'_'.$this->treeName;
			$icon='<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/ol/'.($isOpen?'minus':'plus').'only.gif').' alt="" />';
			$firstHtml = $this->PMiconATagWrap($icon,$cmd);

				// Preparing rootRec for the mount
			if ($uid)   {
				$rootRec = $this->getRecord($uid);
				$firstHtml.=$this->getIcon($rootRec);
			} else {
				// Artificial record for the tree root, id=0
				$rootRec = $this->getRootRecord($uid);
				$firstHtml.=$this->getRootIcon($rootRec);
			}

			if (is_array($rootRec)) {
					// In case it was swapped inside getRecord due to workspaces.
				$uid = $rootRec['uid'];

					// Add the root of the mount to ->tree
				$this->tree[] = array('HTML'=>$firstHtml, 'row'=>$rootRec, 'bank'=>$this->bank, 'hasSub'=>true, 'invertedDepth'=>1000);

					// If the mount is expanded, go down:
				if ($isOpen)	{
						// Set depth:
					if ($this->addSelfId) { $this->ids[] = $uid; }
					$this->getTree($uid, 999, '', $rootRec['_SUBCSSCLASS']);
				}
					// Add tree:
				$treeArr=array_merge($treeArr,$this->tree);
			}
		}
		return $this->printTree($treeArr);
	}


	

	/**
	 * Generate the plus/minus icon for the browsable tree.
	 *
	 * @param	array		record for the entry
	 * @param	integer		The current entry number
	 * @param	integer		The total number of entries. If equal to $a, a "bottom" element is returned.
	 * @param	integer		The number of sub-elements to the current element.
	 * @param	boolean		The element was expanded to render subelements if this flag is set.
	 * @return	string		Image tag with the plus/minus icon.
	 * @access private
	 * @see t3lib_pageTree::PMicon()
	 */
	function PMicon($row,$a,$c,$nextCount,$exp)	{
		$PM   = $nextCount ? ($exp ? 'minus' : 'plus') : 'join';
		$BTM  = ($a == $c) ? 'bottom' : '';
		$icon = '<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/ol/'.$PM.$BTM.'.gif','width="18" height="16"').' alt="" />';

		if ($nextCount) {
			$cmd = $this->bank.'_'.($exp?'0_':'1_').$row['uid'].'_'.$this->treeName;
			$icon = $this->PMiconATagWrap($icon,$cmd,!$exp);
		}
		return $icon;
	}
	
	
	/**
	 * Getting the tree data: Selecting/Initializing data pointer to items for a certain parent id.
	 * For tables: This will make a database query to select all children to "parent"
	 * For arrays: This will return key to the ->dataLookup array
	 *
	 * @param	integer		parent item id
	 * @param	string		Class for sub-elements.
	 * @return	mixed		data handle (Tables: An sql-resource, arrays: A parentId integer. -1 is returned if there were NO subLevel.)
	 * @access private
	 */
	function getDataInit($parentId,$subCSSclass='') {
				
		return tx_categories_db::exec_SELECTquery_getChildren(
							$parentId,
							implode(',',$this->fieldArray),
							t3lib_BEfunc::deleteClause($this->table).
							t3lib_BEfunc::versioningPlaceholderClause($this->table).
							$this->clause,
							$this->orderByFields
					);

	}	




	/**
	 * Returns the number of records having the parent id, $uid
	 *
	 * @param	integer		id to count subitems for
	 * @return	integer
	 * @access private
	 */
	function getCount($uid)	{
		
		return tx_categories_db::countChildren(
						$uid,
						t3lib_BEfunc::deleteClause($this->table).
						t3lib_BEfunc::versioningPlaceholderClause($this->table).
						$this->clause
					);
		

	}	

}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/lib/class.tx_categories_treebase.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/lib/class.tx_categories_treebase.php']);
}

?>