<?php
// wcf imports
require_once(WCF_DIR.'lib/action/AbstractSecureAction.class.php');

// pb imports
require_once(PB_DIR.'lib/data/source/file/SourceFile.class.php');

/**
 * Downloads a build package.
 *
 * @author	Alexander Ebert
 * @copyright	2009-2011 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	action
 * @category 	PackageBuilder
 */
class DownloadFileAction extends AbstractSecureAction {
	/**
	 * file id
	 * 
	 * @var	integer
	 */
	public $fileID = 0;
	
	/**
	 * SourceFile object
	 * 
	 * @var	SourceFile
	 */
	public $sourceFile = null;
	
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
		
		if (!file_exists($this->sourceFile->getPath())) {
			throw new SystemException("Requested file '".$this->sourceFile->filename."' for source identified by '".$this->sourceFile->sourceID."' is missing.");
		}
		
		@header('Content-Type: application/x-gzip');
		@header('Content-length: '.filesize($this->sourceFile->getPath()));
		@header('Content-disposition: attachment; filename="'.$this->sourceFile->filename.'"');
		
		readfile($this->sourceFile->getPath());
		exit;
	}
}
?>