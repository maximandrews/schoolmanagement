Ext.define('Journal.store.ClassRegTeacherStore', {
	extend: 'Ext.data.Store',

	storeId: 'class-reg-form-edit-teacher-combo',
	model: 'Journal.model.UserRegDataModel',
	remoteFilter: true,
	remoteSort: true,
	filters:[{ property: 'ps_ut_id', value: 3 }],
	sorters: [
		{ property : 'ps_lastname', direction: 'ASC' },
		{ property : 'ps_firstname', direction: 'ASC' }
	]
});