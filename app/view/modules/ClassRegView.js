Ext.define('Journal.view.modules.ClassRegView', {
	extend: 'Ext.window.Window',
	alias: 'widget.classreg',

	requires: [
		'Ext.form.*',
		'Ext.data.*'
	],

	minHeight: 500,
	minWidth: 350,
	width: 450,
	height: 590,
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

	initComponent: function () {
		var me = this,
				ctrl = me.controller;

		me.myTabs = {
			'class-courses': {
				title: 'Priekšmeti',
				border: false,
				frame: false,
				flex: 1,
				xtype: 'grid',
				id: 'class-reg-courses',
				tbar: [
					{ xtype: 'button', iconCls: 'icon-add', text: 'Pievienot klasei', handler: ctrl.addCourse, scope: ctrl },
					],
				store:'ClassRegAddCourseStore',		
				columns: [
					{ text: 'Mācību priekšmets', width: 100, sortable: true, dataIndex: 'cc_cr_txt' },
					{ text: 'Skolotājs', flex: 1, sortable: true, dataIndex: 'cc_teacher_txt' },
					{ text: 'Stundu skaits', width: 100, sortable: true, dataIndex: 'cc_hoursweek' },
					{ text: 'Tips', width: 75, sortable: true, dataIndex: 'cc_statuss', renderer: me.changeTypeFormat},
					{
						xtype:'actioncolumn',
						width:50,
						draggable: false,
						menuDisabled: true,
						items: [{
							iconCls: 'icon-edit-butt',
							tooltip: 'Rediģēt',
							handler: ctrl.editCourseHandler,
							scope: ctrl
						},{
							iconCls: 'icon-delete-butt',
							tooltip: 'Dzēst',
							handler: ctrl.deleteCourseHandler,
							scope: ctrl
						}]
					}
				],
				//listeners: { selectionchange: ctrl.courseDataHandlerfunction, scope: ctrl },
				tabConfig: {
					handler: ctrl.onCourseTabClick,
					scope: ctrl
				}
			},
			'class-parents': {
				title: 'Vecāki',
				border: false,
				frame: false,
				flex: 1,
				xtype: 'grid',
				id: 'class-parents-grid',
				store: 'ClassRegParentStore',		
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
			'class-pupils':{
				title: 'Skolēni',
				border: false,
				frame: false,
				flex: 1,
				id:'class-reg-pupils',
				xtype: 'grid',
				tbar: [
					{ 
						xtype: 'button', iconCls: 'icon-add', text: 'Pievienot', handler: ctrl.addChild, scope: ctrl 
					}
				],
				store: 'ClassRegChildStore',		
				columns: [
					{ text: 'Vārds', width: 100, sortable: true, dataIndex: 'ps_firstname' },
					{ text: 'Uzvārds', width: 100, sortable: true, dataIndex: 'ps_lastname' },
					{ text: 'Dzimšanas datums', width: 100, sortable: true, dataIndex: 'ps_birthdate', xtype:'datecolumn', format:'d.m.Y' },
					{ text: 'E-pasts', flex: 1, sortable: true, dataIndex: 'ps_email'}
				],
				tabConfig: {
					handler: ctrl.onPupilTabClick,
					scope: ctrl
				}
			}					
		};

		me.tbar = [
			{ 
				xtype: 'button', iconCls: 'icon-add', text: 'Pievienot', handler: ctrl.addClass, scope: ctrl 
			},{
				xtype: 'button', 
				text: 'Dzēst', 
				iconCls: 'icon-delete',
				handler: function() {
					this.app.removeAlert(ctrl.removeClickHandler, ctrl);
				},
				scope: ctrl
			},{ xtype: 'tbspacer', flex: 1 },{
				xtype: 'combo',
				id:'cr-level-combo',
				fieldLabel: 'Meklēt līmeni',
				labelWidth: 100,
				width: 190,
				labelAlign: 'right',
				editable: true,
				allowBlank: true,
				preventMark: true,
				store: [1,2,3,4,5,6,7,8,9,10,11,12],
				queryMode: 'local',
				listeners: { change: ctrl.filterLevelHandler, scope: ctrl}
		}];

		me.items = [{
			xtype: 'gridpanel',
			frame: false,
			border: false,
			store: 'ClassRegStore',
			flex: 1,

			columns: [
				{ text: 'Klase', width: 50, sortable : true, dataIndex: 'cl_txt'},
				{ text: 'Izlaiduma gads', width: 100, sortable: true, dataIndex: 'cl_year'},
				{ text: 'Klases audzinātājs', flex: 1, sortable: true, dataIndex: 'cl_teacher_txt'},
				{ text: 'Skolēnu skaits', width: 85, sortable: true, dataIndex: 'cl_ps_count'}
			],
			listeners: { selectionchange: ctrl.classDataHandler, scope: ctrl },

			dockedItems:[{
				xtype: 'pagingtoolbar',
				dock: 'bottom',
				store: 'ClassRegStore',
				displayInfo: true
			}]
		},{
			xtype: 'tabpanel',
			bodyStyle:  'padding: 5px; background:#dfe8f6;',
			hidden: true,
			activeTab: 0,
			defaults: { closable: false },
			frame: false,
			border: false,
			height: 230,
			items: [{
				xtype: 'form',
				id: 'class-reg-user-add-form',
				// BINDING TO PHP START
				phpcontroller: 'classesitem',// see data/config/prefixes.php
				phpviewmethod: 'main', // see view methods
				// BINDING TO PHP END
				// AFTER PHP START
				onSuccess: ctrl.onFormSuccess,
				scope: ctrl,
				idProperty: 'cl_id',
				// AFTER PHP END
				bodyStyle: 'padding: 5px; background:#dfe8f6;',
				border: false,
				frame: false,
				title: 'Informācija',
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
				//cl_id, cl_level, cl_postfix, cl_year, cl_teacher, cl_created, cl_modified
				items: [{
						xtype: 'hiddenfield',
						name: 'cl_id'
					},{
						xtype: 'numberfield',
						mixValue: 1,
						maxValue: 12,
						fieldLabel: 'Klase līmenis',
						name: 'cl_level'
					},{
						fieldLabel: 'Klases burts',
						name: 'cl_postfix'
					},{
					// Klases audzinātaju izvēles combo box
						xtype: 'combo',
						name: 'cl_teacher',
						id:'user-type-combo1',
						fieldLabel: 'Klases audzinātājs',
						labelAlign: 'right',
						editable: false,
						allowBlank: false,
						store: 'ClassRegTeacherStore',
						queryMode: 'local',
						forceSelection: true,
						tpl: new Ext.XTemplate(
							'<ul>',
								'<tpl for=".">' +
									'<li class="x-boundlist-item" role="option">' + 
										'{[values["ps_firstname"]+\' \'+values["ps_lastname"]+\' (\'+values["ps_email"]+\')\']}' +
									'</li>' +
								'</tpl>',
							'</ul>'
						),
						displayTpl: new Ext.XTemplate(
							'<tpl for=".">' +
								'{[values["ps_firstname"]+\' \'+values["ps_lastname"]+\' (\'+values["ps_email"]+\')\']}' +
								'<tpl if="xindex < xcount">, </tpl>' +
							'</tpl>'
						),
						displayField: 'ps_firstname',
						valueField: 'ps_id', // vērtības laukums
						afterRender: function () {
							this.getStore().load();
						}
					},{
						xtype: 'displayfield',
						fieldLabel: 'Izlaiduma gads',
						name: 'cl_year'
					},{
						xtype: 'displayfield',
						fieldLabel: 'Skolēnu skaits',
						name: 'cl_ps_count'
					}],
				buttonAlign: 'center',
				buttons: [{ 
					text: 'Saglabāt',
					iconCls: 'icon-accept',
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

	changeTypeFormat: function (val) {
		if (val == 1) 
			return 'Klase';
		 else 
			return 'Grupa';
	},
	
	afterRender: function() {
		this.callParent();
		this.items.getAt(0).store.load();
	}
});