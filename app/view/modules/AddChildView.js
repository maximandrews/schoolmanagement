Ext.define('Journal.view.modules.AddChildView', {
	extend: 'Ext.window.Window',
	alias: 'widget.addchild',

	requires: [
		'Ext.window.*',
		'Ext.form.*'
    ],

	title: 'Skolēnu saraksts',
	modal: true,
	border: false,
	frame: false,
	width: 600,
	height: 330,
	buttonAlign: 'center',
	layout: 'fit',
	app: null,
	controller: null,
	classPupils: 'AddChildViewStore',

	initComponent: function () {
		var me = this,
				ctrl = me.controller;

		me.tbar = [{
			xtype: 'tbspacer', flex: 1 
		},{
			xtype:'textfield',
			fieldLabel: 'Vārds',
			name: 'ps_firstname',
			id: 'bar-ps-firsname',
			labelWidth: 40,
			width: 135,
			listeners: {
				change: me.app.childAddFiltration,
				scope: me
			}
		},{
			xtype: 'tbspacer', flex: 1 
		},{
			xtype:'textfield',
			fieldLabel: 'Uzvārds',
			name: 'ps_lastname',
			id: 'bar-ps-lastname',
			labelWidth: 50,
			width: 145,
			listeners: {
				change: me.app.childAddFiltration,
				scope: me
			}
		},{
			xtype: 'tbspacer', flex: 1 
		},{
			xtype:'textfield',
			fieldLabel: 'Personas kods',
			maxLength: 12,
			maxLengthText: 'Maksīmals laukuma garums: 12 simboli',
			id: 'bar-ps-pk',
			labelWidth: 80,
			width: 175,
			listeners: {
				change: me.app.childAddFiltration,
				scope: me
			}
		},{
			xtype: 'tbspacer', flex: 1 
		},{
			xtype: 'textfield',
			id:'bar-cl-name',
			name: 'cl_name',
			fieldLabel: 'Klase',
			labelWidth: 40,
			width: 100,
			labelAlign: 'right',
			editable: true,
			allowBlank: true,
			preventMark: true,
			listeners: {
				change: me.app.childAddFiltration,
				scope: me
			}
		},{
			xtype: 'tbspacer', flex: 1 
		}];

		me.items = [{
			xtype: 'grid',
			id: 'user-reg-person-rel-pupil-add',
			store: me.classPupils, //'AddChildViewStore',
			border: false,
			frame: false,
			selModel: Ext.create('Ext.selection.CheckboxModel'),
			columns: [
				{ text: 'Vārds', width: 100, sortable: true, dataIndex: 'ps_firstname' },
				{ text: 'Uzvārds', width: 100, sortable: true, dataIndex: 'ps_lastname' },
				{ text: 'Dzimšanas datums', width: 100, sortable: true, dataIndex: 'ps_birthdate', xtype:'datecolumn', format:'d.m.Y' },
				{
					text: 'Personas kods',
					width: 100,
					sortable: true,
					dataIndex: 'ps_personcode',
					renderer: function (val) {
						return val.match(/^(\d{6})(\d{5})$/) ? RegExp.$1+'-'+RegExp.$2:'';
					}
				},
				{ text: 'Klase', flex: 1, sortable: true, dataIndex: 'ps_cl_txt' }
			],
			iconCls: 'icon-grid'
		}];

		me.buttons = [{
			text: 'Pievienot',
			iconCls: 'icon-accept',
			itemId: 'accept',
			margin: 1,
			disabled: true,
			handler: ctrl.addChildren2Parent,
			scope: me
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