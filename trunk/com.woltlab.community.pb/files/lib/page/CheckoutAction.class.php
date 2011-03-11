<?php
// pb imports
require_once(PB_DIR.'lib/data/source/SourceEditor.class.php');

// wcf imports
require_once(WCF_DIR.'lib/action/AbstractAction.class.php');
require_once(WCF_DIR.'lib/system/scm/SCMHelper.class.php');

/**
 * Checks out a repository.
 *
 * @author	Tim Düsterhus, Alexander Ebert
 * @copyright	2009-2011 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	action
 * @category 	PackageBuilder
 */
class CheckoutAction extends AbstractAction {
	/**
	 * Check out repository
	 * 
	 * @var	boolean
	 */
	public $checkoutRepository = false;
	
	/**
	 * Rebuild package dependencies
	 * 
	 * @var	boolean
	 */
	public $rebuildPackageData = false;
	
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
		
		if (isset($_REQUEST['checkoutRepository'])) $this->checkoutRepository = true;
		if (isset($_REQUEST['sourceID'])) $this->sourceID = intval($_REQUEST['sourceID']);
		if (isset($_REQUEST['rebuildPackageData'])) $this->rebuildPackageData = true;
		
		WCF::getUser()->checkPermission('user.source.general.canViewSources');
		
		$this->source = new Source($this->sourceID);
		if (!$this->source->sourceID || !$this->source->hasAccess()) throw new IllegalLinkException();
	}

	/**
	 * @see Action::execute()
	 */
	public function execute() {
		// call execute event
		parent::execute();
		
		if ($this->checkoutRepository) {
			// load scm driver
			$className = ucfirst(Source::validateSCM($this->source->scm));
			
			// check out repository
			require_once(WCF_DIR.'lib/system/scm/'.$className.'.class.php');
			call_user_func(array($className, 'checkout'), $this->source->url, $this->source->sourceDirectory, array('username' => $this->source->username, 'password' => $this->source->password));
			
			// set revision
			$revision = $this->source->getHeadRevision();
			$this->source->update(null, null, null, null, null, null, null, $revision);
		}
		
		// rebuild package data if requested
		if ($this->rebuildPackageData) {
			require_once(PB_DIR.'lib/system/package/PackageHelper.class.php');
			PackageHelper::readPackages($this->source);
		}
		
		// call executed event
		$this->executed();
		
		// forward
		HeaderUtil::redirect('index.php?page=SourceView&sourceID=' . $this->source->sourceID . SID_ARG_2ND_NOT_ENCODED);
		exit;
	}
}
?>