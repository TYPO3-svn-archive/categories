<?php



class tx_categories_templavoilamod1{

	function renderTopToolbar($params,$pObj){
		/*
		global $BACK_PATH;
		
		$out = '
		
		<script src="'.$BACK_PATH.PATH_txcategories_rel.'res/jquery.js" type="text/javascript"></script>
		<script type="text/javascript">
		jQuery(function($) {


			if (
				top && 
				top.content && 
				top.content.nav_frame && 
				top.content.nav_frame.document && 
				top.content.nav_frame.document.body && 
				top.content.nav_frame.navFrameId && 
				top.content.nav_frame.navFrameId == "txcategoriesMain"
			)	{	
				
				// use this to add some content to the templavoila page module
				// if the navframe is the category tree
				
				$("body").append("<h1>Hello world</h1>");				
			}
		});
		</script>
		
		';
		
		
		return $out;
		*/
	
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/hooks/class.tx_categories_templavoilamod1.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/categories/hooks/class.tx_categories_templavoilamod1.php']);
}

?>