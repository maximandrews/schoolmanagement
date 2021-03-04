 Ext.define('Journal.view.modules.UserRegView', {
	extend: 'Ext.window.Window',
	alias: 'widget.userreg',

	requires: [
		'Ext.form.*',
		'Ext.data.*',
		'Ext.grid.Panel',
		'Ext.layout.container.Column',
		'Ext.button.Button',
		'Ext.grid.property.Grid',
		'Ext.window.MessageBox',
		'Ext.tip.*',
		'Ext.toolbar.Paging'
	],

	minHeight: 500,
	minWidth: 500,
	width: 550,
	height: 600,
	layout: {
		type: 'vbox',
		align: 'stretch'
	},
	fieldDefaults: {
		labelAlign: 'left',
		msgTarget: 'side'
	},
	border: false,
	frame: false,
	childWindow: null,

	initComponent: function () {
		var me = this,
				ctrl = me.controller;

		me.myTabs = {
			'teacher-course': {
				title: 'Priekšmeti',
				itemId: 'teacher-course',
				id: 'user-reg-teacher-course',
				border: false,
				frame: false,
				xtype: 'grid',
				store: 'UserRegAddCourseStore',	
				columns: [
					{ text: 'Mācību priekšmets', flex: 1,  sortable: true, dataIndex: 'cr_name' },
					{ text: 'Stundu skaits', width: 100, sortable: true, dataIndex: 'cr_hours' },
					{ text: 'Klase', width: 75, sortable: true, dataIndex: 'cl_name' },
					{ text: 'Tips', width: 100, sortable: true, dataIndex: 'cc_statuss' },
					{ text: 'Skolēnu skaits', width: 100, sortable: true, dataIndex: 'cl_count'}
				]
			},
			'pupil-rel':{
				xtype: 'grid',
				id: 'user-reg-person-pupil-rel',
				title: 'Vecāki',
				border: false,
				frame: false,
				itemId: 'pupil-rel',
				flex: 1,
				store: 'UserRegParentStore',		
				columns: [
					{ text: 'Vārds', width: 100, sortable: true, dataIndex: 'ps_firstname' },
					{ text: 'Uzvārds', width: 100, sortable: true, dataIndex: 'ps_lastname' },
					{ text: 'Dzimšanas datums', width: 100, sortable: true, dataIndex: 'ps_birthdate', xtype:'datecolumn', format:'d.m.Y' },
					{ text: 'E-pasts', flex: 1, sortable: true, dataIndex: 'ps_email'}
				],
				tabConfig: {
					handler: ctrl.onParentsTabClick,
					scope: ctrl
				}					
			},
			'rel-pupil': {
				title: 'Bērni',
				itemId: 'rel-pupil',
				id: 'user-reg-person-rel-pupil',
				border: false,
				frame: false,
				flex: 1,
				xtype: 'grid',
				tbar: [
					{ xtype: 'button', iconCls: 'icon-add', text: 'Pievienot', handler: ctrl.addChild, scope: ctrl },
					{
						xtype: 'button',
						text: 'Dzēst', 
						itemId: 'delete-puplil',
						disabled: true, 
						iconCls: 'icon-delete', 
						handler: function() {
							this.app.removeAlert(ctrl.removeChildHandler, ctrl);
						},
						scope: ctrl
					}],
				store: 'UserRegChildStore',
				columns: [
					{ text: 'Vārds', width: 100, sortable: true, dataIndex: 'ps_firstname' },
					{ text: 'Uzvārds', width: 100, sortable: true, dataIndex: 'ps_lastname' },
					{ text: 'Dzimšanas datums', width: 100, sortable: true, dataIndex: 'ps_birthdate', xtype:'datecolumn', format:'d.m.Y' },
					{ text: 'Klase', width: 40, sortable: true, dataIndex: 'class'},
					{ text: 'E-pasts', flex: 1, sortable: true, dataIndex: 'ps_email'}
				],
				listeners: {
					selectionchange: ctrl.parentsChildSelectionChange,
					scope: me
				},
				tabConfig: {
					handler: ctrl.onPupilsTabClick,
					scope: ctrl
				}
			}
		};
			
		me.tbar = [{
			xtype: 'button',
			iconCls: 'icon-add',
			text: 'Pievienot',
			handler: ctrl.addUserHandler,
			scope: ctrl
		},{
			xtype: 'button', 
			text: 'Dzēst',
			itemId: 'delete-person',
			disabled: true,
			iconCls: 'icon-delete',
			handler: function() {
				this.app.removeAlert(ctrl.removeClickHandler, ctrl);
			},
			scope: ctrl
		},{
			xtype: 'tbspacer', flex: 1
		},{
			xtype: 'combo',
			id:'ur-type-combo',
			fieldLabel: 'Lietotāja tips',
			labelWidth: 90,
			width: 190,
			labelAlign: 'right',
			editable: true,
			allowBlank: true,
			preventMark: true,
			store: 'UserRegTypeStore',
			listeners: { change: ctrl.changedTypeHandler, scope: ctrl },
			queryMode: 'local',
			displayField: 'ut_name',
			valueField: 'ut_id'
		}];

		me.items = [{
			xtype: 'gridpanel',
			frame: false,
			border: false,
			store: 'UserRegDataStore',
			flex: 1,
			columns: [
				{ text: 'Lietotājvārds', flex: 1, sortable : true, dataIndex: 'ps_email'},
				{ text: 'Vārds', width: 75, sortable: true, dataIndex: 'ps_firstname'},
				{ text: 'Uzvārds', width: 75, sortable: true, dataIndex: 'ps_lastname'},
				{ text: 'Dzimšanas datums', width: 100, sortable: true, dataIndex: 'ps_birthdate', xtype:'datecolumn', format:'d.m.Y'},
				{
					text: 'Personas kods',
					width: 100,
					sortable: true,
					dataIndex: 'ps_personcode',
					renderer: function (val) {
						return val.match(/^(\d{6})(\d{5})$/) ? RegExp.$1+'-'+RegExp.$2:'';
					}
				},
				{ text: 'Lietotāja tips', width: 100, sortable: true, dataIndex: 'ps_ut_text'},
				{ text: 'Klase', width: 50, sortable: true, dataIndex: 'ps_cl_txt', hidden: true}
			],
				viewConfig: {
					loadMask: true,
					loadingText: 'Notiek datu ielāde'
				},
			dockedItems:[{
				xtype: 'pagingtoolbar',
				id: 'paging-bar',
				dock: 'bottom',
				store: 'UserRegDataStore',
				displayInfo: true
			}],
			listeners: {
				selectionchange: ctrl.showInfoHandler,
				scope: ctrl
			}
		},{
			xtype: 'tabpanel',
			bodyStyle:  'padding: 5px; background:#dfe8f6;',
			hidden: true,
			header: false,
			activeTab: 0,
			defaults: { closable: false },
			frame: false,
			border: false,
			height: 320,
			items: [{
				xtype: 'form',
				id: 'user-reg-user-edit-form',
				// BINDING TO PHP START
				phpcontroller: 'usersitem',// see data/config/prefixes.php
				phpviewmethod: 'main', // see view methods
				// BINDING TO PHP END
				// AFTER PHP START
				onSuccess: ctrl.onFormSuccess,
				scope: ctrl,
				idProperty: 'ps_id',
				// AFTER PHP END
				bodyStyle:  'padding: 5px; background:#dfe8f6;',
				border: false,
				frame: false,
				title: 'Informācija',
				layout: 'anchor',
				flex: 1,
				defaults: {
					labelWidth: 120,
					anchor: '100%',
					labelAlign: 'right',
				},
				defaultType: 'textfield',
				items: [
					{ xtype: 'hiddenfield', name: 'ps_id' },
					{ fieldLabel: 'Lietotājvārds', name: 'ps_email', vtype: 'email' },
					{ fieldLabel:'Parole', name: 'ps_password', inputType: 'password' },
					{ fieldLabel:'Atkārtot paroli', name: 'repass_ps_password', inputType: 'password' },
					{
						xtype: 'combo',
						fieldLabel: 'Lietotāja tips',
						name: 'ps_ut_id',
						editable: false,
						allowBlank: false,
						store: 'UserRegTypeStore',
						listeners: { change: ctrl.userTypes, scope: ctrl },
						queryMode: 'local',
						displayField: 'ut_name',
						valueField: 'ut_id',
						afterRender: function() {
							this.store.load();
						}
					},
					{ fieldLabel: 'Vārds', name: 'ps_firstname' },
					{ fieldLabel: 'Uzvārds', name: 'ps_lastname' },
					{ xtype: 'datefield', fieldLabel: 'Dzimšanas datums', name: 'ps_birthdate' },
					{ xtype: 'textfield', fieldLabel: 'Personas kods', name: 'ps_personcode' },
					{
						xtype: 'combo',
						fieldLabel: 'Klase',
						name: 'ps_cl_id',
						hidden: true,
						editable: false,
						allowBlank: false,
						queryMode: 'local',
						store: 'ClassRegStore',
						tpl: new Ext.XTemplate(
							'<ul>',
								'<tpl for=".">' +
									'<li class="x-boundlist-item" role="option">' + 
										'{[values["cl_level"]+values["cl_postfix"]]}' +
									'</li>' +
								'</tpl>',
							'</ul>'
						),
						displayTpl: new Ext.XTemplate(
							'<tpl for=".">' +
								'{[values["cl_level"]+values["cl_postfix"]]}' +
								'<tpl if="xindex < xcount">, </tpl>' +
							'</tpl>'
						),
						displayField: 'cl_level',
						valueField: 'cl_id',
						afterRender: function() {
							this.store.load();
						}
					},
					{ fieldLabel: 'Paziņojumu e-pasts', name: 'ps_mailsms', hidden: true }
				],
				buttonAlign: 'center',
				buttons: [{
					text: 'Saglabāt',
					iconCls: 'icon-accept',
					formBind: true,
					disabled: true,
					handler: ctrl.saveHandler,
					scope: ctrl
				},{
					text: 'Aizvērt',
					handler: ctrl.hideInfoHandler,
					scope: ctrl
				}]
			}]
		}];
		me.callParent();
	},
	
	afterRender: function() {
		this.callParent();
		this.items.getAt(0).store.load();
	}
});