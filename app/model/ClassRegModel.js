Ext.define('Journal.model.ClassRegModel', {
	extend: 'Ext.data.Model',
	idProperty: 'cl_id',
	//cl_id, cl_level, cl_postfix, cl_year, cl_teacher, cl_created, cl_modified
	fields: [
		'cl_id',
		'cl_level',
		'cl_postfix',
		'cl_txt', //virtual
		'cl_year',
		'cl_teacher',
		'cl_teacher_txt', //virtual
		'cl_ps_count', //virtual
		'cl_created',
		'cl_modified'
	],
	proxy: {
		type: 'ajax',
		api: {
			read:'data/index.php?t=classesgrid/main',
			destroy:'data/index.php?t=classesgrid/modify&action=delete'
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
