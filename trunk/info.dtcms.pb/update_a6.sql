ALTER TABLE pb1_1_sources ADD trustServerCert TINYINT(1) unsigned NOT NULL DEFAULT '0';
ALTER TABLE pb1_1_sources ADD sortOrder VARCHAR(4) NOT NULL DEFAULT '0';
ALTER TABLE pb1_1_sources ADD INDEX sortOrder (sortOrder);