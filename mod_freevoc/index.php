<?php

unset($MCONF);
require('conf.php');
include ($BACK_PATH.'init.php');
include ($BACK_PATH.'template.php');

class tx_categories_freevoc{
	
	
	
	
	
	function init(){
		global $BE_USER,$LANG,$BACK_PATH,$TYPO3_CONF_VARS,$CLIENT;

		//retriving some GET-params
		$this->root = t3lib_div::_GP('root');
		$this->sword = t3lib_div::_GP('sword');
		
		//initializing the doc object
		$this->doc = t3lib_div::makeInstance('template');
		$this->doc->docType='xhtml_trans';
		$this->doc->backPath = $BACK_PATH;
		
		//is this an ajax call?
		$GLOBALS['TBE_TEMPLATE']->backPath = $BACK_PATH;
		
	}
	
	function main(){
		global $LANG;
		$out = array();
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_categories','title LIKE "%'.$this->sword.'%" AND deleted=0 AND hidden=0','','','0,1000');
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
			$out[] = '<option value="'.$row['uid'].'">'.$row['title'].'</option>';
		}
		$this->content = $LANG->csConvObj->utf8_encode(implode("",$out), $LANG->charSet);
	}
	
	function printContent(){
		//header('X-JSON: ('.($this->treeObject->ajaxStatus?'true':'false').')');
		//header('X-JSON: (true)');
		header('Content-type: text/xml; charset=utf-8');
		echo $this->content;
	}

	
	
	
	
	
	
	
}






// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/mod_freevoc/index.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/mod_freevoc/index.php']);
}

	// Make instance:
$SOBE = t3lib_div::makeInstance('tx_categories_freevoc');
$SOBE->init();
$SOBE->main();
$SOBE->printContent();




?>