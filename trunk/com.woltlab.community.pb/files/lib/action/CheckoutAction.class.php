<?php
// pb imports
require_once(PB_DIR.'lib/data/source/SourceEditor.class.php');

// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/system/scm/SCMHelper.class.php');

/**
 * Checks out a repository.
 *
 * @author	Alexander Ebert
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	action
 * @category 	PackageBuilder
 */
class CheckoutAction extends AbstractAction {
	public $sourceID = 0;

	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		if (isset($_GET['sourceID'])) $this->sourceID = intval($_GET['sourceID']);
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
		$className = ucfirst(SCMHelper::getSCM($row['scm']) ? SCMHelper::getSCM($row['scm']) : 'none');

		// check out repository
		require_once(WCF_DIR.'lib/system/scm/'.$className.'.class.php');
		call_user_func(array($className, 'checkout'), $source->url, $source->sourceDirectory, $source->username, $source->password);

		// set revision
		$revision = call_user_func(array($className, 'getHeadRevision'), $source->url, $source->username, $source->password);
		$source->update(null, null, null, null, null, null, null, $revision);

		// call executed event
		$this->executed();

		// forward
		HeaderUtil::redirect('index.php?page=SourceView&sourceID='.$source->sourceID.'&latestRevision='.$revision.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>