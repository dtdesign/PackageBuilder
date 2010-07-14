<?php
// pb imports
require_once(PB_DIR.'lib/data/source/SourceEditor.class.php');

// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');

/**
 * An action to remove sources.
 *
 * @author	Tim Düsterhus
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	action
 * @category 	PackageBuilder
 */
class SourceDeleteAction extends AbstractAction {
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
		WCF::getUser()->checkPermission('admin.source.canDeleteSources');
		// fetch data
		$source = new SourceEditor($this->sourceID);
		if (!$source->sourceID) throw new IllegalLinkException();

		$source->delete();

		// call executed event
		$this->executed();

		// forward
		HeaderUtil::redirect('index.php?page=SourceList&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>