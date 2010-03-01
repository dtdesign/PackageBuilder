<?php
// pb imports
require_once(PB_DIR.'lib/data/source/Source.class.php');

// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

/**
 * Shows a list of all sources.
 *
 * @author	Alexander Ebert
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	acp.page
 * @category 	PackageBuilder
 */
class SourceListPage extends AbstractPage {
	// system
	public $templateName = 'sourceList';
	public $neededPermissions = 'admin.source.canViewSources';
	
	public $deletedSourceID = 0;
	public $sources = array();
	public $successfulSorting = 0;

	/**
	 * @see	Page::readData()
	 */
	public function readData() {
		parent::readData();

		$sql = "SELECT	*
			FROM	pb".PB_N."_sources
			ORDER BY position ASC";
		$result = WCF::getDB()->sendQuery($sql);

		while ($row = WCF::getDB()->fetchArray($result)) {
			$this->sources[] = new Source('', $row);
		}
	}

	/**
	 * @see	Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
			'deletedSourceID' => $this->deletedSourceID,
			'maxPosition' => count($this->sources),
			'sources' => $this->sources,
			'successfulSorting' => $this->successfulSorting
		));
	}

	/**
	 * @see	Page::show()
	 */
	public function show() {
		WCFACP::getMenu()->setActiveMenuItem('pb.acp.menu.link.content.source.list');
		parent::show();
	}
}
?>