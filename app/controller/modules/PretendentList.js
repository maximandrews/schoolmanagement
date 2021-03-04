Ext.define('Journal.controller.modules.PretendentList', {
	extend: 'Journal.controller.Module',

	shortcutId: 'pretendent-list',
	shortcutName: 'Pretendentu saraksts',
	shortcutIconCls: 'pretendent-list-icon',
	viewClass: 'pretendentlist', // widget name of main view

	views: [ 'modules.PretendentListView' ],
	models: [ 'PretendentListModel', 'ClassRegLevelModel', 'PretendentListPrefixModel'],
	stores: [ 'PretendentListStore', 'PretendentListLevelStore', 'PretendentListPrefixStore'],

	afterMainView: function (mView) {
		var me = this;

		var crcombo = mView.down('#pr-level-combo'),
			ptGrid = mView.items.getAt(0);
		if(crcombo) crcombo.select(crcombo.store.getAt(0));
		if(ptGrid){
			ptGrid.getSelectionModel().on('selectionchange', function (selModel, selections) {
				this.down('#accept').setDisabled(selections.length === 0);
			}, ptGrid);
		}
	},

	changeLevelHandler: function (cmb, newVal, oldVal) {
		var ptstore = this.getMView().items.getAt(0).store,
			crcombo = this.getMView().down('#pr-prefix-combo');
		if(ptstore) {
			ptstore.clearFilter();
			if(newVal != 'visi') {
				ptstore.filter('class', new RegExp('^'+newVal+'$','m'));
				if(crcombo) crcombo.select(crcombo.store.getAt(this.getMView().down('#pr-level-combo').getValue()));
			}
			/*	"g" - global match
				"i" - ignore case
				"m" - Treat beginning and end characters (^ and $) as working over multiple lines (i.e., match the beginning or end of each line (delimited by \n or \r), not only the very beginning or end of the whole input string)
			*/
		}
	},

	removeChildHandler: function (){
		var selection = this.getMView().items.getAt(0).getView().getSelectionModel().getSelection();
		if (selection.length > 0) {
			this.getMView().items.getAt(0).store.remove(selection);
			this.getMView().items.getAt(0).store.sync();
			this.getMView().items.getAt(0).store.loadPage(this.getMView().items.getAt(0).store.currentPage);
		}
	}
});