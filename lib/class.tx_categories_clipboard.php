<?php


require_once(PATH_t3lib.'class.t3lib_clipboard.php');

class tx_categories_clipboard extends t3lib_clipboard{
	
	var $module = 'clipboard'; 

	
	/**
	 * Initialize the clipboard from the be_user session
	 *
	 * @return	void
	 */
	function initializeClipboard()	{
		global $BE_USER;

			// Get data
		$clipData = $BE_USER->getModuleData($this->module,$BE_USER->getTSConfigVal('options.saveClipboard')?'':'ses');

			// NumberTabs
		$clNP = $BE_USER->getTSConfigVal('options.clipboardNumberPads');
		if (t3lib_div::testInt($clNP) && $clNP>=0)	{
			$this->numberTabs = t3lib_div::intInRange($clNP,0,20);
		}

			// Resets/reinstates the clipboard pads
		$this->clipData['normal'] = is_array($clipData['normal']) ? $clipData['normal'] : array();
		for ($a=1;$a<=$this->numberTabs;$a++)	{
			$this->clipData['tab_'.$a] = is_array($clipData['tab_'.$a]) ? $clipData['tab_'.$a] : array();
		}

			// Setting the current pad pointer ($this->current) and _setThumb (which determines whether or not do show file thumbnails)
		$this->clipData['current'] = $this->current = isset($this->clipData[$clipData['current']]) ? $clipData['current'] : 'normal';
		$this->clipData['_setThumb'] = $clipData['_setThumb'];
	}


	/**
	 * Saves the clipboard, no questions asked.
	 * Use ->endClipboard normally (as it checks if changes has been done so saving is necessary)
	 *
	 * @return	void
	 * @access private
	 */
	function saveClipboard()	{
		global $BE_USER;
		$BE_USER->pushModuleData($this->module,$this->clipData);
	}

	

	function elFromCategorizedTables($pad=''){
		$pad = $pad ? $pad : $this->current;
		$list=array();
		if (is_array($this->clipData[$pad]['el']))	{
			reset($this->clipData[$pad]['el']);
			while(list($k,$v)=each($this->clipData[$pad]['el']))	{
				if ($v)	{
					list($table,$uid) = explode('|',$k);
					if ($table!='_FILE') {
						$list[$k]= ($pad=='normal'?$v:$uid);
					} 
				}
			}
		}
		return $list;		
	}


	function makePasteDataMap($ref,$data){
		
		list($pTable,$cId) = explode('|',$ref);
		$cId = intval($cId);

		if ($pTable || $cId>=0)	{	// pUid must be set and if pTable is not set (that means paste ALL elements) the uid MUST be positive/zero (pointing to page id)
			$elements = $this->elFromCategorizedTables();

			$elements = array_reverse($elements);	// So the order is preserved.

				// Traverse elements and make Datamap array
			reset($elements);
			while(list($tP)=each($elements))	{
				list($table,$uid) = explode('|',$tP);

				$mm = 'tx_categories_mm'; 
				$field = '*';
				
				$categories = array();
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
									'uid_foreign',
									$mm,
									'uid_local='.$uid.' AND localtable="'.$table.'"'
								);
								
				while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
					$categories[$row['uid_foreign']] = $row['uid_foreign'];
				}
				
				$categories[$cId] = $cId; 
				
				if (!is_array($data[$table]))	$data[$table]=array();
				$data[$table][$uid][$field]=implode(",",$categories);
				
			}
			$this->endClipboard();
		}
		return $data;		
	}


	function makeDeleteDataMap($ref,$data){
		
		list($pTable,$cId) = explode('|',$ref);
		$cId = intval($cId);

		if ($pTable || $cId>=0)	{	// pUid must be set and if pTable is not set (that means paste ALL elements) the uid MUST be positive/zero (pointing to page id)
			$elements = $this->elFromCategorizedTables();

			$elements = array_reverse($elements);	// So the order is preserved.

				// Traverse elements and make Datamap array
			reset($elements);
			while(list($tP)=each($elements))	{
				list($table,$uid) = explode('|',$tP);

				$mm = 'tx_categories_mm'; 
				$field = '*';
				
				$categories = array();
				$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
									'uid_foreign',
									$mm,
									'uid_local='.$uid.' AND localtable="'.$table.'"'
								);
								
				while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
					$categories[$row['uid_foreign']] = $row['uid_foreign'];
				}
				
				$categories[$cId] = $cId; 
				
				if (!is_array($data[$table]))	$data[$table]=array();
				$data[$table][$uid][$field]=implode(",",$categories);
				
			}
			$this->endClipboard();
		}
		return $data;		
	}
	
	
	
	
	
	function pasteUrl($table,$uid,$setRedirect=1){

		$rU = $this->backPath.PATH_txcategories_rel.'tce_categories.php?'.
			($setRedirect ? 'redirect='.rawurlencode(t3lib_div::linkThisScript(array('CB'=>''))) : '').
			'&vC='.$GLOBALS['BE_USER']->veriCode().
			'&prErr=1&uPT=1'.
			'&CB[paste]='.rawurlencode($table.'|'.$uid).
			'&CB[pad]='.$this->clipboardObj->current;
			
		return $rU;		
	}




}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/lib/class.tx_categories_clipboard.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/lib/class.tx_categories_clipboard.php']);
}
?>