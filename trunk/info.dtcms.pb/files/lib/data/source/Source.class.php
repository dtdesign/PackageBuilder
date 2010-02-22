<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');

/**
 * Represents a source database row
 *
 * @package	info.dtcms.pb
 * @author	Alexander Ebert
 * @copyright	2009-2010 Alexander Ebert IT-Dienstleistungen
 * @license	GNU Lesser Public License <http://www.gnu.org/licenses/lgpl.html>
 * @subpackage	data.source
 * @category	PackageBuilder
 */
class Source extends DatabaseObject {
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
	 * @param	integer	$sourceID	if of a source
	 */
	private function getSource($sourceID) {
		$sql = "SELECT	*
			FROM	pb".PB_N."_sources
			WHERE	sourceID = ".intval($sourceID);
		$row = WCF::getDB()->getFirstRow($sql);

		parent::__construct($row);
	}
}
?>