<?php
require_once(WCF_DIR.'lib/system/WCFACP.class.php');

/**
 * PackageBuilder ACP implementation
 *
 * @author	Alexander Ebert
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	system
 * @category 	PackageBuilder
 */
class PBACP extends WCFACP {
	/**
	 * @see WCF::getOptionsFilename()
	 */
	protected function getOptionsFilename() {
		return PB_DIR.'options.inc.php';
	}

	/**
	 * Initialises the template engine.
	 */
	protected function initTPL() {
		global $packageDirs;

		self::$tplObj = new ACPTemplate(self::getLanguage()->getLanguageID(), ArrayUtil::appendSuffix($packageDirs, 'acp/templates/'));
		$this->assignDefaultTemplateVariables();
	}

	/**
	 * Does the user authentication.
	 */
	protected function initAuth() {
		parent::initAuth();

		// user ban
		if (self::getUser()->banned) {
			throw new PermissionDeniedException();
		}
	}

	/**
	 * @see WCF::assignDefaultTemplateVariables()
	 */
	protected function assignDefaultTemplateVariables() {
		parent::assignDefaultTemplateVariables();

		self::getTPL()->assign(array(
			// add jump to page link
			'additionalHeaderButtons' => '<li><a href="'.RELATIVE_PB_DIR.'index.php?page=Index"><img src="'.RELATIVE_PB_DIR.'icon/indexS.png" alt="" /> <span>'.WCF::getLanguage()->get('pb.acp.jumpToPage').'</span></a></li>',
			// individual page title
			'pageTitle' => WCF::getLanguage()->get(StringUtil::encodeHTML(PAGE_TITLE)) . ' - ' . StringUtil::encodeHTML(PACKAGE_NAME . ' ' . PACKAGE_VERSION)
		));
	}
}
?>