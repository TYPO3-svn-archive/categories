<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Mads Brunn (mads@typoconsult.dk)
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
 * Alternative version of tce_db.php specifically customized for use with tx_categories extension
 *
 * @author	Mads Brunn <mads@typoconsult.dk>
 */
/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 */

define('TYPO3_MOD_PATH', '../typo3conf/ext/categories/'); 
$BACK_PATH='../../../typo3/';
require ($BACK_PATH.'init.php');
require ($BACK_PATH.'template.php');
require_once (PATH_t3lib.'class.t3lib_tcemain.php');
require_once (PATH_txcategories.'lib/class.tx_categories_clipboard.php');



/**
 * Script Class, creating object of t3lib_TCEmain and sending the posted data to the object.
 * Used by many smaller forms/links in TYPO3, including the QuickEdit module.
 * Is not used by alt_doc.php though (main form rendering script) - that uses the same class (TCEmain) but makes its own initialization (to save the redirect request).
 * For all other cases than alt_doc.php it is recommended to use this script for submitting your editing forms - but the best solution in any case would probably be to link your application to alt_doc.php, that will give you easy form-rendering as well.
 *
 * @author	Mads Brunn <mads@typoconsult.dk>
 * @package TYPO3
 * @subpackage core
 */
class tce_categories {

		// Internal, static: GPvar
	var $flags;			// Array. Accepts options to be set in TCE object. Currently it supports "reverseOrder" (boolean).
	var $data;			// Data array on the form [tablename][uid][fieldname] = value
	var $cmd;			// Command array on the form [tablename][uid][command] = value. This array may get additional data set internally based on clipboard commands send in CB var!
	var $mirror;		// Array passed to ->setMirror.
	var $cacheCmd;		// Cache command sent to ->clear_cacheCmd
	var $redirect;		// Redirect URL. Script will redirect to this location after performing operations (unless errors has occured)
	var $prErr;			// Boolean. If set, errors will be printed on screen instead of redirection. Should always be used, otherwise you will see no errors if they happen.
#	var $_disableRTE;
	var $CB;			// Clipboard command array. May trigger changes in "cmd"
	var $vC;			// Verification code
	var $uPT;			// Boolean. Update Page Tree Trigger. If set and the manipulated records are pages then the update page tree signal will be set.
	var $generalComment;	// String, general comment (for raising stages of workspace versions)

		// Internal, dynamic:
	var $include_once=array();		// Files to include after init() function is called:
	var $tce;						// TCEmain object



