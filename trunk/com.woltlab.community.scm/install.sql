CREATE TABLE wcf1_scm (
	packageID int(10) unsigned NOT NULL,
	scm VARCHAR(255) NOT NULL,
	PRIMARY KEY ( packageID ),
	UNIQUE KEY scm (scm)
) ENGINE=MyISAM CHARACTER SET=utf8;
