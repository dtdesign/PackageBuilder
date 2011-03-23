<?php
// pb imports
require_once(PB_DIR.'lib/data/source/Source.class.php');

// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');

/**
 * Sets source directory used as source.
 *
 * @author	Alexander Ebert
 * @copyright	2009-2011 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	action
 * @category 	PackageBuilder
 */
class SetBuildOptionsAction extends AbstractAction {
	/**
	 * active directory
	 * 
	 * @var	string
	 */
	public $directory = '';
	
	/**
	 * active package name
	 * 
	 * @var	string
	 */
	public $packageName = '';
	
	/**
	 * instance of Source
	 * 
	 * @var	Source
	 */
	public $source = null;
	
	/**
	 * source id
	 * 
	 * @var	integer
	 */
	public $sourceID = 0;

	/**
	 * @see Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		if (isset($_POST['directory'])) $this->directory = StringUtil::trim($_POST['directory']);
		if (isset($_POST['packageName'])) $this->packageName = StringUtil::trim($_POST['packageName']);
		if (isset($_POST['sourceID'])) $this->sourceID = intval($_POST['sourceID']);
		
		$this->source = new Source($this->sourceID);
		if (!$this->source->sourceID || !$this->source->hasAccess()) throw new IllegalLinkException();
	}

	/**
	 * @see Action::execute()
	 */
	public function execute() {
		// call execute event
		parent::execute();
		
		// set sourceDirectory
		WCF::getSession()->register('source'.$this->sourceID, serialize(array(
			'directory' => $this->directory,
			'packageName' => $this->packageName
		)));
		
		// write to user preferences
		$sql = "INSERT IGNORE INTO	pb".PB_N."_user_preference
						(sourceID, userID, packageName, directory)
			VALUES			(".$this->sourceID.",
						".WCF::getUser()->userID.",
						'".escapeString($this->packageName)."',
						'".escapeString($this->directory)."')
			ON DUPLICATE KEY UPDATE	directory = VALUES(directory),
						packageName = VALUES(packageName)";
		WCF::getDB()->sendQuery($sql);
		
		// call executed event
		$this->executed();
		
		// forward
		HeaderUtil::redirect('index.php?page=SourceView&sourceID=' . $this->sourceID . SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>