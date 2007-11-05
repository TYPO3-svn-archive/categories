_filterCombo = function(input,target,options){

	var me = this;		
	var $input = jQuery(input).attr("autocomplete", "off");	
	var $target = jQuery(target);	
	
	var cache = {};
	var prev = "";
	var shift = 0;
	
	$input.keydown(function(e) {
		handleKeyDown(e);
	});
	
	$input.keyup(function(e) {
		handleKeyUp(e);
	});
	
	function handleKeyDown(e){

		switch(e.keyCode) {
			
			case 16:
				//alert(e.keyCode);
				shift=1;
				break;
			
			case 38: //up
				e.preventDefault();
				moveUp();
				break;
			case 40: // down
				e.preventDefault();
				moveDown();
				break;
			case 9:  // tab
				e.preventDefault();
				break;
			case 13: // return
				e.preventDefault();
				selectOption();
				break;
			case 8:  //backspace 				
			default:
				onChange(e);
				break;
		}		
	};

	function handleKeyUp(e){
		switch(e.keyCode) {
			case 16:
				//alert(e.keyCode);
				shift = 0;
			default:
				break;
		}
	}
	
	
	
	function moveUp(){

		var len = $target.get()[0].length;
		var selIndex = $target.get()[0].selectedIndex;

		if(selIndex == undefined){
			$target.get()[0].selectedIndex = 0;	
			selIndex = 0;
		}
		if(len > 0 && selIndex > 0){
			$target.get()[0].selectedIndex = selIndex - 1;
		}		
	}
	
	
	
	function moveDown(){
		var len = $target.get()[0].length;		

		var selIndex = $target.get()[0].selectedIndex;

		if(selIndex == undefined){
			$target.get()[0].selectedIndex = 0;			
			selIndex = 0;
		}
		
		if(len > 0 && selIndex < len){
			$target.get()[0].selectedIndex = selIndex + 1
		}
	}
	
	
	function onChange(e) {
		
		//top.debugObj(e);

		var v = $input.val();
		
		//alert(v);
		
		//if the backspace key is pressed we remove one character from the end of the word
		if(e.keyCode == 8){
			v = v.substr(0,v.length-1);
		}
		
		if(e.keyCode >= 48 && e.keyCode <= 90){ 
			if(shift==1){	//if the shift key has been pressed
				v += String.fromCharCode(e.keyCode);	
			} else {	//otherwise it's lowercase
				v += String.fromCharCode(e.keyCode + 32);
			}
		}
		
		//nothing is changed so we don't need to go any further
		if (v == prev) return;
		
		//show the "busy" indicator		
		showIndicator()
		prev = v;
		fetchOptions(v);
	};

	function showIndicator(){
		$input.css('background-image','url(../typo3conf/ext/categories/res/indicator.gif)');
	}
	
	function hideIndicator(){
		$input.css('background-image','none');
	}
	
	
	function selectOption(){

		var len = $target.get()[0].length;		

		var selIndex = $target.get()[0].selectedIndex;
		
		if(selIndex == undefined){
			$target.get()[0].selectedIndex = 0;	
			selIndex = 0;
		}

		if(len > 0){
			$target.trigger('onchange');
		}		
		
	}
	
	
	function fetchOptions(q){

		var data = loadFromCache(q);

		if(data) {

			displayOptions(data);
			
		} else {
			
			jQuery.ajax({
				type: "GET",
				url: options.url,
				data: "sword=" + q,
				dataType: "html",
				success: function(data){
					//alert(data);
					addToCache(q,data)
					displayOptions(data);
				}
			})
		}

	};
	
	function displayOptions(data){
		jQuery(target).html(data);
		//jQuery(target).attr('selectedIndex',0);
		$target.get()[0].selectedIndex=0;
		hideIndicator();
	};
	
	
	function addToCache(q, data) {
		
		if (!data || !q || !options.cacheLength) return;
		
		if (!cache.length || cache.length > options.cacheLength) {
			
			cache = {};
			
			cache.length = 1; // we know we're adding something
			
		} else if (!cache[q]) {
			
			cache.length++;
			
		}
		
		cache[q] = data;
		

		
	};	
	
	
	
	function loadFromCache(q) {
		
		if (!q) return null;
		
		if (cache[q]) return cache[q];
		
		return null;
		
	};	
}

jQuery.fn.filterCombo = function(url,target,options) {

	//top.debugObj(this);
	
	// Make sure options exists
	options = options || {};
	// Set url as option
	options.url = url;
	options.minChars = options.minChars || 1;
	options.cacheLength = options.cacheLength || 10;
	

	this.each(function() {
		var input = this;
		new _filterCombo(input,target,options);
	});

	// Don't break the chain
	return this;
	
};