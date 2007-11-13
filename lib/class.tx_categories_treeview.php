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
 * Contains the tx_categories_inlinetreeview class which extends the tx_categories_treebase class. 
 * This is used to render the category tree in tce forms
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

require_once(PATH_txcategories.'lib/class.tx_categories_treebase.php');

class tx_categories_treeview extends tx_categories_treebase {

	
	
	/**
	 * Initialize the tree class. Needs to be overwritten
	 * Will set ->fieldsArray, ->backPath and ->clause
	 *
	 * @param	string		record WHERE clause
	 * @param	string		record ORDER BY field
	 * @return	void
	 */
	function init($clause='', $orderByFields='')	{
		
		if($this->rootIds){
			$rootIds = t3lib_div::trimExplode(',',$this->rootIds,1);
			if(count($rootIds)){
				$this->MOUNTS = $rootIds;
			}
		}
		
		$params = array();
		$params['itemFormElName'] = $this->TCEforms_itemFormElName; 
		$params['treeName'] = $this->treeName;

		if(trim($this->rootIds)){
			$params['rootIds'] = $this->rootIds;
		}
		
		$this->additionalParams = t3lib_div::implodeArrayForUrl('',$params,'',1);
		
		
		parent::init($clause,$orderByFields);
	}
	
 
	
	/**
	 * Wrapping the image tag, $icon, for the row, $row (except for mount points)
	 *
	 * @param	string		The image tag for the icon
	 * @param	array		The row for the current element
	 * @return	string		The processed icon input value.
	 * @access private
	 */
 	function wrapIcon($icon,$row)	{
		
		global $TYPO3_CONF_VARS;
			// Wrap icon in click-menu link.
		if ($this->ext_IconMode){
			return $this->wrapClickMenuOnIcon($icon,$TYPO3_CONF_VARS['EXTCONF']['categories']['table'],$row['uid'],1,'&bank='.$this->bank.'&tree=1');
		} else {
			return $icon;		
		}
	}	


	
	
	/**
	 * wraps the record titles in the tree with links or not depending on if they are in the TCEforms_nonSelectableItemsArray.
	 *
	 * @param	string		$title: the title
	 * @param	array		$v: an array with uid and title of the current item.
	 * @return	string		the wrapped title
	 */
	function wrapTitle($title,$v)	{
		$hrefTitle = $v['description'];
		$aOnClick = 'window.parent.setFormValueFromBrowseWin(\''.rawurldecode($this->TCEforms_itemFormElName).'\','.$v['uid'].',\''.$title.'\'); return false;';
		return '<a href="#" onclick="'.htmlspecialchars($aOnClick).'" title="'.htmlentities($v['description']).'">'.$title.'</a>';
	}	
	
	
	
