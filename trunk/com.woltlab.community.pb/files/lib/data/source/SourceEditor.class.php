<?php
// pb imports
require_once(PB_DIR.'lib/data/source/Source.class.php');

// wcf imports
require_once(WCF_DIR.'lib/util/FileUtil.class.php');

/**
 * Provides methods to create and edit a source.
 *
 * @author	Alexander Ebert
 * @copyright	2009-2010 Alexander Ebert IT-Dienstleistungen
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	info.dtcms.pb
 * @subpackage	data.source
 * @category 	PackageBuilder
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
	 * Creates a new source.
	 *
	 * @param 	string	$name			The name of the source
	 * @param	string	$sourceDirectory	Source directory used for files
	 * @param	string	$buildDirectory		Build directory contains all archives
	 * @param	string	$scm			Defines used SCM, may be 'git', 'none' and 'subversion'
	 * @param	string	$url			URL for accessing subversion
	 * @param	string	$username		Username neccessary if subversion repository is protected
	 * @param	string	$password		Password neccessary if subversion repository is protected
	 * @param	boolean	$trustServerCert	Automaticly trust server certificate
	 * @return 	SourceEditor
	 */
	public static function create($name, $sourceDirectory, $buildDirectory, $scm, $url = '', $username = '', $password = '', $trustServerCert = false) {
		// handle dir seperators
		$sourceDirectory = FileUtil::unifyDirSeperator($sourceDirectory);
		$buildDirectory = FileUtil::unifyDirSeperator($buildDirectory);

		// validate SCM
		$scm = Source::validateSCM($scm);

		// save data
		$sourceID = self::insert($name, array(
			'sourceDirectory' => $sourceDirectory,
			'buildDirectory' => $buildDirectory,
			'scm' => $scm,
			'url' => $url,
			'username' => $username,
			'password' => $password,
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
	 * @param	string	$scm			Defines used SCM, may be 'git', 'none' and 'subversion'
	 * @param	string	$url			URL for accessing subversion
	 * @param	string	$username		Username neccessary if subversion repository is protected
	 * @param	string	$password		Password neccessary if subversion repository is protected
	 * @param	string	$revision		Currently used revision
	 * @param	boolean	$trustServerCert	Automaticly trust server certificate
	 * @return	SourceEditor
	 */
	public function update($name = null, $sourceDirectory = null, $buildDirectory = null, $scm = null, $url = null, $username = null, $password = null, $revision = null, $trustServerCert = null) {
		$fields = array();
		if ($name !== null) $fields['name'] = $name;
		if ($sourceDirectory !== null) $fields['sourceDirectory'] = $sourceDirectory;
		if ($buildDirectory !== null) $fields['buildDirectory'] = $buildDirectory;
		if ($scm !== null) $fields['scm'] = Source::validateSCM($scm);
		if ($url !== null) $fields['url'] = $url;
		if ($username !== null) $fields['username'] = $username;
		if ($password !== null) $fields['password'] = $password;
		if ($revision !== null) $fields['revision'] = $revision;
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
				SET	".$updates."
				WHERE	sourceID = ".$this->sourceID;
			WCF::getDB()->sendQuery($sql);
		}
	}

	/**
	 * Deletes this source.
	 */
	public function delete() {
		// remove main database entry
		$sql = "DELETE	FROM pb".PB_N."_sources
			WHERE	sourceID = ".$this->sourceID;
		WCF::getDB()->sendQuery($sql);

		// remove cached packages
		$sql = "DELETE	FROM pb".PB_N."_sources_packages
			WHERE	sourceID = ".$this->sourceID;
		WCF::getDB()->sendQuery($sql);

		// remove cached reference data
		$sql = "DELETE	FROM pb".PB_N."_referenced_packages
			WHERE	sourceID = ".$this->sourceID;
		WCF::getDB()->sendQuery($sql);

		// remove package pre-selections
		$sql = "DELETE	FROM pb".PB_N."_selected_packages
			WHERE	sourceID = ".$this->sourceID;
		WCF::getDB()->sendQuery($sql);
	}
}
?>