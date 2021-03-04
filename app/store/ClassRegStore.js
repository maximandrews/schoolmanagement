Ext.define('Journal.store.ClassRegStore', {
	extend: 'Ext.data.Store',

	model: 'Journal.model.ClassRegModel',
	remoteSort: true,
	remoteFilter: true,
	pageSize: 100
});