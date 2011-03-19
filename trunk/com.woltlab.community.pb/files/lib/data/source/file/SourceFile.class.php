<?php
// wcf imports
require_once(WCF_DIR.'lib/data/DatabaseObject.class.php');
require_once(WCF_DIR.'lib/system/scm/SCMHelper.class.php');

/**
 * Represents a source file database row.
 *
 * @author	Alexander Ebert
 * @copyright	2009-2011 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	data.source.file
 * @category 	PackageBuilder
 */
class SourceFile extends DatabaseObject {
	/**
	 * Creates a new SourceFile object.
	 *
	 * If id is set, the function reads the source file data from database.
	 * Otherwise it uses the given resultset.
	 * 
	 * @param	integer		$fileID
	 * @param	array		$row
	 */
	public function __construct($fileID, array $row = null) {
		if ($row === null) {
			$sql = "SELECT	*
				FROM	pb".PB_N."_source_file
				WHERE	fileID = ".$fileID;
			$row = WCF::getDB()->getFirstRow($sql);
		}
		
		parent::__construct($row);
	}
	
	/**
	 * Returns true if current user has access to associated source.
	 * 
	 * @return	boolean
	 */
	public function canDownload() {
		return in_array($this->sourceID, WCF::getUser()->getAccessibleSourceIDs());
	}
	
	/**
	 * Returns path to associated file.
	 * 
	 * @return	string
	 */
	public function getPath() {
		return PB_DIR . 'packages/' . $this->fileID . '-' . $this->hash;
	}
}
?>