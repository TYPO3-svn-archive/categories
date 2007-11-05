<?php


//From Web -> List
$LANG->includeLLFile('EXT:lang/locallang_mod_web_list.xml');
require_once (PATH_t3lib.'class.t3lib_page.php');
require_once (PATH_t3lib.'class.t3lib_pagetree.php');
require_once (PATH_t3lib.'class.t3lib_recordlist.php');
require_once (PATH_t3lib.'class.t3lib_clipboard.php');
require_once (PATH_typo3.'class.db_list.inc');
require_once (PATH_typo3.'class.db_list_extra.inc');
require_once (PATH_txcategories.'lib/class.tx_categories_div.php');


class tx_categories_recordlist extends localRecordList{
	
	
	var $script = 'index.php';
	var $hookObjectsArr = array();	
	
	
	function tx_categories_recordlist(){
		
		$this->initHooks();	
		
		
	}
	

	/**
	 * Writes the top of the full listing
	 *
	 * @param	array		Current page record
	 * @return	void		(Adds content to internal variable, $this->HTMLcode)
	 */
	function writeTop($row)	{
	
		
		global $LANG;

			// Makes the code for the pageicon in the top
		$this->categoryRow = $row;
		$this->counter++;
		$alttext = t3lib_BEfunc::getRecordIconAltText($row,$this->parentTable);
		$iconImg = t3lib_iconWorks::getIconImage($this->parentTable,$row,$this->backPath,'class="absmiddle" title="'.htmlspecialchars($alttext).'"');
		$titleCol = 'test';	// pseudo title column name
		$this->fieldArray = Array($titleCol,'up');		// Setting the fields to display in the list (this is of course "pseudo fields" since this is the top!)


			// Filling in the pseudo data array:
		$theData = Array();
		$theData[$titleCol] = $this->widthGif;

			// Get users permissions for this row:
		//$localCalcPerms = $GLOBALS['BE_USER']->calcPerms($row);

		$theData['up']=array();

			// Initialize control panel for currect page ($this->id):
			// Some of the controls are added only if $this->id is set - since they make sense only on a real page, not root level.
		$theCtrlPanel =array();


			// Adding "Edit page" icon:
		if ($this->id)	{

			$params='&edit['.$this->parentTable.']['.$row['uid'].']=edit';
			$theCtrlPanel[]='<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$this->backPath,'')).'">'.
							'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/edit2.gif','width="11" height="12"').' title="'.$LANG->getLL('editPage',1).'" alt="" />'.
							'</a>';
		}
		