	/**
	 * Initialization of the class
	 *
	 * @return	void
	 */
	function init()	{
		global $BE_USER;

		$GLOBALS['TYPO3_DB']->debugOutput = TRUE;
		
			// GPvars:
		$this->flags = t3lib_div::_GP('flags');
		$this->data = t3lib_div::_GP('data');
		
		
		$this->data = $this->convertDataMap($this->data);
		
		$this->cmd = t3lib_div::_GP('cmd');
		
		$this->mirror = t3lib_div::_GP('mirror');
		$this->cacheCmd = t3lib_div::_GP('cacheCmd');
		$this->redirect = t3lib_div::_GP('redirect');
		$this->prErr = t3lib_div::_GP('prErr');
		$this->_disableRTE = t3lib_div::_GP('_disableRTE');
		$this->CB = t3lib_div::_GP('CB');
		$this->vC = t3lib_div::_GP('vC');
		$this->uPT = t3lib_div::_GP('uPT');
		$this->generalComment = t3lib_div::_GP('generalComment');

			// Creating TCEmain object
		$this->tce = t3lib_div::makeInstance('t3lib_TCEmain');
		$this->tce->stripslashes_values=0;
		$this->tce->generalComment = $this->generalComment;

			// Configuring based on user prefs.
		if ($BE_USER->uc['recursiveDelete'])	{
			$this->tce->deleteTree = 1;	// True if the delete Recursive flag is set.
		}
		if ($BE_USER->uc['copyLevels'])	{
			$this->tce->copyTree = t3lib_div::intInRange($BE_USER->uc['copyLevels'],0,100);	// Set to number of page-levels to copy.
		}
		if ($BE_USER->uc['neverHideAtCopy'])	{
			$this->tce->neverHideAtCopy = 1;
		}

		$TCAdefaultOverride = $BE_USER->getTSConfigProp('TCAdefaults');
		if (is_array($TCAdefaultOverride))	{
			$this->tce->setDefaultsFromUserTS($TCAdefaultOverride);
		}

			// Reverse order.
		if ($this->flags['reverseOrder'])	{
			$this->tce->reverseOrder=1;
		}

			// Clipboard?
		if (is_array($this->CB))	{
			$this->include_once[]=PATH_t3lib.'class.t3lib_clipboard.php';
		}
	}
	

	
	function convertDataMap($data){
		
		$mm = 'tx_categories_mm'; 

		if(is_array($data)){
			foreach($data as $tablename => $uids){
				foreach($uids as $uid => $fieldnames){
					foreach($fieldnames as $fieldname => $value){
						if($fieldname == '*' && $value <= 0){
							
							$category_to_remove = abs($value);
							$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
												'uid_foreign',
												$mm,
												'uid_local='.$uid.' AND localtable="'.$tablename.'" AND uid_foreign <> '.$category_to_remove
											);
							$categories = array();
							while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)){
								$categories[$row['uid_foreign']] = $row['uid_foreign'];
							}	
							$data[$tablename][$uid][$fieldname]=implode(",",$categories);
						}
					}
				}
			}
		}

		
		return $data;
	}


	/**
	 * Clipboard pasting and deleting.
	 *
	 * @return	void
	 */
	function initClipboard()	{
		if (is_array($this->CB))	{
			$clipObj = t3lib_div::makeInstance('tx_categories_clipboard');
			$clipObj->initializeClipboard();
			
			if ($this->CB['paste'])	{
				$clipObj->setCurrentPad($this->CB['pad']);
				$this->data = $clipObj->makePasteDataMap($this->CB['paste'],$this->data);
			}
			
			if ($this->CB['delete'])	{
				$clipObj->setCurrentPad($this->CB['pad']);
				//$this->cmd = $clipObj->makeDeleteCmdArray($this->cmd);
				$this->data = $clipObj->makeDeleteDataMap($this->CB['delete'],$this->data);
			}
		}
	}

	


	/**
	 * Executing the posted actions ...
	 *
	 * @return	void
	 */
	function main()	{
		
		global $BE_USER,$TYPO3_CONF_VARS;

			// LOAD TCEmain with data and cmd arrays:
		$this->tce->start($this->data,$this->cmd);
		if (is_array($this->mirror))	{$this->tce->setMirror($this->mirror);}

			// Checking referer / executing
		$refInfo=parse_url(t3lib_div::getIndpEnv('HTTP_REFERER'));
		$httpHost = t3lib_div::getIndpEnv('TYPO3_HOST_ONLY');
		if ($httpHost!=$refInfo['host'] && $this->vC!=$BE_USER->veriCode() && !$TYPO3_CONF_VARS['SYS']['doNotCheckReferer'])	{
			$this->tce->log('',0,0,0,1,'Referer host "%s" and server host "%s" did not match and veriCode was not valid either!',1,array($refInfo['host'],$httpHost));
		} else {
				// Register uploaded files
			$this->tce->process_uploads($_FILES);

				// Execute actions:
			$this->tce->process_datamap();
			$this->tce->process_cmdmap();

				// Clearing cache:
			$this->tce->clear_cacheCmd($this->cacheCmd);

				// Update page tree?
			if ($this->uPT && (isset($this->data['tx_categories'])||isset($this->cmd['tx_categories'])))	{
				t3lib_BEfunc::getSetUpdateSignal('updatePageTree');
			}
		}
	}

	/**
	 * Redirecting the user after the processing has been done.
	 * Might also display error messages directly, if any.
	 *
	 * @return	void
	 */
	function finish()	{
			// Prints errors, if...
		if ($this->prErr)	{
			$this->tce->printLogErrorMessages($this->redirect);
		}

		if ($this->redirect && !$this->tce->debug) {
			Header('Location: '.t3lib_div::locationHeaderUrl($this->redirect));
		}
	}
}

// Include extension?
if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/tce_categories.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/tce_categories.php']);
}







// Make instance:
$SOBE = t3lib_div::makeInstance('tce_categories');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)	include_once($INC_FILE);

$SOBE->initClipboard();
$SOBE->main();
$SOBE->finish();



?>