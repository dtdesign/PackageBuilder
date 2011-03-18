<?php
// pb imports
require_once(PB_DIR.'lib/data/source/file/SourceFile.class.php');

/**
 * Provides methods to create and edit a source file.
 *
 * @author	Alexander Ebert
 * @copyright	2009-2011 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	com.woltlab.community.pb
 * @subpackage	data.source.file
 * @category 	PackageBuilder
 */
class SourceFileEditor extends SourceFile {
	/**
	 * Creates a new source file entry.
	 * 
	 * @param	integer		$sourceID
	 * @param	string		$location
	 * @param	string		$type
	 * @param	integer		$fileDate
	 * @return	SourceFile
	 */
	public static function create($sourceID, $location, $type, $profileName = '', $fileDate = TIME_NOW) {
		$fileVersion = $packageName = '';
		
		// get filename
		$filename = basename($location);
		
		// set file version based upon type
		if ($type != 'wcfsetup') {
			require_once(PB_DIR . 'lib/system/package/PackageReader.class.php');
			$pr = new PackageReader($sourceID, $location, true);
			$data = $pr->getPackageData();
			
			$fileVersion = $data['version'];
			$packageName = $data['name'];
			$type = 'package';
		}
		
		$sql = "INSERT INTO	pb".PB_N."_source_file
					(sourceID, hash, filename, fileType, fileVersion, fileDate, packageName, profileName)
			VALUES		(".$sourceID.",
					'".escapeString(StringUtil::getRandomID())."',
					'".escapeString($filename)."',
					'".$type."',
					'".escapeString($fileVersion)."',
					".$fileDate.",
					'".escapeString($packageName)."',
					'".escapeString($profileName)."')";
		WCF::getDB()->sendQuery($sql);
		
		$fileID = WCF::getDB()->getInsertID('pb'.PB_N.'_source_file', 'fileID');
		$sourceFile = new SourceFile($fileID);
		
		// move file
		if (!copy($location, PB_DIR . 'packages/' . $sourceFile->fileID . '-' . $sourceFile->hash)) {
			$sql = "DELETE FROM	pb".PB_N."_source_file
				WHERE		fileID = ".$sourceFile->fileID;
			WCF::getDB()->sendQuery($sql);
			
			throw new SystemException("Could not move source file, resource missing or insufficient permissions.");
		}
		
		@unlink($location);
		
		return $sourceFile;
	}
	
	/**
	 * Updates this source file.
	 * 
	 * @param	array		$parameters
	 */
	public function update(array $parameters) {
		$updateSQL = '';
		
		foreach ($parameters as $fieldName => $fieldValue) {
			if (!empty($updateSQL)) $updateSQL .= ',';
			
			$updateSQL .= $fieldName . ' = ';
			if (is_numeric($fieldValue)) $updateSQL .= $fieldValue;
			else $updateSQL .= "'".escapeString($fieldValue)."'";
		}
		
		if (!empty($updateSQL)) {
			$sql = "UPDATE	pb".PB_N."_source_file
				SET	".$updateSQL."
				WHERE	fileID = " . $this->fileID;
			WCF::getDB()->sendQuery($sql);
		}
	}
	
	/**
	 * Deletes this source file.
	 */
	public function delete() {
		$sql = "DELETE FROM	pb".PB_N."_source_file
			WHERE		fileID = " . $this->fileID;
		WCF::getDB()->sendQuery($sql);
		
		@unlink(PB_DIR . 'packages/' . $this->fileID . '-' . $this->hash);
	}
}
?>