<?php
// wcf imports
require_once(WCF_DIR.'lib/acp/package/plugin/AbstractXMLPackageInstallationPlugin.class.php');
require_once(WCF_DIR.'lib/system/scm/SCMHelper.class.php');

/**
 * Provides PIP for source code management systems.
 *
 * @author	Alexander Ebert
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.scm
 * @subpackage	acp.package.plugin
 * @category 	PackageBuilder
 */
class SCMPackageInstallationPlugin extends AbstractXMLPackageInstallationPlugin {
	public $tagName = 'scm';
	public $tableName = 'scm';

	/**
	 * Installs scm.
	 *
	 * @see	AbstractXMLPackageInstallationPlugin::install()
	 */
	public function install() {
		parent::install();

		if (!$xml = $this->getXML()) {
			return;
		}

		$instanceNo = WCF_N.'_'.$this->installation->getPackage()->getParentPackage()->getInstanceNo();

		$scmXML = $xml->getElementTree('data');

		foreach ($scmXML['children'] as $key => $block) {
			if (!empty($block['children'])) {
				switch($block['name']) {
					// install (or update existing) scm
					case 'import':
						foreach ($block['children'] as $scm) {
							foreach ($scm['children'] as $child) {
								// continue with next children if current tree is empty
								if (!isset($child['cdata'])) {
									continue;
								}

								$scm[$child['name']] = $child['cdata'];
							}

							// break operation if we encounter missing attribute
							if (!isset($scm['attrs']['name'])) {
								throw new SystemException('Required "name" attribute for scm item tag is missing.');
							}

							$scmName = $scm['attrs']['name'];

							// insert into db
							$sql = "INSERT INTO	wcf".WCF_N."_".$this->tableName."
									   	(scm)
								VALUES		('".$scmName."')
								ON DUPLICATE KEY UPDATE scm = VALUES(scm)";
							WCF::getDB()->sendQuery($sql);

							// clear cache
							SCMHelper::clearCache();
						}
					break;

					// delete scm
					case 'delete':

						if ($package->getAction() == 'update') {
							$itemNames	= '';

							foreach ($block['children'] as $scm) {
								if (!isset($scm['attrs']['name'])) {
									throw new SystemException('Required "name" attribute for scm tag is missing.');
								}

								if (!empty($itemNames)) {
									$itemNames .= ',';
								}

								$itemNames .= "'".escapeString($scm['attrs']['name'])."'";
							}

							if (!empty($itemNames)) {
								$sql = "DELETE FROM	wcf".WCF_N."_".$this->tableName."
							 		WHERE		scm IN (".$itemNames.")";
								WCF::getDB()->sendQuery($sql);

								// clear cache
								SCMHelper::clearCache();
							}
						}
					break;
				}
			}
		}
	}

	/**
	 * Determine wether data needs to be removed
	 *
	 * @see	PackageInstallationPlugin::hasUninstall()
	 */
	public function hasUninstall() {
		return false;
	}

	/**
	 * Removes associated data from database
	 *
	 * @see	PackageInstallationPlugin::uninstall()
	 */
	public function uninstall() {
		return null;
	}
}
?>