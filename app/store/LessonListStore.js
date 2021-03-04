Ext.define('Journal.store.LessonListStore', {
	extend: 'Ext.data.Store',

	model: 'Journal.model.LessonListModel',
	remoteSort: true,
	remoteFilter: true,
	pageSize: 20
});