<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

/**
 * Default start page, displays all relevant informations.
 *
 * @author	Alexander Ebert
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	page
 * @category 	PackageBuilder
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

	/**
	 * @see	Page::show()
	 */
	public function show() {
		// validate general permission
		if (!WCF::getUser()->getPermission('user.source.general.canViewSources')) throw new PermissionDeniedException();

		parent::show();
	}
}
?>