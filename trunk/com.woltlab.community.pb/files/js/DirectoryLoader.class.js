/**
 * @author	Alexander Ebert
 * @copyright	2009-2011 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
var DirectoryLoader = Class.create({
	/**
	 * Initializes class once document is loaded.
	 */
	initialize: function(selectField, targetSelectField) {
		this.directories = $H();
		this.selectField = null;
		this.targetSelectField = null;
		
		document.observe('dom:loaded', function() {
			this.selectField = $(selectField);
			this.targetSelectField = $(targetSelectField);
			
			this.selectField.observe('change', this.loadDirectories.bind(this));
		}.bind(this));
	},
	/**
	 * Inserts available directories.
 	 */
	loadDirectories: function() {
		var packageName = this.selectField.getValue().strip();
		
		// load directories if not already cached
		var directories = this.directories.get(packageName);
		if (!directories) {
			new Ajax.Request('index.php?action=LoadDirectories&s=' + SID_ARG_2ND, {
				method: 'post',
				parameters: { packageName: packageName, sourceID: SOURCE_ID },
				onSuccess: function(transport) {
					this.directories.set(packageName, transport.responseText.evalJSON(true));
					this.insertOptions(packageName);
				}.bind(this)
			});
		}
		else {
			this.insertOptions(packageName);
		}
	},
	/**
	 * Removes previous options and replaces them with requested ones.
	 */
	insertOptions: function(packageName) {
		var directories = this.directories.get(packageName);
		
		// remove options
		this.targetSelectField.childElements().each(function(childElement) {
			childElement.remove();
		});
		
		// insert new options
		directories.each(function(directory) {
			var option = new Element('option', { value: directory.path }).update(directory.version + ' - ' + directory.path);
			this.targetSelectField.insert(option);
		}.bind(this));
		
		this.targetSelectField.up().show();
	}
});