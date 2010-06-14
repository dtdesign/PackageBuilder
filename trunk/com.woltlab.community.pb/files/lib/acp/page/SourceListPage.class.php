<?php
// pb imports
require_once(PB_DIR.'lib/data/source/SourceList.class.php');

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

		$this->sourceList = new SourceList();
		$this->sourceList->readObjects();
	}

	/**
	 * @see	Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();
		WCF::getTPL()->assign(array(
			'deletedSourceID' => $this->deletedSourceID,
			'maxPosition' => $this->sourceList->countObjects(),
			'sources' => $this->sourceList->getObjects(),
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