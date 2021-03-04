Ext.define('Journal.store.UserRegParentStore', {
	extend: 'Ext.data.Store',

	fields: [
		'ps_id',
		'ps_firstname',
		'ps_lastname',
		{name:'ps_birthdate', type:'date', dateFormat: 'Y-m-d H:i:s'},
		'ps_personcode',
		'ps_email'
	],
	storeId: 'user-reg-person-parent-store',
	remoteSort: true,
	remoteFilter: true,

	proxy: {
		type: 'ajax',
		api: {
			read:'data/index.php?t=childparents/main',
			destroy:'data/index.php?t=childparents/modify&action=deleteParent'
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
			var pForm = Ext.getCmp('user-reg-user-edit-form');
		
			if(pForm) {
				var vals = pForm.getValues();

				if(store && store.getProxy)
					store.getProxy().setExtraParam('childId', vals.ps_id);
			}
		}
	}
});