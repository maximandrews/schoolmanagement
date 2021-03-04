Ext.define('Journal.model.UserRegChildModel', {
	extend: 'Ext.data.Model',
//ps_id, ps_email, ps_password, ps_ut_id, ps_ut_text, ps_firstname, ps_lastname, ps_birthdate, ps_mailsms, ps_cl_id, ps_cl_txt, ps_created, ps_modified
	fields: [
		'ps_id',
		'ps_email',
		'ps_password',
		'repass_ps_password',
		'ps_ut_id',
		'ps_ut_text',
		'ps_firstname',
		'ps_lastname',
		'ps_birthdate',
		'ps_mailsms',
		'ps_cl_id',
		'ps_cl_txt',
		'ps_created',
		'ps_modified'
	]
});