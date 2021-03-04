Ext.define('Journal.store.AddCourseCourseStore', {
	extend: 'Ext.data.Store',

	storeId: 'add-course-course-store',
	remoteSort: true,
	remoteFilter: true,

	fields: [
		'cr_id',
		'cr_name'
	],

	proxy: {
		type: 'ajax',
		api: {
			read:'data/index.php?t=levelcourses/main',
		},
		reader: {
			type:'json',
			root:'values',
			totalProperty:'total',
			successProperty:'success'
		}
	},

	listeners: {
		beforeload: function (store, op, eOpts) {
			var pForm = Ext.getCmp('class-reg-user-add-form');
		
			if(pForm) {
				var vals = pForm.getValues();

				if(store && store.getProxy)
					store.getProxy().setExtraParam('classLvl', vals.cl_level);
			}
		}
	}
});
