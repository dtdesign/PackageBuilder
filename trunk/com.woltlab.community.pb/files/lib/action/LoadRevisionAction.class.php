<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');

// pb imports
require_once(PB_DIR.'lib/data/source/Source.class.php');

/**
 * Loads HEAD revision for a given source.
 *
 * @author	Alexander Ebert
 * @copyright	2009-2011 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	action
 * @category 	PackageBuilder
 */
class LoadRevisionAction extends AbstractAction {
	/**
	 * source id
	 * 
	 * @var	integer
	 */
	public $sourceID = 0;
	
	/**
	 * active source
	 * 
	 * @var	Source
	 */
	public $source = null;
	
	/**
	 * @see	Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_POST['sourceID'])) $this->sourceID = intval($_POST['sourceID']);
		$this->source = new Source($this->sourceID);
		if (!$this->source->sourceID) {
			throw new IllegalLinkException();
		}
	}
	
	/**
	 * @see	Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		$headRevision = $this->source->getHeadRevision();
		if ($this->source->revision == $headRevision) {
			$revision = $headRevision;
		}
		else {
			$revision = '<strong class="red">' . WCF::getLanguage()->getDynamicVariable('pb.source.scm.higherRevisionAvailable', array('source' => $this->source)) . '</strong>';
		}
		
		$revision = array('revision' => $revision);
		$json = JSON::encode($revision);
		
		// send JSON response
		header('Content-type: application/json');
		echo $json;
		exit;
	}
}
?>