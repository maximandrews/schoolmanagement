Ext.define('Journal.view.modules.AddScheduleView', {
	extend: 'Ext.window.Window',
	alias: 'widget.addschedule',

	requires: [
		'Ext.form.Panel'
    ],

	title: 'Stundu saraksts',
	border: false,
	frame: false,
	width: 350,
	height: 210,
	layout: 'fit',
	app: null,
	controller: null,

	initComponent: function () {
		var me = this,
			ctrl = me.controller;
	
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
				bodyStyle: 'background:#dfe8f6;',
				frame: false,
				border: false,
				labelWidth: 70,
				labelAlign: 'right',
				layout: 'column'
			},
			items: [{
				xtype: 'hiddenfield',  // Mainīts: 2012.05.05.
				name: 'sc_id'
			},{
				xtype: 'hiddenfield', // Mainīts: 2012.05.05.
				name: 'sc_prent'
			},{
				xtype: 'combo',
				margin:'0 0 4 0',
				fieldLabel: 'Nodarbība',
				allowBlank: false,
				editable: false,
				width: 161,
				name: 'sc_cr_id',
				store: Ext.create('Ext.data.ArrayStore', {
					autoDestroy: true,
					idIndex: 0,
					fields: ['usertypeId', 'typeName'],
					data : ctrl.coursesNames
				}),
				queryMode: 'local',
				displayField: 'typeName',
				valueField: 'usertypeId'	
			},{
				xtype: 'fieldcontainer',
				combineErrors: true,
				fieldLabel: 'Datums',
				layout: 'hbox',
				items:[{
					width: 110,
					xtype: 'datefield',
					name: 'sc_date',
					requireFlag: 'sc_selected',
					allowBlank: false,
					editable: false,
					margin: '0 4 0 0'
				},{
					xtype: 'combo',
					fieldLabel: 'Laiks',
					labelWidth: 29,
					width: 141,
					margin: 0,
					name: 'sc_from',
					allowBlank: false,
					editable: false,
					store: Ext.create('Ext.data.ArrayStore', {
						autoDestroy: true,
						idIndex: 0,
						fields: ['timeId', 'timeStr'],
						data : ctrl.getLessonTime()
					}),
					queryMode: 'local',
					displayField: 'timeStr',
					valueField: 'timeId'
				}]
					
			},{
				xtype: 'textfield',
				fieldLabel: 'Temats',
				name: 'sc_lesson_theme'
			
			},{
			// Atkārtošanas perioda lauki
				xtype: 'fieldcontainer',
				combineErrors: true,
				items:[{
					xtype: 'checkbox',
					boxLabel: 'Atkārtot',
					name: 'sc_selected',
					id: 'sc_selected',
					margin: '0 2 0 75',
					handler: ctrl.onCheckHide, // izsauc funkciju, kas paslēpj laukus, ja nodarbība neatkārtojās
					scope: ctrl
				},{
					id: 'fields-to-hide1',
					xtype: 'numberfield',
					name: 'freq_count',
					hidden: true,
					disabled: true,
					width: 95,
					minValue: 1,
					value: 1,
					margin: '0 2 0 1',
					fieldLabel: 'katru',
					labelWidth: 30
				},{
					id: 'fields-to-hide2',
					xtype: 'combo',
					name: 'freq_period',
					width: 90,
					hidden: true,
					disabled: true,
					editable: false,
					margin: '0 0 0 2',
					store: Ext.create('Ext.data.ArrayStore', {
						autoDestroy: true,
						storeId: 'pr-store2',
						idIndex: 0,
						fields: ['usertypeId', 'typeName'],
						data : [
							[1, 'Dienu'],
							[2, 'Nedeļu']
						]
					}),
					queryMode: 'local',
					displayField: 'typeName',
					valueField: 'usertypeId'
				}]	
			},{
				id: 'fields-to-hide3',
				xtype: 'datefield',
				fieldLabel: 'Atkārtot līdz',
				editable: false,
				name: 'sc_till',
				requireFlag: 'sc_selected',
				allowBlank: false,
				hidden: true,
				disabled: true,
				anchor: 50
			}],
			buttons: [{ 
				text: 'Saglabāt',
				formBind: true,
				iconCls: 'icon-accept',
				itemId: 'accept',
				handler:  ctrl.onSheduleSaveEdit, 
				scope: ctrl,
				margin: 1
			},{ 
				text: 'Dzēst', 
				iconCls: 'icon-delete',
				handler: ctrl.onSheduleDelete,
				scope: ctrl,
				margin: 1
			},{ 
				text: 'Aizvērt',
				itemId: 'reject',
				margin: 1,
				handler: me.close,
				scope: me
			}],
			buttonAlign: 'center'
		};

		me.listeners = {
			beforeclose: ctrl.beforeSchedileEditClose,
			scope: ctrl
		};
		me.callParent();
	}
});