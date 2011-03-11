/**
 * @author	Alexander Ebert
 * @copyright	2009-2011 WoltLab Community
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 */
var RevisionLoader = Class.create({
	/**
	 * Loads revisions once document is loaded.
	 */
	initialize: function() {
		this.sources = [];
		
		document.observe('dom:loaded', function() {
			this.sources.each(function(sourceID) {
				this.loadRevision(sourceID);
			}.bind(this));
		}.bind(this));
	},
	/**
	 * Registers a source.
	 */
	registerSource: function(sourceID) {
		this.sources.push(sourceID);
	},
	/**
	 * Loads revision for a given source id.
	 */
	loadRevision: function(sourceID) {
		new Ajax.Request('index.php?action=LoadRevision' + SID_ARG_2ND, {
			method: 'post',
			parameters: { sourceID: sourceID },
			onSuccess: function(transport) {
				var data = transport.responseText.evalJSON(true);
				
				// update label
				$('sourceRevision' + sourceID).update(data.revision);
			}
		});
	}
});