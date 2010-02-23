<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/util/StringUtil.class.php');

/**
 * Sets sourceDirectory used as source.
 *
 * @package		info.dtcms.pb
 * @author		Alexander Ebert
 * @copyright	2009 Alexander Ebert IT-Dienstleistungen
 * @license		GNU Lesser Public License <http://www.gnu.org/licenses/lgpl.html>
 * @subpackage	action
 * @category	PackageBuilder
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
		if (isset($_POST['filename'])) $this->filename = StringUtil::trim($_POST['filename']);
		if (isset($_POST['sourceID'])) $this->sourceID = intval($_POST['sourceID']);
	}
	
	/**
	 * @see Action::execute()
	 */
	public function execute() {
		// call execute event
		parent::execute();
		
		// set sourceDirectory
		WCF::getSession()->register('source'.$this->sourceID, $this->directory);
		
		// set filename
		WCF::getSession()->register('filename'.$this->sourceID, $this->filename);
		
		// call executed event
		$this->executed();
		
		// forward
		HeaderUtil::redirect('index.php?page=SourceView&sourceID=' . $this->sourceID . SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>