Ext.define('Journal.view.modules.RecordBookView', {
	extend: 'Ext.window.Window',
	alias: 'widget.recordbook',

	requires: [
		'Ext.data.ArrayStore',
		'Ext.util.Format',
		'Ext.grid.Panel',
		'Ext.grid.RowNumberer',
		'Ext.picker.Date',
		'Ext.data.*',
		'Ext.grid.*',
		'Ext.tab.*'
	],
	width: 740,
	height: 480,
	border: false,
	layout: 'fit',
	dateMenu: null,

	initComponent: function () {
		var me = this,
			ctrl = me.controller;

		me.dateMenu = Ext.create('Ext.menu.DatePicker', {
			disabledDays: [0,6],
			bodyCls: 'record-book-picker',
			format: 'Y.m.d',
			startDay: 1,
			nextText: 'Nākamais mēnesis',
			prevText: 'Iepriekšējais mēnesis',
			handler: ctrl.datePickerHandler,
			scope: ctrl
		});

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

		var tb = [
						{ xtype: 'button', text: 'Nedeļa', allowDepress: false, enableToggle: true, toggleGroup: 'periodType', handler: ctrl.weekButtonHandler, scope: ctrl, pressed: true, id: 'rb-week-button' },
						{ xtype: 'button', text: 'Diena', allowDepress: false, enableToggle: true, toggleGroup: 'periodType', handler: ctrl.dayButtonHandler, scope: ctrl },
						{ xtype: 'tbspacer', width: 100 },
						{ xtype: 'tbspacer', flex: 1 },
						{ xtype: 'button', iconCls: Ext.baseCSSPrefix + 'tbar-page-prev', handler: ctrl.prevButtonHandler, scope: ctrl },
						{ xtype: 'button', text: '', menu: me.dateMenu, id: 'rb-week-interval' },
						{ xtype: 'button', iconCls: Ext.baseCSSPrefix + 'tbar-page-next', handler: ctrl.nextButtonHandler, scope: ctrl },
						{ xtype: 'tbspacer', flex: 1 }
					],
				tt = [
						{ xtype: 'tbspacer', flex: 1 }
					];
		if(me.app.dData.userType == 2) {
			tb.push({
							xtype: 'combo',
							id: 'combo-pupils',
							fieldLabel: 'Skolēns',
							labelAlign: 'right',
							editable: false,
							allowBlank: false,
							store: 'RecordBookChildStore',
							listeners: { change: ctrl.rebuildRB, scope: ctrl },
							displayField: 'childName',
							valueField: 'childId'
						});
			tt.push({
							xtype: 'combo',
							id: 'combo-pupils1',
							fieldLabel: 'Skolēns',
							labelAlign: 'right',
							editable: false,
							allowBlank: false,
							store: 'RecordBookChildStore',
							listeners: { change: ctrl.changeChild, scope: ctrl },
							displayField: 'childName',
							valueField: 'childId'
						});
		}
		me.items = [{
			xtype: 'tabpanel',
			activeTab: 0,
			defaults: { closable: false },
			items: [{
				title: 'Dienasgrmāmata',
				layout: 'fit',
				listeners: {
					activate: ctrl.rebuildRB,
					scope: ctrl
				},
				items:{
					xtype: 'grid',
					frame: false,
					border: false,
					tbar: tb,
					columns: [
						{ text: 'Datums', width: 0, dataIndex: 'sc_date', hidden: true },
						{ text: 'Laiks', width: 75, dataIndex: 'sc_time' },
						{ text: 'Priekšmets', width: 150, dataIndex: 'sc_cc_id' },
						{ text: 'Nodarbības temats', flex: 1, dataIndex: 'sc_lt_id' },
						{ text: 'Majas darbi', flex: 1, dataIndex: 'sc_hw_id' },
						{ text: 'Atzīme', width: 50, dataIndex: 'mk_grade' },
						{ text: 'Skolotājs',  flex: 1 , dataIndex: 'cc_teacher' }
					],
					store: 'RecordBookStore',
					features: [
						Ext.create('Ext.grid.feature.Grouping',{
							groupHeaderTpl: 'Datums: {name} ({rows.length} nodarbība{[values.rows.length > 1 ? "s" : ""]})'
						})
					]
				}
			},
			{
				title: 'Sekmju izraksts',
				layout: 'fit',
				listeners: { activate: ctrl.getMarksSummary, scope: ctrl },
				items:{
					xtype: 'grid',
					columnLines: true,
					selModel: { selType: 'cellmodel' },
					frame: false,
					border: false,
					tbar:tt,
					columnLines: true,
					selModel: { selType: 'cellmodel' },
					store: 'MarkSummaryStore',
					enableColumnResize: true,
					enableColumnMove: false,
					enableLocking: true,
					columns: [
						{ text: 'Mācību priekšmets', width: 150, dataIndex: 'cr_name', menuDisabled:true, resizeable:true, locked: true },
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
						{ text: '15.03', width: 40, dataIndex: '2012_03_15', align:'', menuDisabled:true, resizable:false, renderer: crRenderer },
						{ text: 'Vidēja', width: 45, dataIndex: 'averageMark', align:'right', menuDisabled:true, resizeable:false, xtype: 'numbercolumn', format:'0.000', }
					]
				},
				listeners: {
					activate: function() {
						if(me.app.dData.userType != 2) ctrl.changeChild();
					},
					scope: ctrl
				}
			}]
		}];
		me.callParent();
	}
});