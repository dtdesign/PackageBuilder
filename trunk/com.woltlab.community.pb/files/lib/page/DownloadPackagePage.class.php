<?php
// pb imports
require_once(PB_DIR.'lib/data/source/Source.class.php');

// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

/**
 * Outputs the specified file, so that group restrictions are in effect
 *
 * @author	Tim Düsterhus
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	page
 * @category 	PackageBuilder
 */
class DownloadPackagePage extends AbstractPage {
	// system
	public $templateName = '';
	public $neededPermissions = 'user.source.general.canViewSources';
	
	// data
	public $filename = '';

	/**
	 * @see	Page::readParameters()
	 */
	public function readParameters() {
		$sourceID = 0;
		if (isset($_GET['sourceID'])) $sourceID = $_GET['sourceID'];
		
		$this->source = new Source($sourceID);
		if (!$this->source->sourceID) throw new IllegalLinkException();
		if (!$this->source->hasAccess()) throw new PermissionDeniedException();
		
		if(isset($_GET['filename'])) $this->filename = $_GET['filename'];
		else throw new IllegalLinkException();
		if(!file_exists($this->source->buildDirectory.$this->filename)) throw new IllegalLinkException();
	}

	/**
	 * @see	Page::show()
	 */
	public function show() {
		parent::show();
		@header('Content-Type: application/x-gzip');
		@header('Content-length: '.filesize($this->source->buildDirectory.$this->filename));
		@header('Content-disposition: attachment; filename="'.$this->filename.'"');
		readfile($this->source->buildDirectory.$this->filename);
		exit;
	}
}
?>