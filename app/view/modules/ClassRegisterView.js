Ext.define('Journal.view.modules.ClassRegisterView', {
	extend: 'Ext.window.Window',
	alias: 'widget.classregister',

	requires: [
		'Ext.util.Format',
		'Ext.grid.Panel',
		'Ext.form.Panel'
	],

	width: 740,
	height: 480,
	border: false,
	layout: {
		type: 'hbox',
		align: 'stretch'
	},
	exFields: [],

	initComponent: function () {
		var me = this,
				ctrl = me.controller;
		
		me.tbar = {
			xtype: 'tabbar',
			id: 'cr-tabbar',
			frame: false,
			border: false,
			defaults: { closable: false },
			items: [
				{ text: 'Matemātika', handler: ctrl.tabSelector, scope: ctrl },
				{ text: 'Fizika', handler: ctrl.tabSelector, scope: ctrl },
				{ text: 'Vizuāla maksla', handler: ctrl.tabSelector, scope: ctrl },
				{ xtype: 'tbspacer', flex: 1 },
				{
					xtype: 'combo',
					id:'cr-class-combo',
					fieldLabel: 'Klase',
					labelWidth: 50,
					width: 120,
					labelAlign: 'right',
					editable: false,
					allowBlank: false,
					store: 'ClassRegisterClassStore',
					listeners: {
						change: ctrl.tabSelector,
						scope: ctrl
					},
					queryMode: 'local',
					displayField: 'className',
					valueField: 'classId'
				}
			]
		};

		var crRenderer = function (val, cProps) {
			var ret = '';
			if(val[1] == 0) {
				cProps.style = 'text-align:center';
				ret = '<div style="color:#FFF;background-color:#'+(val[2] == 1?'CCC':'787878')+'">N</div>';
			}else{
				cProps.style = 'text-align:right';
				ret = val[2] == 0 ?'':val[2];
			}

			if(val[3].length > 0)
				cProps.tdAttr = 'data-qtip="' + val[3] + '"';

			return ret;
		};

		me.exFields = [{
			xtype: 'checkboxfield',
			fieldLabel: 'Attaisnota prombūtne',
			labelWidth: 125,
			name: 'mark',
			inputValue: 1
		},{
			xtype:'numberfield',
			fieldLabel: 'Atzīme',
			labelWidth: 45,
			minValue: 1,
			maxValue: 10,
			allowBlank: true,
			width: 100,
			name: 'mark'
		}];

		me.items = [{
			xtype: 'grid',
			id: 'cr-data-grid',
			flex: 1,
			columnLines: true,
			selModel: { selType: 'cellmodel' },
			store: 'ClassRegisterStore',
			enableColumnResize: true,
			enableColumnMove: false,
			enableLocking: true,
			columns: [
				{ text: 'Vārds', width: 100, dataIndex: 'ps_firstname', menuDisabled:true, resizeable:true, locked: true },
				{ text: 'Uzvārds', width: 100, dataIndex: 'ps_lastname', menuDisabled:true, resizeable:true, locked: true },
				{ text: '01.03', width: 40, dataIndex: '2012_03_01', align:'', menuDisabled:true, resizable:false, renderer: crRenderer },
				{ text: '02.03', width: 40, dataIndex: '2012_03_02', align:'', menuDisabled:true, resizable:false, renderer: crRenderer },
				{ text: '03.03', width: 40, dataIndex: '2012_03_03', align:'', menuDisabled:true, resizable:false, renderer: crRenderer },
				{ text: '04.03', width: 40, dataIndex: '2012_03_04', align:'', menuDisabled:true, resizable:false, renderer: crRenderer },
				{ text: '05.03', width: 40, dataIndex: '2012_03_05', align:'', menuDisabled:true, resizable:false, renderer: crRenderer },
				{ text: '06.03', width: 40, dataIndex: '2012_03_06', align:'', menuDisabled:true, resizable:false, renderer: crRenderer },
				{ text: '07.03', width: 40, dataIndex: '2012_03_07', align:'', menuDisabled:true, resizable:false, renderer: crRenderer },
				{ text: '08.03', width: 40, dataIndex: '2012_03_08', align:'', menuDisabled:true, resizable:false, renderer: crRenderer },
				{ text: '09.03', width: 40, dataIndex: '2012_03_09', align:'', menuDisabled:true, resizable:false, renderer: crRenderer },
				{ text: '10.03', width: 40, dataIndex: '2012_03_10', align:'', menuDisabled:true, resizable:false, renderer: crRenderer },
				{ text: '11.03', width: 40, dataIndex: '2012_03_11', align:'', menuDisabled:true, resizable:false, renderer: crRenderer },
				{ text: '12.03', width: 40, dataIndex: '2012_03_12', align:'', menuDisabled:true, resizable:false, renderer: crRenderer },
				{ text: '13.03', width: 40, dataIndex: '2012_03_13', align:'', menuDisabled:true, resizable:false, renderer: crRenderer },
				{ text: '14.03', width: 40, dataIndex: '2012_03_14', align:'', menuDisabled:true, resizable:false, renderer: crRenderer },
				{ text: '15.03', width: 40, dataIndex: '2012_03_15', align:'', menuDisabled:true, resizable:false, renderer: crRenderer }
			]
		},{
			xtype:'form',
			hidden: true,
			//collapsible: true,
			//collapsed: true,
			//hideCollapseTool: true,
			//collapseDirection: 'right',
			//headerPosition: 'left',
			header: false,
			id: 'day-mark-edit',
		  width: 170,
		  frame: true,
		  border: false,
		  padding: '0 0 5 0',
		  margin: 0,
		  defaults:{
				margin:5,
				msgTarget:'side',
				labelAlign:'left'
			},
			items:[{
				xtype: 'hiddenfield',
				name: 'id'
			},{
				xtype: 'displayfield',
				fieldLabel: 'Skolēns',
				labelStyle: 'margin-bottom:-5px',
				anchor:'100%',
				labelAlign: 'top',
				name: 'pupil',
				value: ''
			},{
				xtype: 'displayfield',
				fieldLabel: 'Datums',
				labelWidth: 45,
				anchor:'100%',
				name: 'date',
				value: ''
			},{
				xtype: 'displayfield',
				fieldLabel: 'Tēmats',
				labelAlign: 'top',
				labelStyle: 'margin-bottom:-5px',
				anchor:'100%',
				name: 'theme',
				value: ''
			},{
				xtype: 'checkboxfield',
				fieldLabel: 'Apmeklēja',
				labelWidth: 60,
				name: 'attendance',
				inputValue: 1,
				handler: ctrl.onCellFormCheckBoxClick,
				scope: ctrl
			},{
				xtype: 'textareafield',
				fieldLabel: 'Piezīme',
				labelAlign: 'top',
				margin: '5 5 0 5',
				grow: false,
				anchor:'100%',
				name: 'comment'
			}],
			buttons: [
				{ text:'Saglabāt', handler: ctrl.onCellSaveEdit, scope: ctrl },
				{ text:'Aizvērt', handler: ctrl.onCellCancelEdit, scope: ctrl }
			],
			buttonAlign: 'center'
		}];

		me.callParent();
	},

	afterRender: function () {
		var me = this;
		me.callParent();
		me.controller.tabSelector();
	}
});