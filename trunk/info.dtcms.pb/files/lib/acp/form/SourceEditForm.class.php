<?php
// pb imports
require_once(PB_DIR.'lib/acp/form/SourceAddForm.class.php');

/**
 * A form to edit sources.
 *
 * @package	info.dtcms.pb
 * @author	Alexander Ebert
 * @copyright	2009-2010 Alexander Ebert IT-Dienstleistungen
 * @license	GNU Lesser Public License <http://www.gnu.org/licenses/lgpl.html>
 * @subpackage	acp.form
 * @category	PackageBuilder
 */
class SourceEditForm extends SourceAddForm {
	public $neededPermissions ='admin.source.canEditSources';
	public $action = 'edit';
	public $activeMenuItem = 'pb.acp.menu.link.content.source';
	public $source = null;

	/**
	 * @see	Page::readParameters()
	 */
	public function readParameters() {
		parent::readParameters();

		if (isset($_REQUEST['sourceID'])) $this->source = new Source($_REQUEST['sourceID']);
	}

	/**
	 * @see	Form::validate()
	 */
	public function validate() {
		parent::validate();

		if (!$this->source->sourceID) throw new IllegalLinkException;
	}

	/**
	 * @see	Page::readData()
	 */
	public function readData() {
		parent::readData();

		// break if handling post data
		if (!empty($_POST)) return;

		$this->name = $this->source->name;
		$this->sourceDirectory = $this->source->sourceDirectory;
		$this->buildDirectory = $this->source->buildDirectory;
		$this->useSubversion = $this->source->useSubversion;
		$this->url = $this->source->url;
		$this->username = $this->source->username;
		$this->password = $this->source->password;
		$this->trustServerCert = $this->source->trustServerCert;
	}

	/**
	 * @see Form::save()
	 */
	public function save() {
		AbstractForm::save();

  		$this->source->update(
  			$this->name,
  			$this->sourceDirectory,
  			$this->buildDirectory,
  			$this->useSubversion,
  			$this->url,
  			$this->username,
  			$this->password,
  			null,
  			$this->trustServerCert
		);

		// call saved event
		$this->saved();

		// show success message
		WCF::getTPL()->assign('success', true);
	}

	/**
	 * @see	Page::assignVariables()
	 */
	public function assignVariables() {
		parent::assignVariables();

		WCF::getTPL()->assign(array(
			'action' => $this->action,
			'sourceID' => $this->source->sourceID
		));
	}

	/**
	 * @see Page::show()
	 */
	public function show() {
		// check master password
		WCFACP::checkMasterPassword();

		parent::show();
	}
}
?>