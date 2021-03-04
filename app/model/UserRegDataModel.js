Ext.define('Journal.model.UserRegDataModel', {
	extend: 'Ext.data.Model',
	idProperty: 'ps_id',
	fields: [
		'ps_id',
		'ps_email',
		'ps_password',
		'repass_ps_password',
		'ps_ut_id',
		'ps_ut_text',
		'ps_firstname',
		'ps_lastname',
		{name:'ps_birthdate', type:'date', dateFormat: 'Y-m-d H:i:s'},
		'ps_personcode',
		'ps_mailsms',
		'ps_cl_id',
		'ps_cl_txt',
		'ps_created',
		'ps_modified'
	],
	validations: [
		{	type: 'presence',  field: 'ps_email' },
		{	type: 'email',  field: 'ps_email' },
		{	type: 'presence',  field: 'ps_ut_id' },
		{	type: 'presence',  field: 'ps_firstname' },
		{	type: 'presence',  field: 'ps_lastname' },
		{	type: 'presence',  field: 'ps_birthdate' } /*,
		{	type: 'length',    field: 'name',     min: 2},
		{	type: 'inclusion', field: 'gender',   list: ['Male', 'Female']},
		{	type: 'exclusion', field: 'username', list: ['Admin', 'Operator']},
		{	type: 'format',    field: 'username', matcher: /([a-z]+)[0-9]{2,3}/}*/
	],
	proxy: {
		type: 'ajax',
		api: {
			read:'data/index.php?t=usersgrid/main',
			destroy:'data/index.php?t=usersgrid/modify&action=delete'
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
