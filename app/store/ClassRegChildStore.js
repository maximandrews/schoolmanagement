Ext.define('Journal.store.ClassRegChildStore', {
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
			read:'data/index.php?t=classregchildren/main',
			//destroy:'data/index.php?t=classregchildren/modify&action=deleteChild'
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

				if(store && store.getProxy)
					store.getProxy().setExtraParam('childClass', vals.cl_id);
			}
		}
	}
});