Ext.define('Journal.view.modules.LessonListView', {
	extend: 'Ext.window.Window',
	alias: 'widget.lessontlist',

	requires: [
        'Ext.form.*',
		'Ext.data.*',
		'Ext.grid.Panel',
		'Ext.layout.container.Column'
    ],

	width: 450,
	height: 450,
	resizable: false,
	maximizable: false,
	layout: {
		type: 'vbox',
		align: 'stretch'
	},
	fieldDefaults: {
		labelAlign: 'left',
		msgTarget: 'side'
	},
	border: false,

	initComponent: function () {
		var me = this,
			ctrl = me.controller;

		me.resizable = false;
		me.maximizable = false;

		me.rowEditing = Ext.create('Ext.grid.plugin.RowEditing', {
			clicksToEdit: 2,
			autoCancel: false,
			errorSummary: true,
			listeners: {
				canceledit: ctrl.deleteLessonHandler,
				edit: ctrl.completeEditHandler,
				scope: ctrl
			}
		});

		me.tbar = [{ 
			xtype: 'button', 
			iconCls: 'icon-add',
			text: 'Pievienot', 
			handler: ctrl.addClickHandler,
			scope: ctrl
		},{
			xtype: 'button', 
			text: 'Dzēst', 
			iconCls: 'icon-delete',
			handler: function() {
				this.app.removeAlert(ctrl.removeClickHandler, ctrl);
			},
			scope: ctrl
		}];

		me.items = [{
			xtype: 'gridpanel',
			id: 'courses-grid',
			frame: false,
			border: false,
			plugins: [this.rowEditing],
			store: 'LessonListStore',
			flex: 1,

			columns: [
				{ text: 'Nosaukums', flex: 1, sortable: true, dataIndex: 'cr_name', field: { xtype:'textfield', validateOnBlur: true, validateOnChange: true}},
				{ text: 'Līmenis', width: 85, sortable: true, dataIndex: 'cr_level', field: { xtype:'numberfield', minValue: 1, maxValue: 12, validateOnBlur: true, validateOnChange: true}},
				{ text: 'Stundu skaits', width: 100, sortable: true, dataIndex: 'cr_hours', field: { xtype:'numberfield', minValue: 1, validateOnBlur: true, validateOnChange: true}},
			],
			listeners: { beforedeselect: ctrl.deleteLessonHandler, scope: ctrl },
			dockedItems:[{
				xtype: 'pagingtoolbar',
				dock: 'bottom',
				store: 'LessonListStore',
				displayInfo: true
			}]
		}]

		me.callParent();
	},
	
	afterRender: function() {
		this.callParent();
		this.items.getAt(0).store.load();
	}
});