			// Adding "Hide/Unhide" icon:
		if ($this->id)	{
			if ($row['hidden'])	{
				$params='&data['.$this->parentTable.']['.$row['uid'].'][hidden]=0';
				$theCtrlPanel[]='<a href="#" onclick="'.htmlspecialchars('return jumpToUrl(\''.$GLOBALS['SOBE']->doc->issueCommand($params,0).'\');').'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_unhide.gif','width="11" height="10"').' title="'.$LANG->getLL('unHidePage',1).'" alt="" />'.
								'</a>';

			} else {
				$params='&data['.$this->parentTable.']['.$row['uid'].'][hidden]=1';
				$theCtrlPanel[]='<a href="#" onclick="'.htmlspecialchars('return jumpToUrl(\''.$GLOBALS['SOBE']->doc->issueCommand($params,0).'\');').'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_hide.gif','width="11" height="10"').' title="'.$LANG->getLL('hidePage',1).'" alt="" />'.
								'</a>';
			}
		}
		

			// "Paste into category" link:
		$elFromTable = $this->clipObj->elFromTable('');
		if (count($elFromTable) && $this->id)	{
			$theCtrlPanel[]='<a href="'.htmlspecialchars($this->clipObj->pasteUrl('',$this->id)).'" onclick="'.htmlspecialchars('return '.$this->clipObj->confirmMsg($this->parentTable,$this->categoryRow,'into',$elFromTable)).'">'.
							'<img'.t3lib_iconWorks::skinImg($this->backPath.PATH_txcategories_rel,'gfx/clip_pastesubref.gif','width="12" height="12"').' title="'.$LANG->getLL('insertrecordsintothiscategory',1).'" alt="" />'.
							'</a>';
		}

			// Finally, compile all elements of the control panel into table cells:
		if (count($theCtrlPanel))	{
			$theData['up'][]='

				<!--
					Control panel for page
				-->
				<table border="0" cellpadding="0" cellspacing="0" class="bgColor4" id="typo3-dblist-ctrltop">
					<tr>
						<td>'.implode('</td>
						<td>',$theCtrlPanel).'</td>
					</tr>
				</table>';
		}

			// Add "CSV" link, if a specific table is shown:
		if ($this->table)	{
			$theData['up'][]='<a href="'.htmlspecialchars($this->listURL().'&csv=1').'">'.
							'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/csv.gif','width="27" height="14"').' title="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.csv',1).'" alt="" />'.
							'</a>';
		}


			// Add "refresh" link:
		$theData['up'][]='<a href="'.htmlspecialchars($this->listURL()).'">'.
						'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/refresh_n.gif','width="14" height="14"').' title="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.reload',1).'" alt="" />'.
						'</a>';


			// Add icon with clickmenu, etc:
		if ($this->id)	{	// If there IS a real category...:	
			
				// Setting title of page + the "Go up" link:
			$theData[$titleCol].='<br /><span title="'.htmlspecialchars($row['_thePathFull']).'">'.htmlspecialchars(t3lib_div::fixed_lgd_cs($row['_thePath'],-$this->fixedL)).'</span>';

				// Make Icon:
			$theIcon = $this->clickMenuEnabled ? $GLOBALS['SOBE']->doc->wrapClickMenuOnIcon($iconImg,$this->parentTable,$this->id) : $iconImg;
		
		} else {	// On root-level of page tree:

				// Setting title of root (sitename):
			//$theData[$titleCol].='<br />'.htmlspecialchars(t3lib_div::fixed_lgd_cs($GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'],-$this->fixedL));
			$theData[$titleCol].='<br />'.htmlspecialchars(t3lib_div::fixed_lgd_cs($LANG->sL($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['categories']['rootname']),-$this->fixedL));

				// Make Icon:
			$theIcon = '<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/i/_icon_website.gif','width="18" height="16"').' alt="" />';
		}

			// If there is a returnUrl given, add a back-link:
		if ($this->returnUrl)	{
			$theData['up'][]='<a href="'.htmlspecialchars(t3lib_div::linkThisUrl($this->returnUrl,array('id'=>$this->id))).'" class="typo3-goBack">'.
							'<img'.t3lib_iconWorks::skinImg($GLOBALS["BACK_PATH"],'gfx/goback.gif','width="14" height="14"').' title="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.goBack',1).'" alt="" />'.
							'</a>';
		}

			// Finally, the "up" pseudo field is compiled into a table - has been accumulated in an array:
		$theData['up']='
			<table border="0" cellpadding="0" cellspacing="0">
				<tr>
					<td>'.implode('</td>
					<td>',$theData['up']).'</td>
				</tr>
			</table>';

			// ... and the element row is created:
		$out.=$this->addelement(1,$theIcon,$theData,'',$this->leftMargin);

			// ... and wrapped into a table and added to the internal ->HTMLcode variable:
		$this->HTMLcode.='


		<!--
			Page header for db_list:
		-->
			<table border="0" cellpadding="0" cellspacing="0" id="typo3-dblist-top">
				'.$out.'
			</table>';
	}	
	
	
	/**
	 * Traverses the table(s) to be listed and renders the output code for each:
	 * The HTML is accumulated in $this->HTMLcode
	 * Finishes off with a stopper-gif
	 *
	 * @return	void
	 */
	function generateList()	{
		global $TCA,$TYPO3_CONF_VARS;
		
		//$GLOBALS['TYPO3_DB']->debugOutput = TRUE;
		//$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = TRUE;
		
		
			// We change the order of keys in the TCA array to ensure that tx_categories are rendered first:
		$tmpTCA = $TCA;
		unset($tmpTCA[$this->parentTable]);
		$tx_categories_tca = array($this->parentTable => $TCA[$this->parentTable]);
		$tmpTCA = array_merge($tx_categories_tca,$TCA);
		
			// Traverse the TCA table array:			
		reset($tmpTCA);
		while (list($tableName)=each($tmpTCA))	{

				// Checking if the table should be rendered:
			if (
				(!$this->table || $tableName==$this->table) && 
				(!$this->tableList || t3lib_div::inList($this->tableList,$tableName)) && 
				$GLOBALS['BE_USER']->check('tables_select',$tableName) &&
				tx_categories_div::isTableAllowedForCategorization($tableName)
			){		// Checks that we see only permitted/requested tables:

					// Load full table definitions:
				t3lib_div::loadTCA($tableName);

					// Hide tables which are configured via TSConfig not to be shown (also works for admins):
				//if (t3lib_div::inList($this->hideTables, $tableName))	continue;

					// iLimit is set depending on whether we're in single- or multi-table mode
				if ($this->table)	{
					$this->iLimit=(isset($TCA[$tableName]['interface']['maxSingleDBListItems'])?intval($TCA[$tableName]['interface']['maxSingleDBListItems']):$this->itemsLimitSingleTable);
				} else {
					$this->iLimit=(isset($TCA[$tableName]['interface']['maxDBListItems'])?intval($TCA[$tableName]['interface']['maxDBListItems']):$this->itemsLimitPerTable);
				}
				if ($this->showLimit)	$this->iLimit = $this->showLimit;

					// Setting fields to select:
				if ($this->allFields)	{
					$fields = $this->makeFieldList($tableName);
					$fields[]='tstamp';
					$fields[]='crdate';
					$fields[]='_PATH_';
					$fields[]='_CONTROL_';
					if (is_array($this->setFields[$tableName]))	{
						$fields = array_intersect($fields,$this->setFields[$tableName]);
					} else {
						$fields = array();
					}
				} else {
					$fields = array();
				}

					// Find ID to use (might be different for "versioning_followPages" tables)
				//if (intval($this->searchLevels)==0)	{
				//	if ($TCA[$tableName]['ctrl']['versioning_followPages'] && $this->pageRecord['_ORIG_pid']==-1 && $this->pageRecord['t3ver_swapmode']==0)	{
				//		$this->pidSelect = 'pid='.intval($this->pageRecord['_ORIG_uid']);
				//	} else {
				//		$this->pidSelect = 'pid='.intval($this->id);
				//	}
				//}
				$this->pidSelect = '';
				
				// Finally, render the list:
				$this->HTMLcode.=$this->getTable($tableName, $this->id, implode(',',$fields));

			}
		}
	}
	
	
	function getPagesJoinStmt($table){

		if($this->perms_clause && $table != 'pages'){
			return ' INNER JOIN pages p ON p.uid='.$table.'.pid AND '.str_replace('pages.','p.',$this->perms_clause); 
		}
	}
	
	function getCategoryJoinStmt($table,$id){
		
		if(!$this->searchLevels){ //no need to waste more time here

			if(!$id && $table == $this->parentTable){
				return '';
			} else {
				return ' INNER JOIN '.$this->mm.' mm ON mm.uid_local = '.$table.'.uid AND mm.uid_foreign='.$id.' AND mm.localtable="'.$table.'"';
			}
			
		} else {

			if(!$this->cidList){
				$this->cidList = tx_categories_div::getSubCategoriesAsUidList(array($id),$this->searchLevels,0,1);
			}
			$joinstmt = ' INNER JOIN '.$this->mm.' mm ON mm.uid_local = '.$table.'.uid AND mm.uid_foreign IN ('.$this->cidList.') AND mm.localtable="'.$table.'"';
			return $joinstmt;
			
		}

	}
	
	
	/**
	 * Returns the SQL-query array to select the records from a table $table with pid = $id
	 *
	 * @param	string		Table name
	 * @param	integer		Page id (NOT USED! $this->pidSelect is used instead)
	 * @param	string		Additional part for where clause
	 * @param	string		Field list to select, * for all (for "SELECT [fieldlist] FROM ...")
	 * @return	array		Returns query array
	 */
	function makeQueryArray($table, $id, $addWhere="",$fieldList='*') {
		global $TYPO3_CONF_VARS,$TCA;
		
			// Set ORDER BY:
		$orderBy = ($TCA[$table]['ctrl']['sortby']) ? 'ORDER BY '.$TCA[$table]['ctrl']['sortby'] : $TCA[$table]['ctrl']['default_sortby'];
		if ($this->sortField)	{
			if (in_array($this->sortField,$this->makeFieldList($table,1)))	{
				$orderBy = 'ORDER BY '.$this->sortField;
				if ($this->sortRev)	$orderBy.=' DESC';
			}
		}

			// Set LIMIT:
		$limit = $this->iLimit ? ($this->firstElementNumber ? $this->firstElementNumber.',' : '').($this->iLimit+1) : '';

			// Filtering on displayable pages (permissions):
		$pC = ($table=='pages' && $this->perms_clause)?' AND '.$this->perms_clause:'';
		

			// Adding search constraints:
		$search = $this->makeSearchString($table);

			// Compiling query array:
		$queryParts = array(
			'SELECT' => $fieldList.',mm.uid_foreign AS parent',
			'FROM' =>   $table.$this->getCategoryJoinStmt($table,$id).$this->getPagesJoinStmt($table),
			'WHERE' => '1=1 '.$pC.
						t3lib_BEfunc::deleteClause($table).
						t3lib_BEfunc::versioningPlaceholderClause($table).
						' '.$addWhere.
						' '.$search,
			'GROUPBY' => '',
			//'ORDERBY' => $GLOBALS['TYPO3_DB']->stripOrderBy($orderBy),
			'ORDERBY' => '',
			'LIMIT' => $limit
		);
		

		
		/*
			if root (id = 0)
		*/
		
		//if($id == 0 && $table == $this->parentTable){

		
		if($id == 0){	//display all available records that are not categorized
			
			$queryParts['SELECT'] = $this->searchLevels ? $queryParts['SELECT'] : $fieldList;	
			$queryParts['FROM'] = $table.' LEFT JOIN '.$this->mm.' mm ON mm.localtable="'. $table .'" AND mm.uid_local='.$table.'.uid '.$this->getPagesJoinStmt($table);
			$queryParts['WHERE'] = 	($this->searchLevels ? '1=1' : 'mm.uid_foreign IS NULL ').
						t3lib_BEfunc::deleteClause($table).
						t3lib_BEfunc::versioningPlaceholderClause($table).
						' '.$addWhere.
						' '.$search;
		}
		
			// Return query:
		return $queryParts; 
	}


	/**
	 * Creates the listing of records from a single table
	 *
	 * @param	string		Table name
	 * @param	integer		Page id
	 * @param	string		List of fields to show in the listing. Pseudo fields will be added including the record header.
	 * @return	string		HTML table with the listing for the record.
	 */
	function getTable($table,$id,$rowlist)	{
		global $TCA;

			// Loading all TCA details for this table:
		t3lib_div::loadTCA($table);

			// Init
		$addWhere = '';
		$titleCol = $TCA[$table]['ctrl']['label'];
		$thumbsCol = $TCA[$table]['ctrl']['thumbnail'];
		$l10nEnabled = $TCA[$table]['ctrl']['languageField'] && $TCA[$table]['ctrl']['transOrigPointerField'] && !$TCA[$table]['ctrl']['transOrigPointerTable'];

			// Cleaning rowlist for duplicates and place the $titleCol as the first column always!
		$this->fieldArray=array();
		$this->fieldArray[] = $titleCol;	// Add title column
		if ($this->localizationView && $l10nEnabled)	{
			$this->fieldArray[] = '_LOCALIZATION_';
			$this->fieldArray[] = '_LOCALIZATION_b';
			$addWhere.=' AND (
				'.$TCA[$table]['ctrl']['languageField'].'<=0
				OR 
				'.$TCA[$table]['ctrl']['transOrigPointerField'].' = 0
			)';
		}
		if (!t3lib_div::inList($rowlist,'_CONTROL_'))	{
			$this->fieldArray[] = '_CONTROL_';
		}
		if ($this->showClipboard)	{
			$this->fieldArray[] = '_CLIPBOARD_';
		}
		//if (!$this->dontShowClipControlPanels)	{
		//	$this->fieldArray[]='_REF_';
		//}
		if ($this->searchLevels)	{
			$this->fieldArray[]='_PATH_';
		}
			// Cleaning up:
		$this->fieldArray=array_unique(array_merge($this->fieldArray,t3lib_div::trimExplode(',',$rowlist,1)));
		if ($this->noControlPanels)	{
			$tempArray = array_flip($this->fieldArray);
			unset($tempArray['_CONTROL_']);
			unset($tempArray['_CLIPBOARD_']);
			$this->fieldArray = array_keys($tempArray);
		}

			// Creating the list of fields to include in the SQL query:
		$selectFields = $this->fieldArray;
		$selectFields[] = 'uid';
		$selectFields[] = 'pid';
		if ($thumbsCol)	$selectFields[] = $thumbsCol;	// adding column for thumbnails
		if ($table=='pages')	{
			if (t3lib_extMgm::isLoaded('cms'))	{
				$selectFields[] = 'module';
				$selectFields[] = 'extendToSubpages';
			}
			$selectFields[] = 'doktype';
		}
		if (is_array($TCA[$table]['ctrl']['enablecolumns']))	{
			$selectFields = array_merge($selectFields,$TCA[$table]['ctrl']['enablecolumns']);
		}
		if ($TCA[$table]['ctrl']['type'])	{
			$selectFields[] = $TCA[$table]['ctrl']['type'];
		}
		if ($TCA[$table]['ctrl']['typeicon_column'])	{
			$selectFields[] = $TCA[$table]['ctrl']['typeicon_column'];
		}
		if ($TCA[$table]['ctrl']['versioningWS'])	{
			$selectFields[] = 't3ver_id';
			$selectFields[] = 't3ver_state';
			$selectFields[] = 't3ver_wsid';
			$selectFields[] = 't3ver_swapmode';		// Filtered out when pages in makeFieldList()
		}
		if ($l10nEnabled)	{
			$selectFields[] = $TCA[$table]['ctrl']['languageField'];
			$selectFields[] = $TCA[$table]['ctrl']['transOrigPointerField'];
		}
		
		
		if ($TCA[$table]['ctrl']['label_alt'])	{
			$selectFields = array_merge($selectFields,t3lib_div::trimExplode(',',$TCA[$table]['ctrl']['label_alt'],1));
		}

		
		$selectFields = array_unique($selectFields);		// Unique list!
		$selectFields = array_intersect($selectFields,$this->makeFieldList($table,1));		// Making sure that the fields in the field-list ARE in the field-list from TCA!
		//$selectFields[] = 'mm.uid_foreign AS parent';
		
		foreach($selectFields as $k=>$fn){
			$selectFields[$k] = $table.'.'.$fn;
		}
		

		$selFieldList = implode(',',$selectFields);		// implode it into a list of fields for the SQL-statement.

			// Create the SQL query for selecting the elements in the listing:
		$queryParts = $this->makeQueryArray($table, $id,$addWhere,$selFieldList);	// (API function from class.db_list.inc)
		
		
		
		$this->setTotalItems($queryParts);		// Finding the total amount of records on the page (API function from class.db_list.inc)

		
			// Init:
		$dbCount = 0;
		$out = '';
		
		$GLOBALS['TYPO3_DB']->store_lastBuiltQuery = TRUE;	

			// If the count query returned any number of records, we perform the real query, selecting records.
		if ($this->totalItems)	{
			$result = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($queryParts);
			
			$dbCount = $GLOBALS['TYPO3_DB']->sql_num_rows($result);
		}
		
		
		//if($table=='tx_categories'){
		//	debug($GLOBALS['TYPO3_DB']->debug_lastBuiltQuery);
		//}
		$LOISmode = $this->listOnlyInSingleTableMode && !$this->table;

			// If any records was selected, render the list:
		if ($dbCount)	{


				// Half line is drawn between tables:
			if (!$LOISmode)	{
				$theData = Array();
				if (!$this->table && !$rowlist)	{
					$theData[$titleCol] = '<img src="clear.gif" width="'.($GLOBALS['SOBE']->MOD_SETTINGS['bigControlPanel']?'230':'350').'" height="1" alt="" />';
					if (in_array('_CONTROL_',$this->fieldArray))	$theData['_CONTROL_']='';
					if (in_array('_CLIPBOARD_',$this->fieldArray))	$theData['_CLIPBOARD_']='';
				}
				$out.=$this->addelement(0,'',$theData,'class="c-table-row-spacer"',$this->leftMargin);
			}

				// Header line is drawn
			$theData = Array();
			
			//if single view is disabled we just display the table name and total number of records
			if ($this->disableSingleTableView)	{
				$theData[$titleCol] = '<span class="c-table">'.$GLOBALS['LANG']->sL($TCA[$table]['ctrl']['title'],1).'</span> ('.$this->totalItems.')';
			} else {
				//otherwise we link to the single table view
				
				$theData[$titleCol] = $this->linkWrapTable($table,'<span class="c-table">'.$GLOBALS['LANG']->sL($TCA[$table]['ctrl']['title'],1).'</span> ('.$this->totalItems.') <img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/'.($this->table?'minus':'plus').'bullet_list.gif','width="18" height="12"').' hspace="10" class="absmiddle" title="'.$GLOBALS['LANG']->getLL(!$this->table?'expandView':'contractView',1).'" alt="" />');
			}

				// CSH:
			$theData[$titleCol].= t3lib_BEfunc::cshItem($table,'',$this->backPath,'',FALSE,'margin-bottom:0px; white-space: normal;');

			if ($LOISmode)	{
				$out.='
					<tr>
						<td class="c-headLineTable" style="width:95%;">'.$theData[$titleCol].'</td>
					</tr>';

				if ($GLOBALS['BE_USER']->uc["edit_showFieldHelp"])	{
					$GLOBALS['LANG']->loadSingleTableDescription($table);
					if (isset($GLOBALS['TCA_DESCR'][$table]['columns']['']))	{
						$onClick = 'vHWin=window.open(\'view_help.php?tfID='.$table.'.\',\'viewFieldHelp\',\'height=400,width=600,status=0,menubar=0,scrollbars=1\');vHWin.focus();return false;';
						$out.='
					<tr>
						<td class="c-tableDescription">'.t3lib_BEfunc::helpTextIcon($table,'',$this->backPath,TRUE).$GLOBALS['TCA_DESCR'][$table]['columns']['']['description'].'</td>
					</tr>';
					}
				}
			} else {
				//$theUpIcon = ($table==$this->parentTable && $this->id) ? '<a href="'.htmlspecialchars($this->listURL($this->pageRow['pid'])).'" onclick="setHighlight('.$this->pageRow['pid'].')"><img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/i/pages_up.gif','width="18" height="16"').' title="'.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.upOneLevel',1).'" alt="" /></a>':'';
				$out.=$this->addelement(1,$theUpIcon,$theData,' class="c-headLineTable"','');
			}

			If (!$LOISmode)	{
					// Fixing a order table for sortby tables
				$this->currentTable = array();
				$currentIdList = array();
				$doSort = ($TCA[$table]['ctrl']['sortby'] && !$this->sortField);

				$prevUid = 0;
				$prevPrevUid = 0;
				$accRows = array();	// Accumulate rows here
				while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result))	{
					$accRows[] = $row;
					$currentIdList[] = $row['uid'];
					if ($doSort)	{
						if ($prevUid)	{
							$this->currentTable['prev'][$row['uid']] = $prevPrevUid;
							$this->currentTable['next'][$prevUid] = '-'.$row['uid'];
							$this->currentTable['prevUid'][$row['uid']] = $prevUid;
						}
						$prevPrevUid = isset($this->currentTable['prev'][$row['uid']]) ? -$prevUid : $row['pid'];
						$prevUid=$row['uid'];
					}
				}
				$GLOBALS['TYPO3_DB']->sql_free_result($result);

					// CSV initiated
				if ($this->csvOutput) $this->initCSV();

					// Render items:
				$this->CBnames=array();
				$this->duplicateStack=array();
				$this->eCounter=$this->firstElementNumber;

				$iOut = '';
				$cc = 0;
				foreach($accRows as $row)	{

						// Forward/Backwards navigation links:
					list($flag,$code) = $this->fwd_rwd_nav($table);
					$iOut.=$code;

						// If render item, increment counter and call function
					if ($flag)	{
						$cc++;
						$iOut.= $this->renderListRow($table,$row,$cc,$titleCol,$thumbsCol);

							// If localization view is enabled it means that the selected records are either default or All language and here we will not select translations which point to the main record:
						if ($this->localizationView && $l10nEnabled)	{

								// Look for translations of this record:
							$translations = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
								$selFieldList,
								$table,
								'pid='.$row['pid'].
									' AND '.$TCA[$table]['ctrl']['languageField'].'>0'.
									' AND '.$TCA[$table]['ctrl']['transOrigPointerField'].'='.intval($row['uid']).
									t3lib_BEfunc::deleteClause($table).
									t3lib_BEfunc::versioningPlaceholderClause($table)
							);

								// For each available translation, render the record:
							if (is_array($translations)) {
								foreach($translations as $lRow)	{
									if ($GLOBALS['BE_USER']->checkLanguageAccess($lRow[$TCA[$table]['ctrl']['languageField']]))	{
										$currentIdList[] = $lRow['uid'];
										$iOut.=$this->renderListRow($table,$lRow,$cc,$titleCol,$thumbsCol,18);
									}
								}
							}
						}
					}
						// Counter of total rows incremented:
					$this->eCounter++;
				}

					// The header row for the table is now created:
				$out.=$this->renderListHeader($table,$currentIdList);
			}

				// The list of records is added after the header:
			$out.=$iOut;

				// ... and it is all wrapped in a table:
			$out='



			<!--
				DB listing of elements:	"'.htmlspecialchars($table).'"
			-->
				<table border="0" cellpadding="0" cellspacing="0" class="typo3-dblist'.($LOISmode?' typo3-dblist-overview':'').'">
					'.$out.'
				</table>';

				// Output csv if...
			if ($this->csvOutput)	$this->outputCSV($table);	// This ends the page with exit.
		}
		
		$out =  $out . $this->paginationHTML;
		unset($this->paginationHTML);
		return $out;		

			// Return content:
		return $out;
	}
	
	
	
	
	/**
	 * Rendering the header row for a table
	 *
	 * @param	string		Table name
	 * @param	array		Array of the currectly displayed uids of the table
	 * @return	string		Header table row
	 * @access private
	 * @see getTable()
	 */
	function renderListHeader($table,$currentIdList)	{
		global $TCA, $LANG;

			// Init:
		$theData = Array();
		
		
		//debug($this->fieldArray);

			// Traverse the fields:
		foreach($this->fieldArray as $fCol)	{

				// Calculate users permissions to edit records in the table:

				//PROBLEM
			$permsEdit = $this->calcPerms & ($table=='pages'?2:16);

			switch((string)$fCol)	{
				case '_PATH_':			// Path
					$theData[$fCol] = '<i>['.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels._PATH_',1).']</i>';
				break;
				case '_REF_':			// References
					$theData[$fCol] = '<i>['.$LANG->sL('LLL:EXT:lang/locallang_mod_file_list.xml:c__REF_',1).']</i>';
				break;
				case '_LOCALIZATION_':			// Path
					$theData[$fCol] = '<i>['.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels._LOCALIZATION_',1).']</i>';
				break;
				case '_LOCALIZATION_b':			// Path
					$theData[$fCol] = $LANG->getLL('Localize',1);
				break;
				case '_CLIPBOARD_':		// Clipboard:
					$cells=array();

						// If there are elements on the clipboard for this table, then display the "paste into" icon:
					$elFromTable = $this->clipObj->elFromTable($table);
					if (count($elFromTable))	{
						$cells[]='<a href="'.htmlspecialchars($this->clipObj->pasteUrl($table,$this->id)).'" onclick="'.htmlspecialchars('return '.$this->clipObj->confirmMsg('pages',$this->pageRow,'into',$elFromTable)).'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath.PATH_txcategories_rel,'gfx/clip_pastesubref.gif','width="12" height="12"').' title="'.$LANG->getLL('insertrecordsintothiscategory',1).'" alt="" />'.
								'</a>';
					}

						// If the numeric clipboard pads are enabled, display the control icons for that:
					if ($this->clipObj->current!='normal')	{

							// The "select" link:
						$cells[]=$this->linkClipboardHeaderIcon('<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/clip_copy.gif','width="12" height="12"').' title="'.$LANG->getLL('clip_selectMarked',1).'" alt="" />',$table,'setCB');

							// The "edit marked" link:
						$editIdList = implode(',',$currentIdList);
						$editIdList = "'+editList('".$table."','".$editIdList."')+'";
						$params='&edit['.$table.']['.$editIdList.']=edit&disHelp=1';
						$cells[]='<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$this->backPath,-1)).'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/edit2.gif','width="11" height="12"').' title="'.$LANG->getLL('clip_editMarked',1).'" alt="" />'.
								'</a>';

							// The "Delete marked" link:
						$cells[]=$this->linkClipboardHeaderIcon('<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/garbage.gif','width="11" height="12"').' title="'.$LANG->getLL('clip_deleteMarked',1).'" alt="" />',$table,'delete',sprintf($LANG->getLL('clip_deleteMarkedWarning'),$LANG->sL($TCA[$table]['ctrl']['title'])));

						$cells[] = '<img'.t3lib_iconWorks::skinImg($this->backPath.PATH_txcategories_rel,'gfx/delref.gif','width="11" height="12"').' title="'.$LANG->getLL('removemarkedfromcategory',1).'" alt="" />';
						
							// The "Select all" link:
						$cells[]='<a href="#" onclick="'.htmlspecialchars('checkOffCB(\''.implode(',',$this->CBnames).'\'); return false;').'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/clip_select.gif','width="12" height="12"').' title="'.$LANG->getLL('clip_markRecords',1).'" alt="" />'.
								'</a>';
					} else {
						$cells[]='';
					}
					$theData[$fCol]=implode('',$cells);
				break;
				case '_CONTROL_':		// Control panel:
					if (!$TCA[$table]['ctrl']['readOnly'])	{

							// If new records can be created on this page, add links:
						if ($this->calcPerms&($table=='pages'?8:16) && $this->showNewRecLink($table))	{
							if ($table=="tt_content" && $this->newWizards)	{
									//  If mod.web_list.newContentWiz.overrideWithExtension is set, use that extension's create new content wizard instead:
								$tmpTSc = t3lib_BEfunc::getModTSconfig($this->pageinfo['uid'],'mod.web_list');
								$tmpTSc = $tmpTSc ['properties']['newContentWiz.']['overrideWithExtension'];
								$newContentWizScriptPath = $this->backPath.t3lib_extMgm::isLoaded($tmpTSc) ? (t3lib_extMgm::extRelPath($tmpTSc).'mod1/db_new_content_el.php') : 'sysext/cms/layout/db_new_content_el.php';

								$theData[$fCol]='<a href="#" onclick="'.htmlspecialchars('return jumpExt(\''.$newContentWizScriptPath.'?id='.$this->id.'\');').'">'.
												'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/new_'.($table=='pages'?'page':'el').'.gif','width="'.($table=='pages'?13:11).'" height="12"').' title="'.$LANG->getLL('new',1).'" alt="" />'.
												'</a>';
							} elseif ($table=='pages' && $this->newWizards)	{
								$theData[$fCol]='<a href="'.htmlspecialchars($this->backPath.'db_new.php?id='.$this->id.'&pagesOnly=1&returnUrl='.rawurlencode(t3lib_div::getIndpEnv('REQUEST_URI'))).'">'.
												'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/new_'.($table=='pages'?'page':'el').'.gif','width="'.($table=='pages'?13:11).'" height="12"').' title="'.$LANG->getLL('new',1).'" alt="" />'.
												'</a>';
							} else {
								$params='&edit['.$table.']['.$this->id.']=new';
								$theData[$fCol]='<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$this->backPath,-1)).'">'.
												'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/new_'.($table=='pages'?'page':'el').'.gif','width="'.($table=='pages'?13:11).'" height="12"').' title="'.$LANG->getLL('new',1).'" alt="" />'.
												'</a>';
							}
						}

							// If the table can be edited, add link for editing ALL SHOWN fields for all listed records:
						if ($permsEdit && $this->table && is_array($currentIdList))	{
							$editIdList = implode(',',$currentIdList);
							if ($this->clipNumPane()) $editIdList = "'+editList('".$table."','".$editIdList."')+'";
							$params='&edit['.$table.']['.$editIdList.']=edit&columnsOnly='.implode(',',$this->fieldArray).'&disHelp=1';
							$theData[$fCol].='<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$this->backPath,-1)).'">'.
											'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/edit2.gif','width="11" height="12"').' title="'.$LANG->getLL('editShownColumns',1).'" alt="" />'.
											'</a>';
						}
					}
				break;
				default:			// Regular fields header:
					$theData[$fCol]='';
					if ($this->table && is_array($currentIdList))	{

							// If the numeric clipboard pads are selected, show duplicate sorting link:
						if ($this->clipNumPane()) {
							$theData[$fCol].='<a href="'.htmlspecialchars($this->listURL('',-1).'&duplicateField='.$fCol).'">'.
											'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/select_duplicates.gif','width="11" height="11"').' title="'.$LANG->getLL('clip_duplicates',1).'" alt="" />'.
											'</a>';
						}

							// If the table can be edited, add link for editing THIS field for all listed records:
						if (!$TCA[$table]['ctrl']['readOnly'] && $permsEdit && $TCA[$table]['columns'][$fCol])	{
							$editIdList = implode(',',$currentIdList);
							if ($this->clipNumPane()) $editIdList = "'+editList('".$table."','".$editIdList."')+'";
							$params='&edit['.$table.']['.$editIdList.']=edit&columnsOnly='.$fCol.'&disHelp=1';
							$iTitle = sprintf($LANG->getLL('editThisColumn'),ereg_replace(':$','',trim($LANG->sL(t3lib_BEfunc::getItemLabel($table,$fCol)))));
							$theData[$fCol].='<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$this->backPath,-1)).'">'.
											'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/edit2.gif','width="11" height="12"').' title="'.htmlspecialchars($iTitle).'" alt="" />'.
											'</a>';
						}
					}
					$theData[$fCol].=$this->addSortLink($LANG->sL(t3lib_BEfunc::getItemLabel($table,$fCol,'<i>[|]</i>')),$fCol,$table);
				break;
			}
		}

			// Create and return header table row:
		return $this->addelement(1,'',$theData,' class="c-headLine"','');
	}
	
	
	
	
	
	
	
	
	


	/**
	 * Based on input query array (query for selecting count(*) from a table) it will select the number of records and set the value in $this->totalItems
	 *
	 * @param	array		Query array
	 * @return	void
	 * @see makeQueryArray()
	 */
	function setTotalItems($queryParts)	{
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
					'count(*)',
					$queryParts['FROM'],
					$queryParts['WHERE']
				);
		list($rCount) = $GLOBALS['TYPO3_DB']->sql_fetch_row($result);
		$this->totalItems = $rCount;
	}
	
	
	
	/**
	 * Creates the search box
	 *
	 * @param	boolean		If true, the search box is wrapped in its own form-tags
	 * @return	string		HTML for the search box
	 */
	function getSearchBox($formFields=1)	{

			// Setting form-elements, if applicable:
		$formElements=array('','');
		if ($formFields)	{
			$formElements=array('<form action="'.htmlspecialchars($this->listURL()).'" method="post">','</form>');
		}

			// Make level selector:
		$opt=array();
		$parts = explode('|',$GLOBALS['LANG']->sL('LLL:EXT:categories/locallang.xml:labels.enterSearchLevels'));
		while(list($kv,$label)=each($parts))	{
			$opt[] = '<option value="'.$kv.'"'.($kv==intval($this->searchLevels)?' selected="selected"':'').'>'.htmlspecialchars($label).'</option>';
		}
		$lMenu = '<select name="search_levels">'.implode('',$opt).'</select>';

			// Table with the search box:
		$content.= '
			'.$formElements[0].'

				<!--
					Search box:
				-->
				<table border="0" cellpadding="0" cellspacing="0" class="bgColor4" id="typo3-dblist-search">
					<tr>
						<td>'.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.enterSearchString',1).'<input type="text" name="search_field" value="'.htmlspecialchars($this->searchString).'"'.$GLOBALS['TBE_TEMPLATE']->formWidth(10).' /></td>
						<td>'.$lMenu.'</td>
						<td><input type="submit" name="search" value="'.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.search',1).'" /></td>
					</tr>
					<tr>
						<td colspan="3">'.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.showRecords',1).':<input type="text" name="showLimit" value="'.htmlspecialchars($this->showLimit?$this->showLimit:'').'"'.$GLOBALS['SOBE']->doc->formWidth(4).' /></td>
					</tr>
				</table>
			'.$formElements[1];
		//$content.=t3lib_BEfunc::cshItem('xMOD_csh_corebe', 'list_searchbox', $GLOBALS['BACK_PATH'],'|<br/>');
		return $content;
	}




	/**
	 * Creates the control panel for a single record in the listing.
	 *
	 * @param	string		The table
	 * @param	array		The record for which to make the control panel.
	 * @return	string		HTML table with the control panel (unless disabled)
	 */
	function makeControl($table,$row)	{
		global $TCA, $LANG, $SOBE;
		if ($this->dontShowClipControlPanels)	return '';

			// Initialize:
		t3lib_div::loadTCA($table);
		$cells=array();



		if ($table=='pages') {	
			$localCalcPerms = $GLOBALS['BE_USER']->calcPerms(t3lib_BEfunc::getRecord('pages',$row['uid']));
		} else {
			$localCalcPerms = $GLOBALS['BE_USER']->calcPerms(t3lib_BEfunc::getRecord('pages',$row['pid']));
		}
		
		// if table is pages then we check if we are allowed to edit the page
		// otherwise we check if we check if we are allowed to insert / edit content on the records pid		
		$permsEdit = ($table=='pages' && ($localCalcPerms&2)) || ($table!='pages' && ($localCalcPerms&16));

			// "Show" link (only pages and tt_content elements)
		if ($table=='pages' || $table=='tt_content')	{
			$params='&edit['.$table.']['.$row['uid'].']=edit';
			$cells[]='<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::viewOnClick($table=='tt_content'?$row['pid'].'#'.$row['uid']:$row['uid'], $this->backPath)).'">'.
					'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/zoom.gif','width="12" height="12"').' title="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.showPage',1).'" alt="" />'.
					'</a>';
		}

			// "Edit" link: ( Only if permissions to edit the page-record of the content of the parent page 
		if ($permsEdit)	{
			$params='&edit['.$table.']['.$row['uid'].']=edit';
			$cells[]='<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$this->backPath,-1)).'">'.
					'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/edit2'.(!$TCA[$table]['ctrl']['readOnly']?'':'_d').'.gif','width="11" height="12"').' title="'.$LANG->getLL('edit',1).'" alt="" />'.
					'</a>';
		}

			// "Move" wizard link for pages/tt_content elements:
		//if (($table=="tt_content" && $permsEdit) || ($table=='pages'))	{
		//	$cells[]='<a href="#" onclick="'.htmlspecialchars('return jumpExt(\''.$this->backPath.'move_el.php?table='.$table.'&uid='.$row['uid'].'\');').'">'.
		//			'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/move_'.($table=='tt_content'?'record':'page').'.gif','width="11" height="12"').' title="'.$LANG->getLL('move_'.($table=='tt_content'?'record':'page'),1).'" alt="" />'.
		//			'</a>';
		//}

			// If the extended control panel is enabled OR if we are seeing a single table:
		if ($SOBE->MOD_SETTINGS['bigControlPanel'] || $this->table)	{

				// "Info": (All records)
			$cells[]='<a href="#" onclick="'.htmlspecialchars('top.launchView(\''.$table.'\', \''.$row['uid'].'\'); return false;').'">'.
					'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/zoom2.gif','width="12" height="12"').' title="'.$LANG->getLL('showInfo',1).'" alt="" />'.
					'</a>';

				// If the table is NOT a read-only table, then show these links:
			if (!$TCA[$table]['ctrl']['readOnly'])	{

					// "Revert" link (history/undo)
	
				$cells[]='<a href="#" onclick="'.htmlspecialchars('return jumpExt(\''.$this->backPath.'show_rechis.php?element='.rawurlencode($table.':'.$row['uid']).'&returnUrl='.rawurlencode(PATH_txcategories_rel.'mod_list/index.php?id='.$this->id).'\',\'#latest\');').'">'.
						'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/history2.gif','width="13" height="12"').' title="'.$LANG->getLL('history',1).'" alt="" />'.
						'</a>';

					// Versioning:
				//if (t3lib_extMgm::isLoaded('version'))	{
				//	$vers = t3lib_BEfunc::selectVersionsOfRecord($table, $row['uid'], 'uid', $GLOBALS['BE_USER']->workspace);
				//	if (is_array($vers))	{	// If table can be versionized.
				//		if (count($vers)>1)	{
				//			$st = 'background-color: #FFFF00; font-weight: bold;';
				//			$lab = count($vers)-1;
				//		} else {
				//			$st = 'background-color: #9999cc; font-weight: bold;';
				//			$lab = 'V';
				//		}
				//
				//		$cells[]='<a href="'.htmlspecialchars($this->backPath.t3lib_extMgm::extRelPath('version')).'cm1/index.php?table='.rawurlencode($table).'&uid='.rawurlencode($row['uid']).'" style="'.htmlspecialchars($st).'">'.
				//				$lab.
				//				'</a>';
				//	}
				//}

					// "Edit Perms" link:
				//if ($table=='pages' && $GLOBALS['BE_USER']->check('modules','web_perm'))	{
				//	$cells[]='<a href="'.htmlspecialchars($this->backPath.'mod/web/perm/index.php?id='.$row['uid'].'&return_id='.$row['uid'].'&edit=1').'">'.
				//			'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/perm.gif','width="7" height="12"').' title="'.$LANG->getLL('permissions',1).'" alt="" />'.
				//			'</a>';
				//}

				
				/*
					Hmmmm... how could this be achieved in Categories>List module
					using the sorting_foreign field in the MM-table?
				*/
				
				//if ($TCA[$table]['ctrl']['sortby'] || $TCA[$table]['ctrl']['useColumnsForDefaultValues'])	{
				//	if (
				//		($table!='pages' && ($this->calcPerms&16)) || 	// For NON-pages, must have permission to edit content on this parent page
				//		($table=='pages' && ($this->calcPerms&8))		// For pages, must have permission to create new pages here.
				//		)	{
				//		if ($this->showNewRecLink($table))	{
				//			$params='&edit['.$table.']['.(-$row['uid']).']=new';
				//			$cells[]='<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$this->backPath,-1)).'">'.
				//					'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/new_'.($table=='pages'?'page':'el').'.gif','width="'.($table=='pages'?13:11).'" height="12"').' title="'.$LANG->getLL('new'.($table=='pages'?'Page':'Record'),1).'" alt="" />'.
				//					'</a>';
				//		}
				//	}
				//}


					// "Hide/Unhide" links:
				$hiddenField = $TCA[$table]['ctrl']['enablecolumns']['disabled'];
				if ($permsEdit && $hiddenField && $TCA[$table]['columns'][$hiddenField] && (!$TCA[$table]['columns'][$hiddenField]['exclude'] || $GLOBALS['BE_USER']->check('non_exclude_fields',$table.':'.$hiddenField)))	{
					if ($row[$hiddenField])	{
						$params='&data['.$table.']['.$row['uid'].']['.$hiddenField.']=0';
						$cells[]='<a href="#" onclick="'.htmlspecialchars('return jumpToUrl(\''.$SOBE->doc->issueCommand($params,0).'\');').'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_unhide.gif','width="11" height="10"').' title="'.$LANG->getLL('unHide'.($table=='pages'?'Page':''),1).'" alt="" />'.
								'</a>';
					} else {
						$params='&data['.$table.']['.$row['uid'].']['.$hiddenField.']=1';
						$cells[]='<a href="#" onclick="'.htmlspecialchars('return jumpToUrl(\''.$SOBE->doc->issueCommand($params,0).'\');').'">'.
								'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/button_hide.gif','width="11" height="10"').' title="'.$LANG->getLL('hide'.($table=='pages'?'Page':''),1).'" alt="" />'.
								'</a>';
					}
				}

					// "Delete" link:
				if (
					($table=='pages' && ($localCalcPerms&4)) || ($table!='pages' && ($localCalcPerms&16))
					)	{
					$params='&cmd['.$table.']['.$row['uid'].'][delete]=1';
					$cells[]='<a href="#" onclick="'.htmlspecialchars('if (confirm('.$LANG->JScharCode($LANG->getLL('deleteWarning').t3lib_BEfunc::referenceCount($table,$row['uid'],' (There are %s reference(s) to this record!)')).')) {jumpToUrl(\''.$SOBE->doc->issueCommand($params,0).'\');} return false;').'">'.
							'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/garbage.gif','width="11" height="12"').' title="'.$LANG->getLL('delete',1).'" alt="" />'.
							'</a>';

				}
				
				if($permsEdit && $this->id){	//if it's a real category and we are allowed to edit record 

					//$cells[] = '<img'.t3lib_iconWorks::skinImg($this->backPath.PATH_txcategories_rel,'gfx/delref.gif','width="11" height="12"').' title="'.$LANG->getLL('removefromcategory',1).'" alt="" />';
					$cells[]='<a href="#" onclick="'.htmlspecialchars('if (confirm('.$LANG->JScharCode(sprintf($LANG->getLL('confirmdeletefromcategory'),t3lib_div::fixed_lgd_cs($this->categoryRow['title'],$GLOBALS['BE_USER']->uc['titleLen']))).')) {jumpToUrl(\''.$this->backPath.PATH_txcategories_rel.'tce_categories.php?redirect='.rawurlencode(t3lib_div::linkThisScript()).'&vC='.$GLOBALS['BE_USER']->veriCode().'&prErr=1&uPT=1&data['.$table.']['.$row['uid'].'][*]=-'.$this->id.'\')} return false;').'">'.
							'<img'.t3lib_iconWorks::skinImg($this->backPath.PATH_txcategories_rel,'gfx/delref.gif','width="11" height="12"').' title="'.$LANG->getLL('removefromcategory',1).'" alt="" />'.
							'</a>';							
				
				}
				
				
				// add a 'Remove from category' link


			}
		}

			// If the record is edit-locked	by another user, we will show a little warning sign:
		if ($lockInfo=t3lib_BEfunc::isRecordLocked($table,$row['uid']))	{
			$cells[]='<a href="#" onclick="'.htmlspecialchars('alert('.$LANG->JScharCode($lockInfo['msg']).');return false;').'">'.
					'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/recordlock_warning3.gif','width="17" height="12"').' title="'.htmlspecialchars($lockInfo['msg']).'" alt="" />'.
					'</a>';
		}


			// Compile items into a DIV-element:
		return '
											<!-- CONTROL PANEL: '.$table.':'.$row['uid'].' -->
											<div class="typo3-DBctrl">'.implode('',$cells).'</div>';
	}
	

	/**
	 * Creates the clipboard panel for a single record in the listing.
	 *
	 * @param	string		The table
	 * @param	array		The record for which to make the clipboard panel.
	 * @return	string		HTML table with the clipboard panel (unless disabled)
	 */
	function makeClip($table,$row)	{
		global $TCA, $LANG;

			// Return blank, if disabled:
		if ($this->dontShowClipControlPanels)	return '';
		$cells=array();


			// Return blank, if disabled:
			// Whether a numeric clipboard pad is active or the normal pad we will see different content of the panel:
		if ($this->clipObj->current=='normal')	{	// For the "Normal" pad:

				// Show copy/cut icons:
			$isSel = (string)$this->clipObj->isSelected($table,$row['uid']);
			$cells[]='<a href="#" onclick="'.htmlspecialchars('return jumpSelf(\''.$this->clipObj->selUrlDB($table,$row['uid'],1,($isSel=='copy'),array('returnUrl'=>'')).'\');').'">'.
					'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/clip_copy'.($isSel=='copy'?'_h':'').'.gif','width="12" height="12"').' title="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:cm.copy',1).'" alt="" />'.
					'</a>';
			$cells[]='<a href="#" onclick="'.htmlspecialchars('return jumpSelf(\''.$this->clipObj->selUrlDB($table,$row['uid'],0,($isSel=='cut'),array('returnUrl'=>'')).'\');').'">'.
					'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/clip_cut'.($isSel=='cut'?'_h':'').'.gif','width="12" height="12"').' title="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:cm.cut',1).'" alt="" />'.
					'</a>';

		} else {	// For the numeric clipboard pads (showing checkboxes where one can select elements on/off)

				// Setting name of the element in ->CBnames array:
			$n=$table.'|'.$row['uid'];
			$this->CBnames[]=$n;

				// Check if the current element is selected and if so, prepare to set the checkbox as selected:
			$checked = ($this->clipObj->isSelected($table,$row['uid'])?' checked="checked"':'');

				// If the "duplicateField" value is set then select all elements which are duplicates...
			if ($this->duplicateField && isset($row[$this->duplicateField]))	{
				$checked='';
				if (in_array($row[$this->duplicateField], $this->duplicateStack))	{
					$checked=' checked="checked"';
				}
				$this->duplicateStack[] = $row[$this->duplicateField];
			}

				// Adding the checkbox to the panel:
			$cells[]='<input type="hidden" name="CBH['.$n.']" value="0" /><input type="checkbox" name="CBC['.$n.']" value="1" class="smallCheckboxes"'.$checked.' />';
		}

			// Now, looking for selected elements from the current table:
		$elFromTable = $this->clipObj->elFromTable($table);
		if (count($elFromTable) && $TCA[$table]['ctrl']['sortby'])	{	// IF elements are found and they can be individually ordered, then add a "paste after" icon:
			$cells[]='<a href="'.htmlspecialchars($this->clipObj->pasteUrl($table,-$row['uid'])).'" onclick="'.htmlspecialchars('return '.$this->clipObj->confirmMsg($table,$row,'after',$elFromTable)).'">'.
					'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/clip_pasteafter.gif','width="12" height="12"').' title="'.$LANG->getLL('clip_pasteAfter',1).'" alt="" />'.
					'</a>';
		}

			// Now, looking for elements in general:
		$elFromTable = $this->clipObj->elFromTable('');
		if ($table=='pages' && count($elFromTable))	{
			$cells[]='<a href="'.htmlspecialchars($this->clipObj->pasteUrl('',$row['uid'])).'" onclick="'.htmlspecialchars('return '.$this->clipObj->confirmMsg($table,$row,'into',$elFromTable)).'">'.
					'<img'.t3lib_iconWorks::skinImg($this->backPath,'gfx/clip_pasteinto.gif','width="12" height="12"').' title="'.$LANG->getLL('clip_pasteInto',1).'" alt="" />'.
					'</a>';
		}

			// Compile items into a DIV-element:
		return '							<!-- CLIPBOARD PANEL: '.$table.':'.$row['uid'].' -->
											<div class="typo3-clipCtrl">'.implode('',$cells).'</div>';
	}	
	
	/**
	 * Returns the title (based on $code) of a record (from table $table) with the proper link around (that is for 'pages'-records a link to the level of that record...)
	 *
	 * @param	string		Table name
	 * @param	integer		Item uid
	 * @param	string		Item title (not htmlspecialchars()'ed yet)
	 * @param	array		Item row
	 * @return	string		The item title. Ready for HTML output (is htmlspecialchars()'ed)
	 */
	function linkWrapItems($table,$uid,$code,$row)	{
		global $TCA, $LANG;

		$origCode = $code;

			// If the title is blank, make a "no title" label:
		if (!strcmp($code,'')) {
			$code = '<i>['.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_core.php:labels.no_title',1).']</i> - '.htmlspecialchars(t3lib_div::fixed_lgd_cs(t3lib_BEfunc::getRecordTitle($table,$row),$GLOBALS['BE_USER']->uc['titleLen']));
		} else {
			$code = htmlspecialchars(t3lib_div::fixed_lgd_cs($code,$this->fixedL));
		}

		switch((string)$this->clickTitleMode)	{
			case 'edit':
					// If the listed table is 'pages' we have to request the permission settings for each page:
				if ($table=='pages')	{
					$localCalcPerms = $GLOBALS['BE_USER']->calcPerms(t3lib_BEfunc::getRecord('pages',$row['uid']));
					$permsEdit = $localCalcPerms&2;
				} else {
					$permsEdit = $this->calcPerms&16;
				}

					// "Edit" link: ( Only if permissions to edit the page-record of the content of the parent page ($this->id)
				if ($permsEdit)	{
					$params='&edit['.$table.']['.$row['uid'].']=edit';
					$code = '<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::editOnClick($params,$this->backPath,-1)).'" title="'.$LANG->getLL('edit',1).'">'.
							$code.
							'</a>';
				}
			break;
			case 'show':
					// "Show" link (only pages and tt_content elements)
				if ($table=='pages' || $table=='tt_content')	{
					$code = '<a href="#" onclick="'.htmlspecialchars(t3lib_BEfunc::viewOnClick($table=='tt_content'?$this->id.'#'.$row['uid']:$row['uid'])).'" title="'.$LANG->sL('LLL:EXT:lang/locallang_core.php:labels.showPage',1).'">'.
							$code.
							'</a>';
				}
			break;
			case 'info':
				// "Info": (All records)
				$code = '<a href="#" onclick="'.htmlspecialchars('top.launchView(\''.$table.'\', \''.$row['uid'].'\'); return false;').'" title="'.$LANG->getLL('showInfo',1).'">'.
					$code.
					'</a>';
			break;
			default:
					// Output the label now:
				if ($table==$this->parentTable)	{
					$code = '<a href="'.htmlspecialchars($this->listURL($uid,'')).'" onclick="setHighlight('.$uid.')">'.$code.'</a>';
				} else {
					$code = $this->linkUrlMail($code,$origCode);
				}
			break;
		}

		return $code;
	}


	function fwd_rwd_HTML($type,$pointer,$table='')	{
		$content = parent::fwd_rwd_HTML($type,$pointer,$table='');
		
		$tParam = $table ? '&table='.rawurlencode($table) : '';
		$this->paginationHTML = '';
		if ($type == 'rwd')	$pointer = $pointer - $this->iLimit;
		$total_pages = ceil($this->totalItems/$this->iLimit);
		if ( $total_pages <= 1){
			return '';
		}
	
		$on_page = floor($pointer/$this->iLimit) + 1;
		
		$this->paginationHTML = $this->_paginationAdvanced($total_pages, 5, $on_page, $tParam);
		
		return '';
	}
	
	


	/**
	 * Returns the path for a certain pid
	 * The result is cached internally for the session, thus you can call this function as much as you like without performance problems.
	 *
	 * @param	integer		The page id for which to get the path
	 * @return	string		The path.
	 */
	function recPath($parent)	{
		if (!isset($this->recPath_cache[$parent])) {
			$this->recPath_cache[$parent] = tx_categories_div::getCategoryPath($parent);
		}
		return $this->recPath_cache[$parent];
	}
	
	
	
	/**
	 * Rendering a single row for the list
	 *
	 * @param	string		Table name
	 * @param	array		Current record
	 * @param	integer		Counter, counting for each time an element is rendered (used for alternating colors)
	 * @param	string		Table field (column) where header value is found
	 * @param	string		Table field (column) where (possible) thumbnails can be found
	 * @param	integer		Indent from left.
	 * @return	string		Table row for the element
	 * @access private
	 * @see getTable()
	 */
	function renderListRow($table,$row,$cc,$titleCol,$thumbsCol,$indent=0)	{
		
		$iOut = '';

		if (strlen($this->searchString))	{	// If in search mode, make sure the preview will show the correct page
			$id_orig = $this->id;
			$this->id = $row['pid'];
		}

			// In offline workspace, look for alternative record:
		t3lib_BEfunc::workspaceOL($table, $row, $GLOBALS['BE_USER']->workspace);

			// Background color, if any:
		$row_bgColor=
			$this->alternateBgColors ?
			(($cc%2)?'' :' class="db_list_alt"') :
			'';

			// Overriding with versions background color if any:
		$row_bgColor = $row['_CSSCLASS'] ? ' class="'.$row['_CSSCLASS'].'"' : $row_bgColor;

			// Incr. counter.
		$this->counter++;

			// The icon with link
		$alttext = t3lib_BEfunc::getRecordIconAltText($row,$table);
		$iconImg = t3lib_iconWorks::getIconImage($table,$row,$this->backPath,'title="'.htmlspecialchars($alttext).'"'.($indent ? ' style="margin-left: '.$indent.'px;"' : ''));
		$theIcon = $this->clickMenuEnabled ? $this->wrapClickMenuOnIcon($iconImg,$table,$row['uid'],1,'&dummy=1&category='.$this->id) : $iconImg;

			// Preparing and getting the data-array
		$theData = Array();
		foreach($this->fieldArray as $fCol)	{
			if ($fCol==$titleCol)	{
				$recTitle = t3lib_BEfunc::getRecordTitle($table,$row,FALSE,TRUE);
				$theData[$fCol] = $this->linkWrapItems($table,$row['uid'],$recTitle,$row);
			} elseif ($fCol=='pid') {
				$theData[$fCol]=$row[$fCol];
			} elseif ($fCol=='_PATH_') {
				$theData[$fCol]=$this->recPath($row['parent']);
			} elseif ($fCol=='_REF_') {
				$theData[$fCol]=$this->makeRef($table,$row['uid']);
			} elseif ($fCol=='_CONTROL_') {
				$theData[$fCol]=$this->makeControl($table,$row);
			} elseif ($fCol=='_CLIPBOARD_') {
				$theData[$fCol]=$this->makeClip($table,$row);
#				$t3lib_transl8tools = new t3lib_transl8tools;
#				$theData[$fCol].=t3lib_div::view_array($t3lib_transl8tools->translationInfo($table,$row['uid']));
			} elseif ($fCol=='_LOCALIZATION_') {
				list($lC1, $lC2) = $this->makeLocalizationPanel($table,$row);
				$theData[$fCol] = $lC1;
				$theData[$fCol.'b'] = $lC2;
			} elseif ($fCol=='_LOCALIZATION_b') {
				// Do nothing, has been done above.
			} else {
				$theData[$fCol] = $this->linkUrlMail(htmlspecialchars(t3lib_BEfunc::getProcessedValueExtra($table,$fCol,$row[$fCol],100,$row['uid'])),$row[$fCol]);
			}
		}

		if (strlen($this->searchString))	{	// Reset the ID if it was overwritten
			$this->id = $id_orig;
		}

			// Add row to CSV list:
		if ($this->csvOutput) $this->addToCSV($row,$table);

			// Create element in table cells:
		$iOut.=$this->addelement(1,$theIcon,$theData,$row_bgColor);

			// Render thumbsnails if a thumbnail column exists and there is content in it:
		if ($this->thumbs && trim($row[$thumbsCol]))	{
			$iOut.=$this->addelement(4,'', Array($titleCol=>$this->thumbCode($row,$table,$thumbsCol)),$row_bgColor);
		}

			// Finally, return table row element:
		return $iOut;
	}
	
	
	
	
	
	function _paginationAdvanced($total_pages, $show = 5, $on_page, $tParam = '') {
		$disp = floor($show / 2);
		if ( $on_page <= $disp) {

			$low  = ( ($disp - $on_page) > 0 ) ? ($disp - $on_page) : 1;
			$high = ($low + $show) - 1;

		} elseif ( ($on_page + $disp) > $total_pages) {

			$high = $total_pages;
			$low = ($total_pages - $show) + 1;
		} else {
			$low  = ($on_page - $disp);
			$high = ($on_page + $disp);
		}
	    
		$prev  = (($on_page - 1) > 0) ? '<a href="'.$this->listURL().'&pointer='.(($on_page-2)*$this->iLimit ). $tParam.'">['.$GLOBALS['LANG']->sL('LLL:EXT:categories/locallang.xml:prev').']</a>' : '<span style="color: #666;">['.$GLOBALS['LANG']->sL('LLL:EXT:categories/locallang.xml:prev').']</span>';
	    
		$next  = (($on_page + 1) <= $total_pages) ? ' <a href="'.$this->listURL().'&pointer='.(($on_page)*$this->iLimit ). $tParam.'">['.$GLOBALS['LANG']->sL('LLL:EXT:categories/locallang.xml:next').']</a> ' : '<span style="color: #666;">['.$GLOBALS['LANG']->sL('LLL:EXT:categories/locallang.xml:next').']</span>';    
	
		$first =  ($on_page > 1)  ? '<a href="'.$this->listURL().'&pointer=0'.$tParam.'">['.$GLOBALS['LANG']->sL('LLL:EXT:categories/locallang.xml:first').']</a>' : '<span style="color: #666;">['.$GLOBALS['LANG']->sL('LLL:EXT:categories/locallang.xml:first').']</span>';    

		$last = $on_page <> $total_pages ? '<a href="'.$this->listURL().'&pointer='.(($total_pages-1)*$this->iLimit).$tParam.'">['.$GLOBALS['LANG']->sL('LLL:EXT:categories/locallang.xml:last').']</a>' : '<span style="color: #666;">['.$GLOBALS['LANG']->sL('LLL:EXT:categories/locallang.xml:last').']</span>';
	    
	    
		$content = '
	    <script language="JavaScript" type="text/JavaScript">
<!--
function jumpPagination(){
  	page = document.getElementById(\'quickpage\').value;
  	if (page > '.$total_pages.')	page = '.$total_pages.';
  	page = (page-1) * '.$this->iLimit.';
  	
	eval("self.location=\''.$this->listURL().$tParam.'&pointer="+page+"\'");
}
//-->
</script>
	    
	    <div style="padding: 5px 0;">';

		$content .= $prev ."&nbsp;";

		foreach (range($low, $high) as $i){
			if ($i > $total_pages){
				$content .= '';
			} elseif ($i <= 0){
				$content .= '';
			} else {
				$href = $this->listURL().'&pointer='.( ( $i - 1 ) * $this->iLimit ).$tParam;
				$from = ( $i - 1 ) * $this->iLimit;
				$to = ($this->totalItems > ($from + $this->iLimit) ) ?  ($from + $this->iLimit) : $this->totalItems;
				$txt = '[' . ($from + 1 ). '-' . $to . ']';
				$content .= ( $i == $on_page ) ?  '<strong>'.$txt.'</strong> '  : '<a href="'.htmlspecialchars($href).'">' . $txt . '</a> ';
			}                 
		}  
	    
		$content .= ''. $next;        
		$content .= '<br/>';

		$showing = sprintf($GLOBALS['LANG']->sL('LLL:EXT:categories/locallang.xml:showing'), '<strong>'. $on_page .'</strong>', $total_pages);
	    
		$content .= $first .' | '. $showing .' | '. $last .' &nbsp;&nbsp;&nbsp;'.$GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_browse_links.xml:page').' #<input id="quickpage" size="3" name="quickpage" /> <a href="javascript:jumpPagination();">'.$GLOBALS['LANG']->sL('LLL:EXT:categories/locallang.xml:goto').'</a></div>';
    
		return $content;        
	}

	function listURL($altId='',$table=-1,$exclList='')	{
		$url  = parent::listURL($altId,$table,$exclList);
		$url .= ($this->firstElementNumber?'&pointer='.rawurlencode($this->firstElementNumber):'');
		return $url;
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
	 * Inits hooks for the record list
	 */
	function initHooks(){
		
		if(is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['categories']['recordList'])){
			foreach($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['categories']['recordList'] as $classRef){
				$this->hookObjectsArr[] = &t3lib_div::getUserObj($classRef);
			}
		}
		
	}	
}


?>
