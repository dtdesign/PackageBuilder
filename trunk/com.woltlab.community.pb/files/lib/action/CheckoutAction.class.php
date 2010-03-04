<?php
// pb imports
require_once(PB_DIR.'lib/data/source/SourceEditor.class.php');

// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/system/scm/SCMHelper.class.php');

/**
 * Checks out a repository.
 *
 * @author	Tim Düsterhus, Alexander Ebert
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	action
 * @category 	PackageBuilder
 */
class CheckoutAction extends AbstractAction {
	public $sourceID = 0;
	public $rebuildPackageData;

	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		if (isset($_REQUEST['sourceID'])) $this->sourceID = intval($_REQUEST['sourceID']);
		if (isset($_REQUEST['rebuildPackageData'])) $this->rebuildPackageData = (boolean) $_REQUEST['rebuildPackageData'];
	}

	/**
	 * @see Action::execute()
	 */
	public function execute() {
		// call execute event
		parent::execute();

		// fetch data
		$source = new SourceEditor($this->sourceID);
		if (!$source->sourceID) throw new IllegalLinkException();

		// load scm driver
		$className = ucfirst(SCMHelper::getSCM($source->scm) ? SCMHelper::getSCM($source->scm) : 'none');

		// check out repository
		require_once(WCF_DIR.'lib/system/scm/'.$className.'.class.php');
		call_user_func(array($className, 'checkout'), $source->url, $source->sourceDirectory, $source->username, $source->password);

		// set revision
		$revision = call_user_func(array($className, 'getHeadRevision'), $source->url, $source->username, $source->password);
		$source->update(null, null, null, null, null, null, null, $revision);

		// rebuild package data if requested
		if ($this->rebuildPackageData) {
			require_once(PB_DIR.'lib/system/package/PackageHelper.class.php');
			PackageHelper::readPackages($source);
		}

		// call executed event
		$this->executed();

		// forward
		HeaderUtil::redirect('index.php?page=SourceView&sourceID='.$source->sourceID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>