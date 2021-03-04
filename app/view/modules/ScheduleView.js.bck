Ext.define('Journal.view.modules.ScheduleView', {
	extend: 'Ext.window.Window',
	alias: 'widget.schedule',

	requires: [
		'Ext.view.View',
		'Journal.MonthPicker'
	],

	width: 780,
	maxWidth: 780,
	minWidth: 780,
	minHeight: 260,
	height: 480,
	border: false,
	layout: 'fit',
	monthPicker: null,

	eventItemSelector: 'div.journal-schedule-event',
	schedule: [
		'<div class="schedule-week">',
				'<tpl for=".">',
				'<tpl if="xindex == 1">',
			'<tpl if="this.isCurMonth(parent, xindex)">',
			'<div class="schedule-day">',
			'</tpl>',
			'<tpl if="this.isCurMonth(parent, xindex) === false">',
			'<div class="schedule-day not-active-month">',
			'</tpl>',
				'<div class="schedule-date">{[Ext.Date.format(parent[xindex-1].sc_date, \'d.m.Y\')]}</div>',
				'</tpl>',
				'<tpl if="this.isNextDay(parent, xindex)">',
			'</div>',
			'<tpl if="parent[xindex-1].sc_wday == 1 && xindex != 1">',
			'<div class="x-clear"></div>',
		'</div>',
		'<div class="schedule-week">',
			'</tpl>',
			'<tpl if="this.isCurMonth(parent, xindex)">',
			'<div class="schedule-day">',
			'</tpl>',
			'<tpl if="this.isCurMonth(parent, xindex) === false">',
			'<div class="schedule-day not-active-month">',
			'</tpl>',
				'<div class="schedule-date">{[Ext.Date.format(parent[xindex-1].sc_date, \'d.m.Y\')]}</div>',
				'</tpl>',
					'<tpl if="sc_from != \'99:99\'">',
					'<div class="journal-schedule-event" id="{sc_id}-event">',
						'<span class="journal-schedule-event-time">{sc_from}</span>',
						'<span class="journal-schedule-event-text">{[this.courseName(parent[xindex-1].sc_cr_id)]}</span>',
						'<div class="x-clear"></div>',
					'</div>',
					'</tpl>',
					'<tpl if="sc_from == \'99:99\'">',
					'<div class="journal-schedule-event add-event" id="{sc_id}-event">+</div>',
					'</tpl>',
				'</tpl>',
			'</div>',
			'<div class="x-clear"></div>',
		'</div>',
		'<div class="x-clear"></div>'
	],

	initComponent: function () {
		var me = this,
				ctrl = me.controller,
				fTpl = me.schedule;

		fTpl.push({
			isNextDay: function(data, idx){
				return idx > 1 && Ext.Date.format(data[idx-2].sc_date, 'd.m.Y') != Ext.Date.format(data[idx-1].sc_date, 'd.m.Y');
			},
			isCurMonth: function(vals, idx){
				//alert(ctrl.curMonth+' == '+(Ext.Date.format(vals[idx-1].sc_date, 'm')-1));
				return ctrl.curMonth == vals[idx-1].sc_date.getMonth();
			},
			courseName: function(crId) {
				return ctrl.coursesNames[crId] ? ctrl.coursesNames[crId][1]:null;
			}
		});

		me.monthPicker =  Ext.create('Ext.picker.Month',{
			floating: true
		});

		me.tbar = [ 
			{ xtype: 'tbspacer', width: 120 },
			{ xtype: 'tbspacer', flex: 1 },
			{ xtype: 'button', iconCls: Ext.baseCSSPrefix + 'tbar-page-prev', handler: ctrl.prevButtonHandler, scope: ctrl },
			{
				xtype: 'button',
				menu: {
					xtype: 'monthmenu',
					showButtons: false,
					listeners: {
						afterrender: ctrl.setMonthPickerDate,
						monthclick: ctrl.onDateSet,
						yearclick: ctrl.onDateSet,
						scope: ctrl
					}
				},
				id: 'sv-month-interval'
			},
			{ xtype: 'button', iconCls: Ext.baseCSSPrefix + 'tbar-page-next', handler: ctrl.nextButtonHandler, scope: ctrl },
			{ xtype: 'tbspacer', flex: 1 },
			{
				xtype: 'combo',
				fieldLabel: 'Klase',
				id: 'sv-class-combo',
				labelWidth: 50,
				width: 120,
				labelAlign: 'right',
				editable: false,
				allowBlank: false,
				store: 'ClassRegisterClassStore',
				listeners: {
					change: ctrl.classSelector,
					scope: ctrl
				},
				queryMode: 'local',
				displayField: 'className',
				valueField: 'classId'
			}
		];

		me.items = {
			xtype: 'dataview',
			autoScroll: true,
			overItemCls: 'x-boundlist-item-over',
			trackOver: true,
			store: 'ScheduleStore',
			itemSelector: me.eventItemSelector,
			tpl: new Ext.XTemplate(fTpl),
			listeners: {
				itemclick: ctrl.onScheduleEventClick,
				scope: ctrl
			}
		};

		me.callParent();
	}
});