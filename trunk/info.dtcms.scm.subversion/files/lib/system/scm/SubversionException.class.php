<?php
/**
 * @package		info.dtcms.svn
 * @author		Alexander Ebert
 * @copyright	2009 Alexander Ebert IT-Dienstleistungen
 * @license		GNU Lesser Public License <http://www.gnu.org/licenses/lgpl.html>
 * @subpackage	system.subversion
 */
class SubversionException extends SystemException {
	/**
	 * Removes data from stack trace.
	 * @see Exception::getTraceAsString()
	 */
	public function __getTraceAsString() {
		return preg_replace('/Subversion\:\:(.*)\((.*)/', 'Subversion::$1(...)', $this->getTraceAsString());
	}
}
?>