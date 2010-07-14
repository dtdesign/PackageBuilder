<?php
/**
 * @deprecated This class is currently nowhere needed. It will maybe be deleted in a further version
 */
class SimplePackage {
	public $packageData = array();

	public function __construct($packageData) {
		if (is_array($packageData)) {
			$this->packageData = $packageData;
		}
	}

	public function __get($identifier) {
		if (isset($this->packageData[$identifier])) {
			return $this->packageData[$identifier];
		}

		return null;
	}
}
?>