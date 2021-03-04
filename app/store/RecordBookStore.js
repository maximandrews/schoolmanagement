Ext.define('Journal.store.RecordBookStore', {
	extend: 'Ext.data.ArrayStore',

	model: 'Journal.model.RecordBookModel',
	groupField: 'sc_date'
});