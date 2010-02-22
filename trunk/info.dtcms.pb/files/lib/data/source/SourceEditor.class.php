<?php
// pb imports
require_once(PB_DIR.'lib/data/source/Source.class.php');

/**
 * Provides functions to edit a source
 *
 * @package	info.dtcms.pb
 * @author	Alexander Ebert
 * @copyright	2009 Alexander Ebert IT-Dienstleistungen
 * @license	GNU Lesser Public License <http://www.gnu.org/licenses/lgpl.html>
 * @subpackage	data.source
 * @category	PackageBuilder
 */
class SourceEditor extends Source {
	/**
	 * Creates a new SourceEditor object.
	 * @see Source::__construct()
	 */
	public function __construct($sourceID, $row = null) {
		$sql = "SELECT	*
			FROM	pb".PB_N."_sources
			WHERE	sourceID = ".$sourceID;
		$row = WCF::getDB()->getFirstRow($sql);
		parent::__construct(null, $row);
	}

	/**
	 * Creates a new source
	 *
	 * @param 	string	$name			The name of the source
	 * @param	string	$sourceDirectory	Source directory used for files
	 * @param	string	$buildDirectory		Build directory contains all archives
	 * @param	boolean	$useSubversion		Toggle use of subversion
	 * @param	string	$url			URL for accessing subversion
	 * @param	string	$username		Username neccessary if subversion repository is protected
	 * @param	string	$password		Password neccessary if subversion repository is protected
	 * @param	integer	$revision		Currently used revision
	 * @param	boolean	$trustServerCert	Automaticly trust server certificate
	 * @return 	SourceEditor
	 */
	public static function create($name, $sourceDirectory, $buildDirectory, $useSubversion = false, $url = null, $username = null, $password = null, $revision = 0, $trustServerCert = false) {
		// save data
		$sourceID = self::insert($name, array(
			'sourceDirectory' => $sourceDirectory,
			'buildDirectory' => $buildDirectory,
			'useSubversion' => $useSubversion,
			'url' => $url,
			'username' => $username,
			'password' => $password,
			'revision' => $revision,
			'trustServerCert' => $trustServerCert
		));

		// create sourceDirectory
		if (!empty($sourceDirectory) && !is_dir($sourceDirectory)) {
			@mkdir($sourceDirectory, 0770);
		}

		// create buildDirectory
		if (!empty($buildDirectory) && !is_dir($buildDirectory)) {
			@mkdir($buildDirectory, 0770);
		}

		// get source
		$source = new SourceEditor($sourceID, null);

		return $source;
	}

	/**
	 * Creates the source row in database table.
	 *
	 * @param 	string 	$name
	 * @param 	array	$additionalFields
	 * @return	integer	new source id
	 */
	public static function insert($name, $additionalFields = array()) {
		$keys = $values = '';
		foreach ($additionalFields as $key => $value) {
			$keys .= ','.$key;
			if (is_int($value)) $values .= ",".$value;
			else $values .= ",'".escapeString($value)."'";
		}

		$sql = "INSERT INTO	pb".PB_N."_sources
					(name
					".$keys.")
			VALUES		('".escapeString($name)."'
					".$values.")";
		WCF::getDB()->sendQuery($sql);
		return WCF::getDB()->getInsertID();
	}

	/**
	 * Updates the data of a source.
	 *
	 * @param 	string	$name			The name of the source
	 * @param	string	$sourceDirectory	Source directory used for files
	 * @param	string	$buildDirectory		Build directory contains all archives
	 * @param	boolean	$useSubversion		Toggle use of subversion
	 * @param	string	$url			URL for accessing subversion
	 * @param	string	$username		Username neccessary if subversion repository is protected
	 * @param	string	$password		Password neccessary if subversion repository is protected
	 * @param	integer	$revision		Currently used revision
	 * @param	boolean	$trustServerCert	Automaticly trust server certificate
	 * @return	SourceEditor
	 */
	public function update($name = null, $sourceDirectory = null, $buildDirectory = null, $useSubversion = null, $url = null, $username = null, $password = null, $revision = null, $trustServerCert = null) {
		$fields = array();
		if ($name !== null) $fields['name'] = $name;
		if ($sourceDirectory !== null) $fields['sourceDirectory'] = $sourceDirectory;
		if ($buildDirectory !== null) $fields['buildDirectory'] = $buildDirectory;
		if ($useSubversion !== null) $fields['useSubversion'] = intval($useSubversion);
		if ($url !== null) $fields['url'] = $url;
		if ($username !== null) $fields['username'] = $username;
		if ($password !== null) $fields['password'] = $password;
		if ($revision !== null) $fields['revision'] = intval($revision);
		if ($trustServerCert !== null) $fields['trustServerCert'] = intval($trustServerCert);

		self::updateData($fields);
	}

	/**
	 * Updates the data of a source.
	 *
	 * @param array	$fields
	 */
	public function updateData($fields = array()) {
		$updates = '';
		foreach ($fields as $key => $value) {
			if (!empty($updates)) $updates .= ',';
			$updates .= $key.'=';
			if (is_int($value)) $updates .= $value;
			else $updates .= "'".escapeString($value)."'";
		}

		if (!empty($updates)) {
			$sql = "UPDATE	pb".PB_N."_sources
				SET		".$updates."
				WHERE	sourceID = ".$this->sourceID;
			WCF::getDB()->sendQuery($sql);
		}
	}

	/**
	 * Deletes this source.
	 */
	public function delete() {
		$sql = "DELETE	FROM pb".PB_N."_sources
			WHERE	sourceID = ".$this->sourceID;
		WCF::getDB()->sendQuery($sql);
	}
}
?>