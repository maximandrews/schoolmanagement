Ext.define('Journal.store.AddCourseClassStore', {
	extend: 'Ext.data.Store',

	model: 'Journal.model.AddCourseClassModel',

	fields: ['abbr', 'name']

});