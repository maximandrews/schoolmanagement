Ext.define('Journal.controller.modules.LessonList', {
	extend: 'Journal.controller.Module',

	shortcutId: 'lesson-list',
	shortcutName: 'Priekšmeti',
	shortcutIconCls: 'lesson-list-icon',
	viewClass: 'lessontlist', // widget name of main view

	views: [ 'modules.LessonListView' ],
	models: [ 'LessonListModel'],
	stores: [ 'LessonListStore'],

	// View handlers  --------------------------- START

	/*editClickHandler: function (gd, opts) {
		var selection = this.getMView().items.getAt(0).getView().getSelectionModel().getSelection();
		if (selection[0]) {
			if(!selection[0].getId()) this.getMView().items.getAt(0).store.remove(selection);
		}
	},*/
	
	deleteLessonHandler:function ( row, rec, ri, opts ) {
		if (rec && rec.getId && !rec.getId())
			this.getMView().items.getAt(0).store.remove(rec);

		return true;
	},

	addClickHandler: function(){
		var selection = this.getMView().items.getAt(0).getView().getSelectionModel().getSelection();
			this.getMView().items.getAt(0).store.insert(0, new Ext.getCmp('cr_id'));
			this.getMView().rowEditing.startEdit(0, 0);
	},

	removeClickHandler: function(){
		var selection = this.getMView().items.getAt(0).getView().getSelectionModel().getSelection(),
			store = this.getMView().items.getAt(0).store;
		if (selection.length > 0) {
			store.remove(selection);
			this.getMView().rowEditing.cancelEdit(true);
			store.sync();
			store.loadPage(store.currentPage);
		}
	},

	completeEditHandler: function(editor, e) {
		e.store.sync();
		e.store.loadPage(e.store.currentPage);
	}

	// View handlers  --------------------------- END
});
