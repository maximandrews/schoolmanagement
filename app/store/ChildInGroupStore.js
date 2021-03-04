Ext.define('Journal.store.ChildInGroupStore', {
	extend: 'Ext.data.Store',

	fields: [
		'pc_ps_id',
		'pc_ps_firstname',
		'pc_ps_lastname',
		'pc_id'
	],

	remoteSort: true,
	remoteFilter: true,

	proxy: {
		type: 'ajax',
		api: {
			read:'data/index.php?t=personcoursesgrid/main',
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
			var pForm = Ext.getCmp('class-reg-user-add-form'),
					sForm = Ext.getCmp('add-course-item');
		
			if(pForm  && sForm) {
				var vals = pForm.getValues(),
						sVals = sForm.getValues();

				if(store && store.getProxy)
					store.getProxy().setExtraParam('classId', vals.cl_id);
					store.getProxy().setExtraParam('lessonId', sVals.cc_cr_id);
			}
		},
		load: function () {
			var sellmodel = Ext.getCmp('class-reg-data-grid').getView().getSelectionModel(),
			selection = sellmodel.getSelection();
			Ext.getCmp('class-reg-data-grid').store.each(function(record){	
				if(record.data.pc_id > 0 ) {
					sellmodel.select(record, true);
				}
			}, this );
		}
	}
});