	/**
	 * Makes click menu link (context sensitive menu)
	 * Returns $str (possibly an <|img> tag/icon) wrapped in a link which will activate the context sensitive menu for the record ($table/$uid) or file ($table = file)
	 * The link will load the top frame with the parameter "&item" which is the table,uid and listFr arguments imploded by "|": rawurlencode($table.'|'.$uid.'|'.$listFr)
	 *
	 * @param	string		String to be wrapped in link, typ. image tag.
	 * @param	string		Table name/File path. If the icon is for a database record, enter the tablename from $TCA. If a file then enter the absolute filepath
	 * @param	integer		If icon is for database record this is the UID for the record from $table
	 * @param	boolean		Tells the top frame script that the link is coming from a "list" frame which means a frame from within the backend content frame.
	 * @param	string		Additional GET parameters for the link to alt_clickmenu.php
	 * @param	string		Enable / Disable click menu items. Example: "+new,view" will display ONLY these two items (and any spacers in between), "new,view" will display all BUT these two items.
	 * @param	boolean		If set, will return only the onclick JavaScript, not the whole link.
	 * @return	string		The link-wrapped input string.
	 */
	function wrapClickMenuOnIcon($str,$table,$uid='',$listFr=1,$addParams='',$enDisItems='', $returnOnClick=FALSE)	{
		$backPath = rawurlencode($this->backPath).'|'.t3lib_div::shortMD5($this->backPath.'|'.$GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']);
		$onClick = 'showClickmenu("'.$table.'","'.$uid.'","'.$listFr.'","'.str_replace('+','%2B',$enDisItems).'","'.str_replace('&','&amp;',addcslashes($backPath,'"')).'","'.addcslashes($addParams,'"').'");return false;';
		return $returnOnClick ? $onClick : '<a href="#" onclick="'.htmlspecialchars($onClick).'"'.($GLOBALS['TYPO3_CONF_VARS']['BE']['useOnContextMenuHandler'] ? ' oncontextmenu="'.htmlspecialchars($onClick).'"' : '').'>'.$str.'</a>';
	}	
	
	
	/**
	 * Compiles the HTML code for displaying the structure found inside the ->tree array
	 *
	 * @param	array		"tree-array" - if blank string, the internal ->tree array is used.
	 * @return	string		The HTML code for the tree
	 */
	function printTree($treeArr = '')   {
		$titleLen = intval($this->BE_USER->uc['titleLen']);
		if (!is_array($treeArr))	$treeArr = $this->tree;

		$out = '
			<!-- TYPO3 tree structure. -->
			<ul class="tree">
		';

			// -- evaluate AJAX request
			// IE takes anchor as parameter
		$PM = t3lib_div::_GP('PM');
		if(($PMpos = strpos($PM, '#')) !== false) { $PM = substr($PM, 0, $PMpos); }
		$PM = explode('_', $PM);
		if(($isAjaxCall = t3lib_div::_GP('ajax')) && is_array($PM) && count($PM)==4)	{
			if($PM[1])	{
				$expandedPageUid = $PM[2];
				$ajaxOutput = '';
				$invertedDepthOfAjaxRequestedItem = 0; // We don't know yet. Will be set later.
				$doExpand = true;
			} else	{
				$collapsedPageUid = $PM[2];
				$doCollapse = true;
			}
		}

		// we need to count the opened <ul>'s every time we dig into another level, 
		// so we know how many we have to close when all children are done rendering
		$closeDepth = array();

		foreach($treeArr as $k => $v)	{
			$classAttr = $v['row']['_CSSCLASS'];
			$uid	   = $v['row']['uid'];
			$idAttr	= htmlspecialchars($this->domIdPrefix.$this->getId($v['row']).'_'.$v['bank']);
			$itemHTML  = '';

			// if this item is the start of a new level, 
			// then a new level <ul> is needed, but not in ajax mode
			if($v['isFirst'] && !($doCollapse) && !($doExpand && $expandedPageUid == $uid))	{
				$itemHTML = '<ul>';
			}

			// add CSS classes to the list item
			if($v['hasSub']) { $classAttr .= ($classAttr) ? ' expanded': 'expanded'; }
			if($v['isLast']) { $classAttr .= ($classAttr) ? ' last'	: 'last';	 }

			$itemHTML .='
				<li id="'.$idAttr.'"'.($classAttr ? ' class="'.$classAttr.'"' : '').'>'.
					$v['HTML'].
					$this->wrapTitle($this->getTitleStr($v['row'],$titleLen),$v['row'],$v['bank'])."\n";


			if(!$v['hasSub']) { $itemHTML .= '</li>'; }

			// we have to remember if this is the last one
			// on level X so the last child on level X+1 closes the <ul>-tag
			if($v['isLast'] && !($doExpand && $expandedPageUid == $uid)) { $closeDepth[$v['invertedDepth']] = 1; }


			// if this is the last one and does not have subitems, we need to close
			// the tree as long as the upper levels have last items too
			if($v['isLast'] && !$v['hasSub'] && !$doCollapse && !($doExpand && $expandedPageUid == $uid)) {
				for ($i = $v['invertedDepth']; $closeDepth[$i] == 1; $i++) {
					$closeDepth[$i] = 0;
					$itemHTML .= '</ul></li>';
				}
			}

			// ajax request: collapse
			if($doCollapse && $collapsedPageUid == $uid) {
				$this->ajaxStatus = true;
				return $itemHTML;
			}

			// ajax request: expand
			if($doExpand && $expandedPageUid == $uid) {
				$ajaxOutput .= $itemHTML;
				$invertedDepthOfAjaxRequestedItem = $v['invertedDepth'];
			} elseif($invertedDepthOfAjaxRequestedItem) { 
				if($v['invertedDepth'] < $invertedDepthOfAjaxRequestedItem) {
					$ajaxOutput .= $itemHTML;
				} else {
					$this->ajaxStatus = true;
					return $ajaxOutput;
				}
			}

			$out .= $itemHTML;
		}

		if($ajaxOutput) {
			$this->ajaxStatus = true;
			return $ajaxOutput;
		}

		// finally close the first ul
		$out .= '</ul>';
		return $out;
	}

	
	/**
	 * Wrap the plus/minus icon in a link
	 *
	 * @param	string		HTML string to wrap, probably an image tag.
	 * @param	string		Command for 'PM' get var
	 * @param	boolean		If set, the link will have a anchor point (=$bMark) and a name attribute (=$bMark)
	 * @return	string		Link-wrapped input string
	 * @access private
	 */
	function PM_ATagWrap($icon,$cmd,$bMark='')	{
		if ($this->thisScript) {
			if ($bMark)	{
				$anchor = '#'.$bMark;
				$name=' name="'.$bMark.'"';
			}

			$aUrl = $this->thisScript.'?PM='.$cmd.$this->additionalParams.$anchor;
			return '<a href="'.htmlspecialchars($aUrl).'"'.$name.'>'.$icon.'</a>';
		} else {
			return $icon;
		}
	}	
	
	
	/**
	 * Fetches the data for the tree
	 *
	 * @param	integer		item id for which to select subitems (parent id)
	 * @param	integer		Max depth (recursivity limit)
	 * @param	string		? (internal)
	 * @return	integer		The count of items on the level
	 */
	function getTree($uid, $depth=999, $blankLineCode='', $subCSSclass='') {
		
			// Buffer for id hierarchy is reset:
		$this->buffer_idH = array();

			// Init vars
		$depth = intval($depth);
		$HTML = '';
		$a = 0;

		$res = $this->getDataInit($uid, $subCSSclass);
		$c = $this->getDataCount($res);
	
		$crazyRecursionLimiter = 999;

			// Traverse the records:
		while ($crazyRecursionLimiter > 0 && $row = $this->getDataNext($res,$subCSSclass))	{
			
			$a++;
			$crazyRecursionLimiter--;
			
			$newID = $row['uid']; 
			

			$this->tree[]=array();	  // Reserve space.
			end($this->tree);
			$treeKey = key($this->tree);	// Get the key for this space
			$LN = ($a==$c) ? 'blank' : 'line';

				// If records should be accumulated, do so
			if ($this->setRecs) { $this->recs[$row['uid']] = $row; }

				// Accumulate the id of the element in the internal arrays
			$this->ids[]=$idH[$row['uid']]['uid'] = $row['uid'];
			$this->ids_hierarchy[$depth][] = $row['uid'];

				// Make a recursive call to the next level
			if ($depth > 1 && $this->expandNext($newID) && !$row['php_tree_stop'])	{
				
				
				$nextCount=$this->getTree(
					$newID,
					$depth-1,
					$blankLineCode.','.$LN,
					$row['_SUBCSSCLASS']
				);
				if (count($this->buffer_idH)) { $idH[$row['uid']]['subrow']=$this->buffer_idH; }
				$exp = 1; // Set "did expand" flag
			} else {
				$nextCount = $this->getCount($newID);
				$exp = 0; // Clear "did expand" flag
			}

				// Set HTML-icons, if any:
			if ($this->makeHTML)	{
				$HTML = $this->PMicon($row,$a,$c,$nextCount,$exp);
				$HTML.= $this->wrapStop($this->getIcon($row),$row);
			}

				// Finally, add the row/HTML content to the ->tree array in the reserved key.
			$this->tree[$treeKey] = array(
				'row'    => $row,
				'HTML'   => $HTML,
				'hasSub' => $nextCount&&$this->expandNext($newID),
				'isFirst'=> $a==1,
				'isLast' => false,
				'invertedDepth'=> $depth,
				'blankLineCode'=> $blankLineCode,
				'bank' => $this->bank
			);
		}

		if($a) { $this->tree[$treeKey]['isLast'] = true; }

		$this->getDataFree($res);
		$this->buffer_idH = $idH;
		return $c;
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/lib/class.tx_categories_treeview.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/lib/class.tx_categories_treeview.php']);
}
?>