Ext.define('Journal.store.ClassRegParentStore', {
	extend: 'Ext.data.Store',

	fields: [
		'ps_id',
		'ps_firstname',
		'ps_lastname',
		{name:'ps_birthdate', type:'date', dateFormat: 'Y-m-d H:i:s'},
		'ps_email'
	],
	remoteSort: true,
	remoteFilter: true,

	proxy: {
		type: 'ajax',
		api: {
			read:'data/index.php?t=classparents/main',
		//	destroy:'data/index.php?t=childparents/modify&action=deleteParent'
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
						//me = Ext.getCmp('user-reg-person-pupil-rel').store;

				if(store && store.getProxy)
					store.getProxy().setExtraParam('classId', vals.cl_id);
			}
		}
	}
});