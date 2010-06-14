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
	 * Directorysizes
	 *
	 * @var array
	 */
	public $size = array('build' => 0, 'repository' => 0);

	/**
	 * @see	Page::readData()
	 */
	public function readData() {
		

		$this->requiredFunctions = array(
			'filesystem'	=> array('copy'),
			'system'	=> array('escapeshellcmd', 'exec')
		);
		
		parent::readData();
		
		// mark all disabled functions
		foreach ($this->requiredFunctions as $functionType => $functions) {
			foreach ($functions as $function) {
				if (!function_exists($function)) $this->disabledFunctions[$functionType][] = $function;
			}
		}
		/*
		$this->size['build'] = FileUtil::formatFilesize(DirectoryUtil::getInstance(PB_DIR . 'build')->getSize());
		$this->size['repository'] = FileUtil::formatFilesize(DirectoryUtil::getInstance(PB_DIR . 'repository')->getSize());*/
	}

	/**
	 * @see	Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
			'disabledFunctions' => $this->disabledFunctions,
			'size' => $this->size
		));
	}
}
?>