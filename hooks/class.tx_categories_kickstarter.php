<?php

class tx_categories_kickstarter{

	
	function addModuleFunction(&$lines, &$pObj){
	
		// lines is an array of strings that will be concatenated right 
		// after this function has been run by the kickstarter.
		// we need to find the element in the array which has a string that contains 
		// a selectorbox.
		
		//debug($lines);
		
		foreach($lines as $k=>$v){
			if(strstr($v,'<select')){
				$lines[$k] = $this->modifySelectBox($v,$pObj);
			}
		}
		
		return $lines;
	}
	
	
	function modifySelectBox($str,$pObj){
		
		$out = array();
		
		//first we extract the option-tags
		preg_match('/(.*)(<select[^>]*>)(.*)(<\/select>)(.*)/',$str,$matches);
		
		//index 1 contains some html that we want to keep
		$out[] = $matches[1];
		
		//now we split all options
		preg_match_all('/<option[^>]*>[^<]*<\/option>/',$matches[3],$splittedoptions);
		$optValues = array();
		
		//we create a new array with option values
		foreach($splittedoptions[0] as $option){
			preg_match('/<option value="([^"]*)"[^>]*>([^<]*).*/',$option,$valuelabels);
			$optValues[$valuelabels[1]] = str_replace('&gt;','>',$valuelabels[2]);
		}
		//adds some new options
		$optValues['txcategoriesMain_txcategoriesInfo'] = 'Categories>Info';
		$optValues['txcategoriesMain_txcategoriesFunc'] = 'Categories>Functions';
		
		
		$action = explode(':',$pObj->wizard->modData['wizAction']);
		$piConf = $pObj->wizard->wizArray[$pObj->sectionID][$action[1]];
		$ffPrefix='['.$pObj->sectionID.']['.$action[1].']';
		
		$out[] = $pObj->renderSelectBox($ffPrefix.'[position]',$piConf['position'],$optValues);
		
		
		$out[] = $matches[5];

		return implode("\n",$out);
	}
	

}

?>
