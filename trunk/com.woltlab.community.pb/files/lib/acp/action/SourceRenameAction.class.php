<?php
// pb imports
require_once(PB_DIR.'lib/data/source/SourceEditor.class.php');

// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');

/**
 * An action to rename sources.
 *
 * @author	Tim Düsterhus
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	action
 * @category 	PackageBuilder
 */
class SourceRenameAction extends AbstractAction {
	public $sourceID = 0;
	public $title = '';

	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		if (isset($_GET['sourceID'])) $this->sourceID = intval($_GET['sourceID']);
		if (isset($_POST['title'])) {
			$this->title = StringUtil::trim($_POST['title']);
		}
		else {
			throw new IllegalLinkException();
		}
	}

	/**
	 * @see Action::execute()
	 */
	public function execute() {
		// call execute event
		parent::execute();
		WCF::getUser()->checkPermission('admin.source.canEditSources');
		// fetch data
		$source = new SourceEditor($this->sourceID);
		if (!$source->sourceID) throw new IllegalLinkException();

		$source->update(
  			$this->title
		);


		// call executed event
		$this->executed();

		// only called via ajax, so no header location
		exit;
	}
}
?>