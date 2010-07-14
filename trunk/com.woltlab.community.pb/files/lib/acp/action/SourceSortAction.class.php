<?php
// pb imports
require_once(PB_DIR.'lib/data/source/SourceEditor.class.php');

// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');

/**
 * An action to sort sources.
 *
 * @author	Tim Düsterhus
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	action
 * @category 	PackageBuilder
 */
class SourceSortAction extends AbstractAction {
	public $positions = array();

	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		if (isset($_POST['sourceListPositions']) && is_array($_POST['sourceListPositions'])) $this->positions = ArrayUtil::toIntegerArray($_POST['sourceListPositions']);

	}

	/**
	 * @see Action::execute()
	 */
	public function execute() {
		// call execute event
		parent::execute();
		WCF::getUser()->checkPermission('admin.source.canEditSources');

		// sort them
		asort($this->positions);
		$position = 1;

		// set the position for each of them
		foreach ($this->positions as $key => $val) {
			$sql = "UPDATE	pb".PB_N."_sources
				SET	position = ".$position."
				WHERE	sourceID = ".$key;
			WCF::getDB()->sendQuery($sql);
			$position++;
		}
		// call executed event
		$this->executed();

		// forward
		HeaderUtil::redirect('index.php?page=SourceList&packageID='.PACKAGE_ID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>