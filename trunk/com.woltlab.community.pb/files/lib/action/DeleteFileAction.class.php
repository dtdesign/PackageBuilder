<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');

// pb imports
require_once(PB_DIR.'lib/data/source/file/SourceFileEditor.class.php');

/**
 * Deletes a build package.
 *
 * @author	Alexander Ebert
 * @copyright	2009-2011 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	action
 * @category 	PackageBuilder
 */
class DeleteFileAction extends AbstractSecureAction {
	/**
	 * file id
	 * 
	 * @var	integer
	 */
	public $fileID = 0;
	
	/**
	 * SourceFileEditor object
	 * 
	 * @var	SourceFileEditor
	 */
	public $sourceFileEditor = null;
	
	/**
	 * @see	Action::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();
		
		if (isset($_GET['fileID'])) $this->fileID = intval($_GET['fileID']);
		$this->sourceFile = new SourceFile($this->fileID);
		
		if (!$this->sourceFile->fileID) {
			throw new IllegalLinkException();
		}
		
		if (!$this->sourceFile->canDownload()) {
			throw new PermissionDeniedException();
		} 
	}
	
	/**
	 * @see	Action::execute()
	 */
	public function execute() {
		parent::execute();
		
		$this->sourceFileEditor->delete();
		
		$this->executed();
		exit;
	}
}
?>