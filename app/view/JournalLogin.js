Ext.define('Journal.view.JournalLogin', {
	extend: 'Ext.window.Window',
	alias: 'widget.journallogin',

	requires: [
		'Ext.form.*',
		'Ext.data.*'
	],

	app: null,

	title: 'Pieslēgties skolas elektroniskājām žurnālam',
	border: false,
	frame: false,
	width: 300,
	height: 120,
	layout: 'fit',
	modal: true,

	initComponent: function () {
		var me = this;

		me.resizable = false;
		me.constrain = true;
		me.draggable = false;
		me.closable = false;

		me.items = {
			xtype: 'form',
			border: false,
			frame: false,
			bodyStyle:  'background:#dfe8f6;',
			bodyPadding: 5,
			layout: {
				type: 'vbox',
				align: 'stretch'
			},
			defaults: {
				anchor: '100%',
				labelAlign: 'right',
				labelWidth: 60
			},
			items: [
				{ xtype:'textfield', fieldLabel:'E-pasts', name:'username' },
				{ xtype:'textfield', fieldLabel:'Parole', name:'password', inputType: 'password' }
			],
			buttons: [{
				text: 'Pieslēgties',
				handler: me.app.doLogin,
				scope: me.app
			}],
			buttonAlign: 'center'
		};

		me.renderTo = Ext.getBody();

		me.callParent();
	}
});