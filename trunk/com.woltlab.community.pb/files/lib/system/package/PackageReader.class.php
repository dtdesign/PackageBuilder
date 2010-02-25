<?php
// pb imports
require_once(PB_DIR.'lib/data/source/Source.class.php');

/**
 * Reads package informations.
 *
 * @author	Alexander Ebert
 * @copyright	2009-2010 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	system
 * @category 	PackageBuilder
 */
class PackageReader {
	private $file = '';
	private $package = array();
	private $skipPackages = false;
	private $source = null;
	private $xml = null;

	/**
	 * @param	mixed	$source
	 * @param	string	$file
	 * @param	boolean	$readArchive
	 * @param	boolean	$readReferencedPackages
	 */
	public function __construct($source, $file, $readArchive = false, $readReferencedPackages = true) {
		if ($source instanceof Source) {
			$this->source = $source;
		}
		else {
			if ($source === null) {
				throw new SystemException('No source given.');
			}
			$source = intval($source);

			// check if a sourceID is given
			if ($source) {
				$this->source = new Source($source);
			}
			else {
				if (!$readArchive) {
					throw new SystemException('Missing sourceID');
				}
			}
		}

		$this->file = StringUtil::trim($file);

		if ($readArchive) {
			// extract package.xml from archive
			$this->readArchive();
			// there is no need to read optional or required packages
			$this->skipPackages = true;
		}
		else {
			if (!$readReferencedPackages) {
				$this->skipPackages = true;
			}

			$this->file = $this->source->sourceDirectory.$this->file.'/package.xml';

			if (!file_exists($this->file) || !is_readable($this->file)) {
				throw new SystemException('package.xml missing in '.str_replace('package.xml', '', $this->file).'.');
			}
		}

		// read package information
		$this->readPackageXml($readArchive);
	}

	/**
	 * Extracts package.xml from archive
	 */
	private function readArchive() {
		// wcf imports
		require_once(WCF_DIR.'lib/system/io/Tar.class.php');

		// extract the package.xml from archive
		$archive = new Tar($this->file);
		$this->file = $this->source->buildDirectory.'/package.'.sha1(microtime()).'.xml';
		$archive->extract('package.xml', $this->file);
		$archive->close();
	}

	/**
	 * Reads data from package.xml
	 *
	 * @param	boolean	$readArchive
	 */
	private function readPackageXml($readArchive) {
		if (!is_file($this->file)) {
			return;
		}

		$this->xml = new XML($this->file);
		$data = $this->xml->getAttributes();

		// get package name
		if (!array_key_exists('name', $data)) {
			throw new SystemException('package.xml is invalid, missing name.');
		}

		$this->package['name'] = $data['name'];
		$nodes = $this->xml->getChildren();

		// extract additional information from xml
		foreach($nodes as $node) {
			$this->readNode($node);
		}

		// remove temporary package.xml
		if ($readArchive) {
			@unlink($this->file);
		}
	}

	/**
	 * Search data within a node
	 *
	 * @param	object	$node
	 */
	private function readNode(SimpleXMLElement $node) {
		$packageType = '';

		// read package version from xml
		if (!array_key_exists('version', $this->package) && (StringUtil::toLowerCase($node->getName()) == 'packageinformation')) {
			$children = $this->xml->getChildren($node);

			foreach ($children as $child) {
				if (StringUtil::toLowerCase($child->getName()) == 'version') {
					$this->package['version'] = $this->xml->getCDATA($child);
				}
			}
		}

		// skip required and optional packages when working with an archive
		if (!$this->skipPackages) {
			if (!array_key_exists('requiredpackages', $this->package) && (StringUtil::toLowerCase($node->getName()) == 'requiredpackages')) {
				// required packages found
				$packageType = 'requiredpackage';
			}

			if (!array_key_exists('optionalpackages', $this->package) && (StringUtil::toLowerCase($node->getName()) == 'optionalpackages')) {
				// optional packages found
				$packageType = 'optionalpackage';
			}
		}

		// read all requested packages
		if (!empty($packageType)) {
			$children = $this->xml->getChildren($node);

			foreach ($children as $child) {
				$attributes = $this->xml->getAttributes($child);
				$packageName = $this->xml->getCDATA($child);

				$this->package[$packageType][$packageName] = array(
					'minversion' => (isset($attributes['minversion'])) ? $attributes['minversion'] : '',
					'file' => (isset($attributes['file'])) ? $attributes['file'] : ''
				);
			}
		}
	}

	/**
	 * Returns all gathered package data
	 */
	public function getPackageData() {
		return $this->package;
	}
}
?>