Ext.define('Journal.store.UserRegDataStore', {
	extend: 'Ext.data.Store',

	model: 'Journal.model.UserRegDataModel',
	storeId: 'ur-reg-data',
	remoteSort: true,
	remoteFilter: true,
	pageSize: 23
});
