Ext.define('Journal.model.LessonListModel', {
	extend: 'Ext.data.Model',
	idProperty : 'cr_id',
	fields: [
		'cr_id',
		'cr_name',
		'cr_level',
		'cr_hours'
	],

	validations: [
		{ type: 'presence', field: 'cr_name' }
	],

	proxy: {
		type: 'ajax',
		api: {
			create:'data/index.php?t=coursesgrid/modify&action=create',
			read:'data/index.php?t=coursesgrid/main',
			update:'data/index.php?t=coursesgrid/modify&action=update',
			destroy:'data/index.php?t=coursesgrid/modify&action=delete'
		},
		reader: {
			type:'json',
			root:'values',
			totalProperty:'total',
			successProperty:'success'
		},
		writer: {
			type:'json',
			encode: true,
			root:'ids'
		}
	}
});