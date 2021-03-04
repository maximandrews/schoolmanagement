Ext.define('Journal.model.ScheduleModel', {
	extend: 'Ext.data.Model',
	idProperty: 'sc_id',
	fields: [
		'sc_id',
		'sc_lt_id', // lesson theme
		{ name: 'sc_date',type: 'date', dateFormat: 'd.m.Y'},
		'sc_wday',
		'sc_from',
		'sc_until',
		'sc_cl_id',
		'sc_cr_id',
		'sc_cc_id',
		'sc_lt_id', // lesson theme
		'sc_created',
		'sc_modified',
		'sc_selected',
		'freq_count',
		'freq_period',
		{ name: 'sc_till', type: 'date', dateFormat: 'Y-m-d H:i:s'},
		'sc_parent'
	]/*,
	validations: [{
		type: 'presence',
        field: 'sc_cr_id',
    }, {
		type: 'presence',
        field: 'sc_from',
    }]*/
});
