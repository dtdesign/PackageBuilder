DROP TABLE IF EXISTS pb1_1_source;
CREATE TABLE pb1_1_source (
	sourceID INT(10) NOT NULL AUTO_INCREMENT,
	name VARCHAR(80) NOT NULL,
	sourceDirectory TEXT NOT NULL,
	buildDirectory TEXT NOT NULL,
	position SMALLINT(5) NOT NULL default '0',
	scm VARCHAR (255) NOT NULL default 'none',
	url TEXT NULL,
	username VARCHAR(80) NULL default '',
	revision VARCHAR(40) NOT NULL default '',
	trustServerCert TINYINT(1) unsigned NOT NULL,
	password VARCHAR(80) NULL,
	PRIMARY KEY (sourceID),
	INDEX position (position)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS pb1_1_source_package;
CREATE TABLE pb1_1_source_package (
	sourceID INT(10) NOT NULL,
	hash CHAR(40) NOT NULL,
	packageName VARCHAR(255) NOT NULL,
	version VARCHAR(50) NOT NULL,
	directory MEDIUMTEXT NOT NULL,
	packageType ENUM('standalone', 'plugin') NOT NULL DEFAULT 'plugin',
	KEY sourceID (sourceID),
	KEY packageType (packageType),
	UNIQUE KEY hash (hash)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS pb1_1_referenced_package;
CREATE TABLE pb1_1_referenced_package (
	sourceID INT(10) NOT NULL,
	hash CHAR(40) NOT NULL,
	packageName VARCHAR(255) NOT NULL,
	minVersion VARCHAR(50) NOT NULL,
	file MEDIUMTEXT NOT NULL,
	KEY sourceID (sourceID),
	KEY hash (hash)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS pb1_1_selected_package;
CREATE TABLE pb1_1_selected_package (
	sourceID INT(10) NOT NULL,
	directory TEXT NOT NULL,
	packageName VARCHAR(255) NOT NULL,
	hash CHAR(40) NOT NULL,
	resourceDirectory TEXT NOT NULL,
	UNIQUE KEY packageKey (sourceID, directory(250), hash)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS pb1_1_user_preference;
CREATE TABLE pb1_1_user_preference (
	sourceID INT(10) NOT NULL DEFAULT 0,
	userID INT(10) NOT NULL DEFAULT 0,
	directory TEXT NOT NULL,
	packageName VARCHAR(255) NOT NULL DEFAULT '',
	PRIMARY KEY (userID, sourceID),
	KEY sourceID (sourceID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS pb1_1_build_profile;
CREATE TABLE pb1_1_build_profile (
	packages TEXT NOT NULL,
	packageHash CHAR(40) NOT NULL DEFAULT '',
	packageName VARCHAR(255) NOT NULL DEFAULT '',
	profileHash CHAR(40) NOT NULL DEFAULT '',
	profileName VARCHAR(255) NOT NULL DEFAULT '',
	resource TEXT NOT NULL,
	UNIQUE KEY profileName (profileName)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS pb1_1_setup_resource;
CREATE TABLE pb1_1_setup_resource (
	sourceID INT(10) NOT NULL DEFAULT 0,
	directory TEXT NOT NULL,
	UNIQUE KEY (sourceID, directory(255))
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS pb1_1_source_file;
CREATE TABLE pb1_1_source_file (
	fileID INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
	sourceID INT(10) NOT NULL DEFAULT 0,
	hash CHAR(40) NOT NULL DEFAULT '',
	filename VARCHAR(255) NOT NULL DEFAULT '',
	fileType ENUM('package','wcfsetup') NOT NULL DEFAULT 'package',
	fileVersion VARCHAR(255) NOT NULL DEFAULT '',
	fileDate INT(10) NOT NULL DEFAULT 0,
	packageName VARCHAR(255) NOT NULL DEFAULT '',
	profileName VARCHAR(255) NOT NULL DEFAULT '',
	KEY sourceID (sourceID)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;