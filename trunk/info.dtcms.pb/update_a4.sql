CREATE TABLE pb1_1_referenced_packages (
	sourceID INT(10) NOT NULL,
	hash CHAR(40) NOT NULL,
	packageName VARCHAR(255) NOT NULL,
	minVersion VARCHAR(50) NOT NULL,
	file MEDIUMTEXT NOT NULL,
	KEY sourceID (sourceID),
	KEY hash (hash)
) ENGINE=MyISAM CHARACTER SET=utf8;

CREATE TABLE pb1_1_selected_packages (
	sourceID INT(10) NOT NULL,
	directory MEDIUMTEXT NOT NULL,
	packageName VARCHAR(255) NOT NULL,
	hash CHAR(40) NOT NULL,
	resourceDirectory MEDIUMTEXT NOT NULL,
	UNIQUE KEY packageKey (sourceID, directory(250), hash)
) ENGINE=MyISAM CHARACTER SET=utf8;