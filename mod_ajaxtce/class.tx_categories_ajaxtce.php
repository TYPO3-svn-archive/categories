<?php


require_once(PATH_t3lib.'class.t3lib_tcemain.php');

class tx_categories_ajaxtce{
	
	
	
	function init(){
		global $BE_USER,$LANG,$BACK_PATH,$TYPO3_CONF_VARS,$CLIENT;
		
		//retriving some GET-params
		$this->data = t3lib_div::_GP('data');
		
		//initializing the doc object
		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->docType='xhtml_trans';
		$this->doc->backPath = $BACK_PATH;
		
		$GLOBALS['TBE_TEMPLATE']->backPath = $BACK_PATH;
		
	}
	
	function main(){
		global $LANG;
		
		$out = array();
	}
	
	
	
	function printContent(){
		header('Content-type: text/xml; charset=utf-8');
		echo $this->content;
	}

	
	
	
	
	
	
	
}



// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/mod_ajaxtce/class.tx_categories_ajaxtce.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/mod_ajaxtce/class.tx_categories_ajaxtce.php']);
}




?>