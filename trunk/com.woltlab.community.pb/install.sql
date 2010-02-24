CREATE TABLE pb1_1_sources (
	sourceID INT( 10 ) NOT NULL AUTO_INCREMENT ,
	name VARCHAR( 80 ) NOT NULL ,
	sourceDirectory TEXT NOT NULL ,
	buildDirectory TEXT NOT NULL,
	scm ENUM('git','none','subversion') NOT NULL,
	url TEXT NULL ,
	username VARCHAR( 80 ) NULL default '',
	revision VARCHAR(40) NOT NULL default '',
	trustServerCert TINYINT( 1 ) unsigned NOT NULL ,
	sortOrder VARCHAR(4) NOT NULL ,
	password VARCHAR( 80 ) NULL ,
	PRIMARY KEY ( sourceID ),
	INDEX sortOrder (sortOrder)
) ENGINE=MyISAM CHARACTER SET=utf8;

CREATE TABLE pb1_1_sources_packages (
	sourceID INT( 10 ) NOT NULL ,
	hash CHAR( 40 ) NOT NULL ,
	packageName VARCHAR( 255 ) NOT NULL ,
	version VARCHAR( 50 ) NOT NULL ,
	directory MEDIUMTEXT NOT NULL ,
	INDEX ( sourceID ),
	UNIQUE ( hash )
) ENGINE=MyISAM CHARACTER SET=utf8;

CREATE TABLE pb1_1_referenced_packages (
	sourceID INT( 10 ) NOT NULL ,
	hash CHAR( 40 ) NOT NULL ,
	packageName VARCHAR( 255 ) NOT NULL ,
	minVersion VARCHAR( 50 ) NOT NULL ,
	file MEDIUMTEXT NOT NULL ,
	KEY sourceID ( sourceID ),
	KEY hash ( hash )
) ENGINE=MyISAM CHARACTER SET=utf8;

CREATE TABLE pb1_1_selected_packages (
	sourceID INT(10) NOT NULL,
	directory MEDIUMTEXT NOT NULL,
	packageName VARCHAR(255) NOT NULL,
	hash CHAR(40) NOT NULL,
	resourceDirectory MEDIUMTEXT NOT NULL,
	UNIQUE KEY packageKey (sourceID, directory(250), hash)
) ENGINE=MyISAM CHARACTER SET=utf8;