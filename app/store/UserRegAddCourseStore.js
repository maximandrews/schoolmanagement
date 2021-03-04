Ext.define('Journal.store.UserRegAddCourseStore', {
	extend: 'Ext.data.ArrayStore',

	model: 'Journal.model.UserRegAddCourseModel',

	data: [
		['Latviešu valoda','5','10.a','klase','12'],
		['Latviešu valoda','5','11.a','grupa','22'],
		['Literatūra','4','4','klase','20'],
		['Literatūra','4','5.c','grupa','15']
	]

});