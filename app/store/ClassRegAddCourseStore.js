Ext.define('Journal.store.ClassRegAddCourseStore', {
	extend: 'Ext.data.Store',

	fields: [
		'cc_id',
		'cc_cl_id',
		'cc_cl_txt',
		'cc_cr_id',
		'cc_cr_txt',
		'cc_teacher',
		'cc_teacher_txt',
		'cc_hoursweek',
		'cc_statuss'
	],
	remoteSort: true,
	remoteFilter: true,

	proxy: {
		type: 'ajax',
		api: {
			read:'data/index.php?t=classescourses/main',
			destroy:'data/index.php?t=classescourses/modify&action=delete'
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
	},

	listeners: {
		beforeload: function (store, op, eOpts) {
			var pForm = Ext.getCmp('class-reg-user-add-form');
		
			if(pForm) {
				var vals = pForm.getValues();

				if(store && store.getProxy) {
					store.getProxy().setExtraParam('classId', vals.cl_id);
				}
			}
		}
	}

});