<?php

########################################################################
# Extension Manager/Repository config file for ext: "categories"
#
# Auto generated 09-11-2007 23:28
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'Categories',
	'description' => 'This extension adds general categorization features to the framework of TYPO3.',
	'category' => 'misc',
	'author' => 'Mads Brunn',
	'author_email' => 'mads@typoconsult.dk',
	'shy' => '',
	'dependencies' => 'cms',
	'conflicts' => '',
	'priority' => '',
	'module' => 'mod_main,mod_list,mod_func,mod_info,mod_import,mod_freevoc,mod_browsecat',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => 'be_users,be_groups',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.0.0',
	'constraints' => array(
		'depends' => array(
			'cms' => '',
		),
		'conflicts' => array(
		),
		'suggests' => array(
		),
	),
	'_md5_values_when_last_written' => 'a:108:{s:9:"ChangeLog";s:4:"66ee";s:10:"README.txt";s:4:"ee2d";s:21:"ext_conf_template.txt";s:4:"1d67";s:12:"ext_icon.gif";s:4:"ca1b";s:17:"ext_localconf.php";s:4:"b733";s:14:"ext_tables.php";s:4:"cf60";s:14:"ext_tables.sql";s:4:"d7d6";s:13:"locallang.xml";s:4:"1eaa";s:25:"locallang_csh_modfunc.xml";s:4:"35f9";s:27:"locallang_csh_modimport.xml";s:4:"940c";s:25:"locallang_csh_modinfo.xml";s:4:"fd8f";s:25:"locallang_csh_modlist.xml";s:4:"940c";s:31:"locallang_csh_tx_categories.xml";s:4:"c42a";s:16:"locallang_db.xml";s:4:"4fdd";s:7:"tca.php";s:4:"a8e0";s:18:"tce_categories.php";s:4:"08d8";s:8:"todo.txt";s:4:"9738";s:21:"dev/ipsvhierarchy.xml";s:4:"ebbd";s:12:"dev/ipvs.zip";s:4:"1306";s:12:"dev/todo.txt";s:4:"4c4a";s:11:"dev/uwl.txt";s:4:"1a66";s:21:"gfx/category_blue.gif";s:4:"1f23";s:22:"gfx/category_green.gif";s:4:"e923";s:21:"gfx/category_grey.gif";s:4:"e8b7";s:21:"gfx/category_icon.gif";s:4:"f003";s:20:"gfx/category_red.gif";s:4:"0eeb";s:23:"gfx/category_violet.gif";s:4:"dc84";s:23:"gfx/category_yellow.gif";s:4:"1f07";s:24:"gfx/clip_pastesubref.gif";s:4:"9260";s:16:"gfx/clip_ref.gif";s:4:"6812";s:18:"gfx/clip_ref_h.gif";s:4:"ac5e";s:14:"gfx/delref.gif";s:4:"81e0";s:12:"gfx/func.gif";s:4:"2d41";s:12:"gfx/func.png";s:4:"08ac";s:16:"gfx/func_old.gif";s:4:"df91";s:17:"gfx/func_old2.gif";s:4:"4130";s:14:"gfx/import.gif";s:4:"abb1";s:14:"gfx/import.old";s:4:"492a";s:14:"gfx/import.png";s:4:"a349";s:18:"gfx/import_old.gif";s:4:"e0e2";s:12:"gfx/info.gif";s:4:"2723";s:12:"gfx/info.png";s:4:"c4cb";s:16:"gfx/info_old.gif";s:4:"1fb8";s:12:"gfx/list.gif";s:4:"adc5";s:12:"gfx/list.old";s:4:"adc5";s:12:"gfx/list.png";s:4:"1b00";s:16:"gfx/list_old.gif";s:4:"4fc3";s:17:"gfx/list_old2.gif";s:4:"0c64";s:12:"gfx/main.gif";s:4:"ca1b";s:21:"gfx/makelocalcopy.gif";s:4:"ce99";s:41:"hooks/class.tx_categories_kickstarter.php";s:4:"f574";s:43:"hooks/class.tx_categories_modnewelement.php";s:4:"bcfb";s:37:"hooks/class.tx_categories_tceform.php";s:4:"4d49";s:37:"hooks/class.tx_categories_tcemain.php";s:4:"74b5";s:45:"hooks/class.tx_categories_templavoilamod1.php";s:4:"b73c";s:37:"hooks/class.tx_categories_tslibfe.php";s:4:"373c";s:31:"lib/class.tx_categories_api.php";s:4:"bccc";s:34:"lib/class.tx_categories_befunc.php";s:4:"2ef0";s:35:"lib/class.tx_categories_browser.php";s:4:"0f91";s:37:"lib/class.tx_categories_clipboard.php";s:4:"bd26";s:30:"lib/class.tx_categories_cm.php";s:4:"80b1";s:30:"lib/class.tx_categories_db.php";s:4:"5873";s:31:"lib/class.tx_categories_div.php";s:4:"3516";s:30:"lib/class.tx_categories_fe.php";s:4:"c73b";s:32:"lib/class.tx_categories_menu.php";s:4:"1acc";s:35:"lib/class.tx_categories_navtree.php";s:4:"efd0";s:33:"lib/class.tx_categories_perms.php";s:4:"704c";s:39:"lib/class.tx_categories_positionmap.php";s:4:"6c3a";s:38:"lib/class.tx_categories_recordlist.php";s:4:"250a";s:31:"lib/class.tx_categories_tce.php";s:4:"d912";s:36:"lib/class.tx_categories_treebase.php";s:4:"30f8";s:36:"lib/class.tx_categories_treeview.php";s:4:"f0ca";s:24:"lib/tx_categories_fe.php";s:4:"cbee";s:22:"mod_browsecat/conf.php";s:4:"5e94";s:23:"mod_browsecat/index.php";s:4:"7e80";s:20:"mod_freevoc/conf.php";s:4:"b88b";s:21:"mod_freevoc/index.php";s:4:"49e9";s:40:"mod_func/class.tx_categories_modfunc.php";s:4:"4eeb";s:17:"mod_func/conf.php";s:4:"7b71";s:18:"mod_func/index.php";s:4:"93f9";s:22:"mod_func/locallang.xml";s:4:"f08c";s:26:"mod_func/locallang_mod.xml";s:4:"1ef5";s:41:"mod_import/class.tx_categories_import.php";s:4:"9003";s:19:"mod_import/conf.php";s:4:"09e1";s:20:"mod_import/index.php";s:4:"7719";s:24:"mod_import/locallang.xml";s:4:"1ca2";s:40:"mod_info/class.tx_categories_modinfo.php";s:4:"37ae";s:17:"mod_info/conf.php";s:4:"4213";s:18:"mod_info/index.php";s:4:"4e43";s:22:"mod_info/locallang.xml";s:4:"12e2";s:26:"mod_info/locallang_mod.xml";s:4:"83ee";s:17:"mod_list/conf.php";s:4:"2c93";s:18:"mod_list/index.php";s:4:"3cbe";s:22:"mod_list/locallang.xml";s:4:"6485";s:26:"mod_list/locallang_mod.xml";s:4:"641b";s:35:"mod_list/newelement_positionmap.php";s:4:"6fec";s:30:"mod_list/newelement_wizard.php";s:4:"bab4";s:41:"mod_main/class.tx_categories_navframe.php";s:4:"ed00";s:17:"mod_main/conf.php";s:4:"a768";s:26:"mod_main/locallang_mod.xml";s:4:"fdd6";s:17:"res/indicator.gif";s:4:"03ce";s:25:"res/jquery.filtercombo.js";s:4:"4d51";s:13:"res/jquery.js";s:4:"608a";s:26:"res/jquery_uncompressed.js";s:4:"9960";s:16:"res/prototype.js";s:4:"76a7";s:18:"res/stylesheet.css";s:4:"f07c";s:11:"res/tree.js";s:4:"39e2";s:25:"tca/tx_categories_tca.inc";s:4:"030e";}',
	'suggests' => array(
	),
);

?>