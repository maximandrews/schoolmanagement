Ext.define('Journal.model.PretendentListModel', {
	extend: 'Ext.data.Model',
	idProperty: 'pt_id',
	fields: [
	// pt_id, pt_firstnamechild, pt_lastnamechild, pt_personcodechild,  pt_genderchild, pt_cl_level, pt_comment,
// pt_firstnameparent, pt_lastnameparent, pt_personcodeparent, pt_password, pt_phonenumber, pt_mailsms,
// pt_created, pt_modified
		'pt_id',
		'pt_emailchild',
		'pt_firstnamechild',
		'pt_lastnamechild',
		'pt_personcodechild',
		{ name: 'pt_childbirthday', type:'date', dateFormat: 'dmy' },
		'pt_genderchild',
		'pt_cl_level',
		'pt_comment',
		'pt_emailparent',
		'pt_firstnameparent',
		'pt_lastnameparent',
		'pt_personcodeparent',
		'pt_password',
		'repass_pt_password',
		'pt_phonenumber',
		'pt_mailsms',
		'pt_created',
		'pt_modified'
	],
	validations: [
		{	type: 'presence',  field: 'pt_emailchild' },
		{	type: 'presence',  field: 'pt_firstnamechild' },
		{	type: 'presence',  field: 'pt_lastnamechild' },
		{	type: 'presence',  field: 'pt_personcodechild' },
		{	type: 'presence',  field: 'pt_childbirthday' },
		{	type: 'presence',  field: 'pt_genderchild' },
		{	type: 'presence',  field: 'pt_cl_level' },
		{	type: 'presence',  field: 'pt_emailparent' },
		{	type: 'presence',  field: 'pt_firstnameparent' },
		{	type: 'presence',  field: 'pt_lastnameparent' },
		{	type: 'presence',  field: 'pt_personcodeparent' },
		{	type: 'presence',  field: 'pt_password' },
		{	type: 'presence',  field: 'repass_pt_password' },
		{	type: 'email',  field: 'pt_emailparent' },
		{	type: 'email',  field: 'pt_emailchild' },
		{	type: 'email',  field: 'pt_mailsms' },
	/*	{	type: 'inclusion', field: 'pt_genderchild',   list: ['Sivieрu', 'Female']},
		{	type: 'length',    field: 'name',     min: 2},
		{	type: 'exclusion', field: 'username', list: ['Admin', 'Operator']},
		{	type: 'format',    field: 'username', matcher: /([a-z]+)[0-9]{2,3}/}*/
	],
	proxy: {
		type: 'ajax',
		api: {
			read:'data/index.php?t=pretendentsgrid/main',
			destroy:'data/index.php?t=pretendentsgrid/modify&action=delete'
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