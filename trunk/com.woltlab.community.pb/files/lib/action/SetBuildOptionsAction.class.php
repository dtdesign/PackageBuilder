<?php
// pb imports
require_once(PB_DIR.'lib/data/source/Source.class.php');

// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');

/**
 * Sets source directory used as source.
 *
 * @author	Alexander Ebert
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	action
 * @category 	PackageBuilder
 */
class SetBuildOptionsAction extends AbstractAction {
	public $directory = '';
	public $filename = '';
	public $sourceID = 0;

	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		if (isset($_POST['directory'])) $this->directory = StringUtil::trim($_POST['directory']);
		if (isset($_POST['sourceID'])) $this->sourceID = intval($_POST['sourceID']);
	}

	/**
	 * @see Action::execute()
	 */
	public function execute() {
		// call execute event
		parent::execute();
		
		$source = new Source($this->sourceID);
		if (!$source->sourceID) throw new IllegalLinkException();
		WCF::getUser()->checkPermission('user.source.general.canViewSources');
		WCF::getUser()->checkPermission('user.source.dynamic.canUseSource'.$source->sourceID);
		
		// set sourceDirectory
		WCF::getSession()->register('source'.$this->sourceID, $this->directory);

		// write to user preferences
		$sql = "INSERT IGNORE	INTO pb".PB_N."_user_preferences
					(sourceID, userID, directory)
			VALUES		(".$this->sourceID.", ".WCF::getUser()->userID.", '".escapeString($this->directory)."')
			ON DUPLICATE KEY UPDATE directory=VALUES(directory)";
		WCF::getDB()->sendQuery($sql);

		// call executed event
		$this->executed();

		// forward
		HeaderUtil::redirect('index.php?page=SourceView&sourceID='.$this->sourceID.SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>