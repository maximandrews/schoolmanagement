Ext.define('Journal.view.modules.ChildPretendentsView', {
	extend: 'Ext.window.Window',
	alias: 'widget.childpredendents',

	requires: [
		'Ext.window.*',
		'Ext.form.*'
    ],
		
	title: 'Pieteikt bērnu',
	modal: true,
	border: false,
	frame: false,
	width: 330,
	height: 350,
	buttonAlign: 'center',

	initComponent: function () {
		var me = this,
				ctrl = me.controller;

		me.items = [{
				bodyStyle:  'padding: 5px; background:#dfe8f6;',
				border: false,
				frame: false,
				xtype: 'form',
				layout: {
					type: 'vbox',
					align: 'stretch'
				},
				layout: 'anchor',
				flex: 1,
				defaults: {
					labelWidth: 120,
					anchor: '100%',
					labelAlign: 'right',
				},
				defaultType: 'textfield',
				items: [{
					xtype      : 'radiogroup',
					fieldLabel : 'Dzimums',
					items: [
						{ xtype: 'tbspacer', flex: 1 },
						{ boxLabel: 'Sieviešu', name: 'woman', id: 'radio1'},
						{ xtype: 'tbspacer', flex: 1 },
						{ boxLabel: 'Viriešu', name: 'man', id: 'radio2'},
						{ xtype: 'tbspacer', flex: 1 }
					]
				},{
					fieldLabel: 'E-pasts/Lietotājvārds',
					name: 'ps_email'
				},{
					fieldLabel: 'Vārds',
					name: 'ps_firstname'
				},{
					fieldLabel: 'Uzvārds',
					name: 'ps_lastname'
				},{
					fieldLabel: 'Personas kods',
					name: 'ps_pk'
				},{
					xtype: 'numberfield',
					name: 'class',
					fieldLabel: 'Līmenis (klase)',
					minValue: 1,
					maxValue: 12
				},{
					xtype: 'textarea',
					fieldLabel: 'Speciālie norādījumi',
					name: 'pt_text',
					height: 100
				}
			]
		}];

		me.buttons = [{ 
			text: 'Pieteikt',
			iconCls: 'icon-accept',
			itemId: 'accept',
			margin: 1,
		},{ 
			text: 'Aizvērt',
			itemId: 'reject',
			margin: 1,
			handler: me.close,
			scope: me
		}];

		me.callParent();
	}
});