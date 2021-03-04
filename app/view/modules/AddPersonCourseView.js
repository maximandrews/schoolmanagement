Ext.define('Journal.view.modules.AddPersonCourseView', {
	extend: 'Ext.window.Window',
	alias: 'widget.addpersoncourse',

	requires: [
		'Ext.window.*',
		'Ext.form.*'
    ],

	title: 'Pievienot priekšmetu grupai',
	modal: true,
	border: false,
	frame: false,
	width: 600,
	height: 330,
	buttonAlign: 'center',
	app: null,
	controller: null,

	initComponent: function () {
		var me = this,
				ctrl = me.controller;
		me.items = [{
			xtype: 'grid',
			store: 'ClassRegDataStore',
			border: false,
			frame: false,
			selModel: Ext.create('Ext.selection.CheckboxModel'),
			columns: [
				{ text: 'Vārds', width: 100, sortable: true, dataIndex: 'ps_firstname' },
				{ text: 'Uzvārds', width: 100, sortable: true, dataIndex: 'ps_lastname' },
				{ text: 'Dzimšanas datums', width: 100, sortable: true, dataIndex: 'ps_birthdate' },
				{ text: 'Vecāks', flex: 1, sortable: true, dataIndex: 'parent' }
			],
			width: 600,
			height: 330,
			iconCls: 'icon-grid',
			tbar: [{ 
					xtype: 'tbspacer', flex: 1 
				},{
					xtype: 'combo',
					id:'pr-prefix-combo',
					width: 200,
					labelWidth: 120,
					labelAlign: 'right',
					fieldLabel: 'Izvēlēties priekšmetu',
					editable: false,
					store: Ext.create('Ext.data.ArrayStore', {
						autoDestroy: true,
						id:'cr-class-combo',
						storeId: 'pr-prefix-store',
						idIndex: 0,
						fields: ['prefixId', 'prefixName'],
						data : [
							['a', 'Matemātika'],
							['b', 'Vēsture'],
							['c', 'Bioloģija'],
							['d', 'Fizika'],
							['e', 'Bioloģija'],
							['f', 'Mūzika'],
							['g', 'Vēsture']
						]
					}),

					queryMode: 'local',
					displayField: 'prefixName',
					valueField: 'prefixId'
				},
				{
					xtype: 'button',
					frame: true,
					text: 'Pievienot',
					itemId: 'accept',
					disabled: true
				},{ xtype: 'tbspacer', width: 50 }]
		}];
		me.callParent();
	}
});