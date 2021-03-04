Ext.define('Journal.view.modules.PersonDataView', {
	extend: 'Ext.window.Window',
	alias: 'widget.persondata',

	requires: [
		'Ext.window.*',
		'Ext.form.*'
    ],

	width: 400,
	height: 460  ,
	border: false,
	layout: {
		type: 'vbox',
		align: 'stretch'
	},
	resizable: false,
	maximizable: false,

	initComponent: function () {
		var me = this,
				ctrl = me.controller;

		me.resizable = false;
		me.maximizable = false;
		me.myTabs = {
			'child-to-submit': {
				title: 'Pieteiktie bērni',
				border: false,
				frame: false,
				flex: 1,
				xtype: 'grid',
				store: 'PersonReqChildStore',	
				columns: [
					{ text: 'Vārds', width: 100, sortable: true, dataIndex: 'ps_firstname' },
					{ text: 'Uzvārds', width: 100, sortable: true, dataIndex: 'ps_lastname' },
					{ text: 'Dzimšanas datums', width: 100, sortable: true, dataIndex: 'ps_birthdate' },
					{ text: 'Klase', flex: 1, sortable: true, dataIndex: 'class' }
				]
			}
		};

		me.items = [{	
			xtype: 'form',
			// BINDING TO PHP START
			phpcontroller: 'usersitem',// see data/config/prefixes.php
			phpviewmethod: 'main', // see view methods
			// BINDING TO PHP END
			// AFTER PHP START
			onSuccess: me.close,
			scope: me,
			idProperty: 'ps_id',
			// AFTER PHP END
			id: 'profil-data-form',
			height: 430,
			border: false,
			frame: true,
			bodyPadding: '0 5',
			layout: {
				type: 'vbox',
				align: 'stretch'
			},
			defaults: {
				labelWidth: 130,
				anchor: '100%',
				labelAlign: 'right'
			},
			items: [{ xtype: 'hiddenfield', name: 'ps_id' },{
					xtype: 'displayfield',
					name: 'ps_email',
					fieldLabel:'E-pasts/Lietotājvārds'
				},{ 
					xtype: 'textfield',
					name: 'password',
					fieldLabel:'Esoša parole',
					inputType: 'password'
				},{ 
					xtype: 'textfield',
					name: 'newpassword',
					fieldLabel:'Jauna parole',
					inputType: 'password'
				},{
					xtype: 'textfield',
					name: 'repeatpassword',
					fieldLabel:'Parole atkārtoti',
					inputType: 'password'
				},{ 
					xtype: 'displayfield',
					name: 'ps_firstname',
					fieldLabel:'Vārds'
				},{ 
					xtype: 'displayfield',
					name: 'ps_lastname',
					fieldLabel:'Uzvārds'
				},{ 
					xtype: 'displayfield',
					name: 'ps_personcode',
					fieldLabel:'Personas kods'
				},/*{
					xtype: 'textfield',
					fieldLabel: 'Telefons',
					name: 'phone_number'
				},*/{ 
					xtype: 'displayfield',
					name: 'ut_name',
					fieldLabel:'Lietotāja tips'
				},{ 
					xtype: 'textfield',
					name: 'ps_mailsms',
					hidden: true,
					fieldLabel:'Paziņojumu e-pasts'
				},{
					xtype: 'tabpanel',
					id: 'tabpanel',
					flex: 1,
					activeTab: 0,
					defaults: { closable: false },
					frame: false,
					border: false,
					items: [{
						title: 'Bērni',
						border: false,
						frame: false,
						flex: 1,
						xtype: 'grid',
						hidden: true,
						id: 'person-data-approved-child',
						tbar: [{ 
								xtype: 'button', iconCls: 'icon-add', text: 'Pieteikt', handler: ctrl.addChild, scope: ctrl 
							},{
								xtype: 'button', 
								text: 'Dzēst', 
								iconCls: 'icon-delete',
								handler: function() {
									this.app.removeAlert(ctrl.removeClickHandler, ctrl);
								},
								scope: ctrl
							}],
						store: 'PersonAppChildStore',		
							columns: [
							{ text: 'Vārds', width: 100, sortable: true, dataIndex: 'ps_firstname' },
							{ text: 'Uzvārds', width: 100, sortable: true, dataIndex: 'ps_lastname' },
							{ text: 'Dzimšanas datums', width: 100, sortable: true, dataIndex: 'ps_birthdate' },
							{ text: 'Klase', flex: 1, sortable: true, dataIndex: 'class' }
							]
					}],
					buttons: [
						{ text: 'Saglabāt', iconCls: 'icon-accept', margin: '0 1 3 1', handler: ctrl.onFormSuccess, scope: ctrl},
						{ text: 'Aizvērt' , margin: '0 1 3 1', handler: me.close, scope: me },
					
					],
					buttonAlign: 'center'
				}]
		}];

		me.callParent();
	}/*,

	afterRender: function() {
		this.callParent();
		
	}*/
});