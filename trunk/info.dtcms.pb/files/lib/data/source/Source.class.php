<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');
require_once(WCF_DIR.'lib/util/FileUtil.class.php');
require_once(WCF_DIR.'lib/util/StringUtil.class.php');

/**
 * Represents a source database row.
 *
 * @author	Alexander Ebert
 * @copyright	2009-2010 Alexander Ebert IT-Dienstleistungen
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	info.dtcms.pb
 * @subpackage	data.source
 * @category 	PackageBuilder
 */
class Source extends DatabaseObject {
	/**
	 * Holds all available SCM
	 *
	 * @var	array
	 */
	protected static $availableSCM = array(
		'git',
		'none',
		'subversion'
	);

	/**
	 * Creates a new Source object.
	 *
	 * If id is set, the function reads the source data from database.
	 * Otherwise it uses the given resultset.
	 *
	 * @param 	integer	$sourceID	id of a source
	 * @param 	array	$row		resultset with source data from database
	 */
	public function __construct($sourceID, $row = null) {
		if ($sourceID !== null) self::getSource($sourceID);
		if ($row !== null) parent::__construct($row);
	}

	/**
	 * Reads source from database.
	 *
	 * @param	integer	$sourceID	id of a source
	 */
	private function getSource($sourceID) {
		$sql = "SELECT	*
			FROM	pb".PB_N."_sources
			WHERE	sourceID = ".intval($sourceID);
		$row = WCF::getDB()->getFirstRow($sql);

		parent::__construct($row);
	}

	/**
	 * Returns all available SCM
	 *
	 * @return	string	Valid SCMs
	 */
	public static function getAvailableSCM() {
		return self::$availableSCM;
	}

	/**
	 * Validates a given SCM, if it is not available or unknown will return 'none' instead
	 *
	 * @param	string	$scm	Source Code Management
	 * @return	string	Valid SCM
	 */
	public static function validateSCM($scm) {
		$scm = StringUtil::toLowerCase($scm);

		// return lower case SCM
		if (in_array($scm, self::$availableSCM)) {
			return $scm;
		}

		// scm unknown, change to none
		return 'none';
	}

	/**
	 * Returns a random directory
	 *
	 * @param	string	$directory	Directory to include
	 * @return	Random directory
	 */
	public static function getRandomDirectory($directory) {
		$directory = PB_DIR.$directory.'/'.StringUtil::getRandomID().'/';
		$directory = FileUtil::unifyDirSeperator($directory);

		return $directory;
	}
}
?>