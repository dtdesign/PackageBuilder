<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

/**
 * Default page, showing all relevant informations.
 *
 * @package		info.dtcms.pb
 * @author		Alexander Ebert
 * @copyright	2009 Alexander Ebert IT-Dienstleistungen
 * @license		GNU Lesser Public License <http://www.gnu.org/licenses/lgpl.html>
 * @subpackage	lib.page
 * @category	PackageBuilder
 */
class IndexPage extends AbstractPage {
	public $templateName = 'index';
	
	// data
	public $sources = array();
	
	/**
	 * @see	Page::readData()
	 */
	public function readData() {
		$sql = "SELECT *
				FROM	pb".PB_N."_sources";
		$result = WCF::getDB()->sendQuery($sql);
		
		while ($row = WCF::getDB()->fetchArray($result)) {
			// fetch available revision if subversion is used
			if ($row['useSubversion']) {
				require_once(WCF_DIR.'lib/system/subversion/Subversion.class.php');
				$availableRevision = Subversion::getHeadRevision($row['url'], $row['username'], $row['password']);
				$row['availableRevision'] = $availableRevision;
			}
			
			$this->sources[] = $row;
		}
	}
	
	/**
	 * @see Page::assignVariables()	 
	 */
	public function assignVariables() {
		parent::assignVariables();
		
		WCF::getTPL()->assign(array(				
				'allowSpidersToIndexThisPage' => false,
				'sources' => $this->sources				
		));
	}
}
?>