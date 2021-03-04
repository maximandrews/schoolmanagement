Ext.define('Journal.view.modules.PretendentListView', {
	extend: 'Ext.window.Window',
	alias: 'widget.pretendentlist',
	
	requires:[
		'Ext.window.*',
		'Ext.form.*',
		'Ext.selection.CheckboxModel'
	],

	width: 600,
	height: 330,
	border: false,
	layout: 'fit',

	initComponent: function () {
		var me = this,
				ctrl = me.controller;

		me.items = [{
			xtype: 'grid',
			id: 'pt-grid',
			store: 'PretendentListStore',
			border: false,
			frame: false,
			selModel: Ext.create('Ext.selection.CheckboxModel'),
// pt_id, pt_firstnamechild, pt_lastnamechild, pt_personcodechild,  pt_genderchild, pt_cl_level, pt_comment,
// pt_firstnameparent, pt_lastnameparent, pt_personcodeparent, pt_password, pt_phonenumber, pt_mailsms,
// pt_created, pt_modified
			columns: [
				{ text: 'Bērna lietotājvārds',  width: 100, sortable: true, dataIndex: 'pt_emailchild' },
				{ text: 'Bērna vārds', width: 80, sortable: true, dataIndex: 'pt_firstnamechild' },
				{ text: 'Bērna uzvārds', width: 90, sortable: true, dataIndex: 'pt_lastnamechild' },
				{ text: 'Dzimums', width: 50, sortable: true, dataIndex: 'pt_genderchild' },
				{ text: 'Dzimšanas datums', width: 100, sortable: true, dataIndex: 'pt_childbirthday',  xtype:'datecolumn', format:'d.m.Y' },
				{ text: 'Personas kods', width: 90, sortable: true, dataIndex: 'pt_personcodechild' },
				{ text: 'Līmenis', width: 50, sortable: true, dataIndex: 'pt_cl_level' },
				{ text: 'Komentārs', width: 100, sortable: false, dataIndex: 'pt_comment' },
				{ text: 'Vecāka lietotajvārds',  width: 120, sortable: true, dataIndex: 'pt_emailparent' },
				{ text: 'Vecāka vārds',  width: 80, sortable: true, dataIndex: 'pt_firstnameparent' },
				{ text: 'Vecāka uzvārds', width: 100, sortable: true, dataIndex: 'pt_lastnameparent' },
				{ text: 'V. personas kods', width: 100, sortable: true, dataIndex: 'pt_personcodeparent' },
				{ text: 'Vecāka tālrunis', width: 100, sortable: true, dataIndex: 'pt_phonenumber' },
				{ text: 'Vecāka paziņojumu e-pasts', width: 100, sortable: true, dataIndex: 'pt_mailsms' },
				{ text: 'Pieteikšanas datums', width: 120, sortable: true, dataIndex: 'pt_created' }
			],
			dockedItems:[{
				xtype: 'pagingtoolbar',
				id: 'paging-pretendents-bar',
				dock: 'bottom',
				store: 'PretendentListStore',
				displayInfo: true
			}],
			tbar: [ 
				{ xtype: 'tbspacer', flex: 1 },{
					xtype: 'button', 
					text: 'Dzēst', 
					iconCls: 'icon-delete',
					handler: function() {
						this.app.removeAlert(ctrl.removeChildHandler, ctrl);
					},
					scope: ctrl
				},{
					xtype: 'combo',
					id:'pr-level-combo',
					fieldLabel: 'Meklēt līmeni',
					labelWidth: 95,
					width: 155,
					labelAlign: 'right',
					editable: false,
					store: 'PretendentListLevelStore',
					listeners: {
						change: ctrl.changeLevelHandler,
						scope: ctrl
					},
					queryMode: 'local',
					displayField: 'className',
					valueField: 'classId'
				},{ 
					xtype: 'tbspacer', flex: 1 
				},{
					xtype: 'combo',
					id:'pr-prefix-combo',
					fieldLabel: 'Izvēlēties klasi',
					labelWidth: 95,
					width: 155,
					labelAlign: 'right',
					editable: false,
					store: 'PretendentListLevelStore',
					queryMode: 'local',
					displayField: 'className',
					valueField: 'classId'
				},{
					xtype: 'combo',
					id:'pr-prefix-combo2',
					width: 60,
					labelAlign: 'right',
					editable: false,
					store: 'PretendentListPrefixStore',

					queryMode: 'local',
					displayField: 'prefixName',
					valueField: 'prefixId'
				},{
					xtype: 'button',
					frame: true,
					text: 'Uzņemt',
					itemId: 'accept',
					disabled: true
				},{ 
					xtype: 'tbspacer', flex: 1 
				}
			]
		}];

		me.callParent();
	},
	/*
	afterRender: function() {
		this.callParent();
		this.items.getAt(0).store.load();
	}*/
});