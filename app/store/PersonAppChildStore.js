Ext.define('Journal.store.PersonAppChildStore', {
	extend: 'Ext.data.Store',

	fields: [
		'ps_id',
		'ps_firstname',
		'ps_lastname',
		{name:'ps_birthdate', type:'date', dateFormat: 'Y-m-d H:i:s'},
		'ps_personcode',
		'ps_cl_id',
		'ps_cl_txt'
	],

	remoteSort: true,
	remoteFilter: true,

	proxy: {
		type: 'ajax',
		api: {
			read:'data/index.php?t=childparentsadd/main',
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
			var pForm = Ext.getCmp('user-reg-user-edit-form');
		
			if(pForm) {
				var vals = pForm.getValues();

				if(store && store.getProxy)
					store.getProxy().setExtraParam('childId', vals.ps_id);
			}
		}
	}
});