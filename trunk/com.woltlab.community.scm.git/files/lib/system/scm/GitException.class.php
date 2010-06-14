<?php
/**
 * Removes critical data from git exceptions
 *
 * @author	Alexander Ebert
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.scm.git
 * @subpackage	system.git
 * @category 	PackageBuilder
 */
class GitException extends SystemException {
	/**
	 * Removes critical data from stack trace.
	 *
	 * @see Exception::getTraceAsString()
	 */
	public function __getTraceAsString() {
		return preg_replace('/Git\:\:(.*)\((.*)/', 'Git::$1(...)', $this->getTraceAsString());
	}
}
?>