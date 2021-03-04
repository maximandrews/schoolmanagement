Ext.define('Journal.view.modules.AddCourseView', {
	extend: 'Ext.window.Window',
	alias: 'widget.addcourse',

	requires: [
		'Ext.window.*',
		'Ext.form.*',
		'Ext.data.*'
    ],

	title: 'Pievienot priekšmetu',
	modal: true,
	border: false,
	frame: false,
	width: 300,
	height: 220,
	buttonAlign: 'center',
	padding: '0 0 2 0',
	app: null,
	controller: null,

	initComponent: function () {
		var me = this,
				ctrl = me.controller;

		me.items = [{
			xtype: 'form',
			id: 'add-course-item',
			// BINDING TO PHP START
			phpcontroller: 'classescoursesitem', // see data/config/prefixes.php
			phpviewmethod: 'main', // see view methods
			// BINDING TO PHP END
			// AFTER PHP START
			onSuccess: ctrl.onFormFilledIn,
			scope: me,
			idProperty: 'cc_id',
			// AFTER PHP END
			bodyStyle: 'padding: 5px; background:#dfe8f6;',
			flex: 1,
			frame: false,
			border: false,
			defaults: {
				labelWidth: 120,
				anchor: '100%',
				labelAlign: 'right',
			},
			items: [{
				xtype: 'hiddenfield',
				name: 'cc_id' 
			},{ 
				xtype: 'hiddenfield',
				name: 'cc_cl_id' 
			},{
				xtype: 'displayfield',
				fieldLabel: 'Klase',
				value: '',
				bodyStyle: 'background:#dfe8f6',
				name: 'cc_cl_txt'
			},{
				xtype: 'combobox',
				name: 'cc_cr_id',
				id: 'add-course-to-class-combo',
				fieldLabel: 'Priekšmets',
				editable: false,
				allowBlank: false,
				store: 'AddCourseCourseStore',
				queryMode: 'local',
				forceSelection: true,
				displayField: 'cr_name',
				valueField: 'cr_id',
				listeners: { change: ctrl.loadPupilStore, scope: me },
				afterRender: function () {
					this.getStore().load();
				}
			},{
				xtype: 'combobox',
				name: 'cc_teacher',
				fieldLabel: 'Skolotājs',
				editable: false,
				allowBlank: false,
				store: 'ClassRegTeacherStore',
				queryMode: 'local',
				forceSelection: true,
				tpl: new Ext.XTemplate(
					'<ul>',
						'<tpl for=".">' +
							'<li class="x-boundlist-item" role="option">' + 
								'{[values["ps_firstname"]+" "+values["ps_lastname"]]}' +
							'</li>' +
						'</tpl>',
					'</ul>'
				),
				displayTpl: new Ext.XTemplate(
					'<tpl for=".">' +
						'{[values["ps_firstname"]+\' \'+values["ps_lastname"]]}' +
						'<tpl if="xindex < xcount">, </tpl>' +
					'</tpl>'
				),
				displayField: 'ps_firstname',
				valueField: 'ps_id', // vērtības laukums
				afterRender: function () {
					this.getStore().load();
				}
			},{
				xtype: 'combobox',
				name: 'cc_statuss',
				store: [
					['1', 'Klasē'],
					['2', 'Grupā']
				],
				listeners: { change: ctrl.statussChange, scope: me },
				fieldLabel:'Priekšmetu apgūst',
				queryMode: 'local'
			},{
				xtype: 'numberfield',
				name: 'cc_hoursweek',
				fieldLabel: 'St. skaits nedeļā',
				minValue: 1
			},{
				xtype: 'tbspacer', height: 5
			},{
				xtype: 'grid',
				id: 'class-reg-data-grid',
				hidden: true,
				store: 'ChildInGroupStore',
				border: false,
				frame: false,
				selModel: Ext.create('Ext.selection.CheckboxModel'),
				columns: [
					{ text: 'Vārds', width: 100, sortable: true, dataIndex: 'pc_ps_firstname' },
					{ text: 'Uzvārds', flex: 1, sortable: true, dataIndex: 'pc_ps_lastname' }
				],
				//listeners: { beforeshow: ctrl.gridShowSelected, scope: ctrl},
				width: 300,
				height: 150,
				iconCls: 'icon-grid'
			}],
			buttons: [{ 
				text: 'Saglabāt',
				iconCls: 'icon-accept',
				margin: 1,
				handler: ctrl.savePersonCoursesHandler,
				scope: me
			},{ 
				text: 'Aizvērt',
				margin: 1,
				handler: me.close,
				scope: me
			}],
			buttonAlign: 'center'
		}];

		me.callParent();
	}
});