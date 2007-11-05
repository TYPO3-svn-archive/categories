#
# Table structure for table 'tx_categories'
#
CREATE TABLE tx_categories (
	uid int(11) NOT NULL auto_increment,
	pid int(11) DEFAULT '0' NOT NULL,
	tstamp int(11) DEFAULT '0' NOT NULL,
	crdate int(11) DEFAULT '0' NOT NULL,
	cruser_id int(11) DEFAULT '0' NOT NULL,
	sys_language_uid int(11) DEFAULT '0' NOT NULL,
	l18n_parent int(11) DEFAULT '0' NOT NULL,
	l18n_diffsource mediumblob NOT NULL,	
	editlock tinyint(4) unsigned DEFAULT '0' NOT NULL,	
	deleted tinyint(4) DEFAULT '0' NOT NULL,
	hidden tinyint(4) DEFAULT '0' NOT NULL,
	title tinytext NOT NULL,
	alias varchar(32) DEFAULT '' NOT NULL,	
	description text NOT NULL,
	media blob NOT NULL,	
	synonyms text NOT NULL,
	related int(11) DEFAULT '0' NOT NULL,
	php_tree_stop tinyint(4) DEFAULT '0' NOT NULL,
	orig_id tinytext NOT NULL,
	PRIMARY KEY (uid),
	KEY parent (pid)
);


#
# Table structure for table 'tx_categories_mm'
# 
CREATE TABLE tx_categories_mm (
	uid_local int(11) DEFAULT '0' NOT NULL,
	uid_foreign int(11) DEFAULT '0' NOT NULL,
	tablenames varchar(30) DEFAULT '' NOT NULL,
	sorting int(11) DEFAULT '0' NOT NULL,
	sorting_foreign int(11) DEFAULT '0' NOT NULL,
	localtable varchar(30) DEFAULT '' NOT NULL,	
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);



#
# Table structure for table 'tx_categories_related_category_mm'
# 
CREATE TABLE tx_categories_related_category_mm (
	uid_local int(11) DEFAULT '0' NOT NULL,
	uid_foreign int(11) DEFAULT '0' NOT NULL,
	tablenames varchar(30) DEFAULT '' NOT NULL,
	sorting int(11) DEFAULT '0' NOT NULL,
	KEY uid_local (uid_local),
	KEY uid_foreign (uid_foreign)
);


#
# Add field to table 'be_groups'
#
CREATE TABLE be_groups (
	tx_categories_mountpoints tinytext NOT NULL
);

#
# Add field to table 'be_users'
#
CREATE TABLE be_users (
	tx_categories_mountpoints tinytext NOT NULL
);
