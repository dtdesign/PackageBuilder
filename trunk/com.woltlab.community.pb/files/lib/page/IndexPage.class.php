<?php
// pb imports
require_once(PB_DIR.'lib/data/source/SourceList.class.php');

// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');
require_once(WCF_DIR.'lib/system/scm/SCMHelper.class.php');

/**
 * Default start page, displays all relevant informations.
 *
 * @author	Tim Düsterhus, Alexander Ebert
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	page
 * @category 	PackageBuilder
 */
class IndexPage extends AbstractPage {
	// system
	public $templateName = 'index';
	public $neededPermissions = 'user.source.general.canViewSources';

	/**
	 * instace of SourceList
	 * 
	 * @var	SourceList
	 */
	public $sourceList = null;

	/**
	 * @see	Page::readData()
	 */
	public function readData() {
		parent::readData();
		$this->sourceList = new SourceList();
		$this->sourceList->readObjects();
	}

	/**
	 * @see Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
			'allowSpidersToIndexThisPage' => false,
			'sources' => $this->sourceList->getObjects()
		));
	}
}
?>