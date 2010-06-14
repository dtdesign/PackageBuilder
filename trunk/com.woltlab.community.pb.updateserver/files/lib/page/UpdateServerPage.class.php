<?php
// pb imports
require_once(PB_DIR.'lib/data/source/Source.class.php');

// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

/**
 * Displays the valid XML for an update-server
 *
 * @author	Tim Düsterhus
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb.updateserver
 * @category 	PackageBuilder
 */
class UpdateServerPage extends AbstractPage {
	/**
	 * Source Object
	 *
	 * @var Source
	 */
	public $source = NULL;
	
	/**
	 * Type of packages wanted
	 * can be stable, unstable or testing
	 *
	 * @var string
	 */
	public $type = 'stable';
	
	/**
	 * @see Page::readParameters
	 */
	public function readParameters() {
		// if there is no user logged in try to get valid logindata
		if (!WCF::getUser()->userID && function_exists('getallheaders')) {
			if (!isset($_SERVER['PHP_AUTH_USER']) && !isset($_SERVER['PHP_AUTH_PW'])) {
		    		$this->authenticate();
			}
			else {
				$this->user = new UserSession(null, null, $_SERVER['PHP_AUTH_USER']);
				if (!$this->user->checkPassword($_SERVER['PHP_AUTH_PW'])) {
					$this->authenticate();
				}
			}
		}
		// use WCF::getUser();
		else {
			$this->user = WCF::getUser();
		}
		
		$sourceID = 0;
		if (isset($_REQUEST['sourceID'])) $sourceID = $_REQUEST['sourceID'];
		if (isset($_REQUEST['type'])) $this->type = StringUtil::trim($_REQUEST['type']);
		
		$this->source = new Source($sourceID);
		if (!$this->source->sourceID) throw new IllegalLinkException();
		if (!$this->source->hasAccess($this->user)) throw new PermissionDeniedException();
	}
	
	/**
	 * Generates header ouput for authentification
	 *
	 * @return void
	 */
	protected function authenticate() {
		@header('HTTP/1.1 401 Unauthorized');
		@header('WWW-Authenticate: Basic realm="Please enter username and password for ' . PAGE_TITLE . '"');
		echo 'HTTP/1.0 401 Unauthorized';
    		exit;
	}
	/**
	 * @see Page::show();
	 */
	public function show() {
		$this->user->checkPermission('user.source.general.canViewSources');
		parent::show();
		WCF::getCache()->addResource(
			'update-'.$this->source->sourceID.'-'.$this->type,
			PB_DIR.'cache/cache.update-'.$this->source->sourceID.'-'.$this->type.'.php',
			PB_DIR.'lib/system/cache/CacheBuilderUpdateServer.class.php',
			0,
			3600
		);
		$xml = WCF::getCache()->get('update-'.$this->source->sourceID.'-'.$this->type);
		@header('Content-Type: text/xml');
		echo $xml->asXML();
		exit;
	}
}
?>