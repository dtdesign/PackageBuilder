<?php
// wcf imports
require_once(WCF_DIR.'lib/page/AbstractPage.class.php');

/**
 * Basic statistics and information about this installation of PackageBuilder.
 *
 * @author	Alexander Ebert
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	lib.acp.page
 * @category 	PackageBuilder
 */
class IndexPage extends AbstractPage {
	/**
	 * Holds all disabled functions
	 *
	 * @var	array
	 */
	public $disabledFunctions = array();

	/**
	 * Holds all required functions
	 *
	 * @var	array<array>
	 */
	public $requiredFunctions = array();

	/**
	 * Template
	 *
	 * @var	string
	 */
	public $templateName = 'index';

	/**
	 * @see	Page::readData()
	 */
	public function readData() {
		parent::readData();

		$this->requiredFunctions = array(
			'filesystem'	=> array('copy'),
			'system'	=> array('escapeshellcmd', 'exec')
		);

		// mark all disabled functions
		foreach ($this->requiredFunctions as $functionType => $functions) {
			foreach ($functions as $function) {
				if (!function_exists($function)) $this->disabledFunctions[$functionType][] = $function;
			}
		}
	}

	/**
	 * @see	Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
			'disabledFunctions' => $this->disabledFunctions
		));
	}
}
?>
