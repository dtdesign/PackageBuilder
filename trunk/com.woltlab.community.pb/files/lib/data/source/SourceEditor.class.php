<?php
// pb imports
require_once(PB_DIR.'lib/data/source/Source.class.php');

// wcf imports
require_once(WCF_DIR.'lib/util/pip/LanguagesXMLPIP.class.php');
require_once(WCF_DIR.'lib/system/language/LanguageEditor.class.php');

/**
 * Provides methods to create and edit a source.
 *
 * @author	Tim Düsterhus, Alexander Ebert
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
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
	 * @param	integer	$position		Position used to order sources
	 * @return 	SourceEditor
	 */
	public static function create($name, $sourceDirectory, $buildDirectory, $scm, $url, $username, $password, $trustServerCert, $position) {
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

		// get source
		$source = new SourceEditor($sourceID, null);

		// set position
		$source->setPosition($position);

		// create permissions
		$source->createPermissions();

		return $source;
	}

	/**
	 * Creates permissions for this source
	 */
	public function createPermissions() {
		// break if no sourceID given
		if (!$this->sourceID) throw new IllegalLinkException();

		// determine position for next group option
		$sql = "SELECT	IFNULL(MAX(showOrder), 0) + 1 AS showOrder
			FROM	wcf".WCF_N."_group_option
			WHERE	categoryName = 'user.source.dynamic'
			AND	packageID = ".PACKAGE_ID;
		$row = WCF::getDB()->getFirstRow($sql);
		$showOrder = $row['showOrder'];

		// create group option
		$sql = "INSERT IGNORE INTO	wcf".WCF_N."_group_option
						(packageID, optionName, categoryName, optionType, defaultValue, showOrder)
			VALUES			(".PACKAGE_ID.",
						'user.source.dynamic.canUseSource".$this->sourceID."',
						'user.source.dynamic',
						'boolean',
						0,
						".intval($showOrder).")";
		WCF::getDB()->sendQuery($sql);

		//get available languages
		$languageCodes = Language::getLanguageCodes();

		// create language variables
		$sql = "SELECT	languageID, languageItem, languageItemValue
			FROM	wcf".WCF_N."_language_item
			WHERE	languageItem IN
				(
					'wcf.acp.group.option.user.source.dynamic.default',
					'wcf.acp.group.option.user.source.dynamic.default.description'
				)
			AND	packageID = ".PACKAGE_ID;
		$result = WCF::getDB()->sendQuery($sql);

		// create language variables for each language
		while ($row = WCF::getDB()->fetchArray($result)) {
			$key = 'option.user.source.dynamic.canUseSource'.$this->sourceID;

			if ($row['languageItem'] == 'wcf.acp.group.option.user.source.dynamic.default.description') {
				$key .= '.description';
			}

			$value = str_replace('#sourceName#', $this->name, $row['languageItemValue']);

			$languageCode = $languageCodes[$row['languageID']];
			$languageData[$languageCode]['wcf.acp.group'][$key] = $value;
		}

		// import language variables
		foreach ($languageData as $languageCode => $data) {
			//create XML string
			$xml = LanguagesXMLPIP::create(array($languageCode => $data), true);

			// parse xml
			$xmlObj = new XML();
			$xmlObj->loadString($xml);

			// import language xml
			LanguageEditor::importFromXML($xmlObj, PACKAGE_ID);
		}
	}

	/**
	 * Sets new position
	 *
	 * @param	integer	$position	New position
	 */
	public function setPosition($position = null) {
		if ($position !== null) {
			$sql = "UPDATE	pb".PB_N."_sources
				SET	position = position + 1
				WHERE 	position >= ".$position;
			WCF::getDB()->sendQuery($sql);
		}

		// get final position
		$sql = "SELECT 	IFNULL(MAX(position), 0) + 1 AS position
			FROM	pb".PB_N."_sources";
		if ($position) $sql .= " WHERE position <= ".$position;
		$row = WCF::getDB()->getFirstRow($sql);
		$position = $row['position'];

		// save position
		$sql = "UPDATE	pb".PB_N."_sources
			SET	position = ".$position."
			WHERE	sourceID = ".$this->sourceID;
		WCF::getDB()->sendQuery($sql);
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
	 * @param	integer	$position		Position used to order sources
	 * @return	SourceEditor
	 */
	public function update($name = null, $sourceDirectory = null, $buildDirectory = null, $scm = null, $url = null, $username = null, $password = null, $revision = null, $trustServerCert = null, $position = null) {
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
		if ($position !== null) $fields['position'] = intval($position);

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