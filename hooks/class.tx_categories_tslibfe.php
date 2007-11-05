<?php


class tx_categories_tslibfe{


	function determineId_PostProc($params,$pObj){
	
		$params['pObj']->applicationData['tx_categories'] = t3lib_div::getUserObj('EXT:categories/lib/class.tx_categories_fe.php:tx_categories_fe');
	
	}


}






?>