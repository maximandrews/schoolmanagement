Ext.define('Journal.store.PretendentListStore', {
	extend: 'Ext.data.Store',

	model: 'Journal.model.PretendentListModel',
	remoteSort: true,
	remoteFilter: true,
	pageSize: 20

});