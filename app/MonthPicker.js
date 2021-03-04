Ext.define('Journal.MonthPicker', {
	extend: 'Ext.menu.Menu',

	alias: 'widget.monthmenu',

	requires: [
		'Ext.picker.Month'
	],
	hideOnClick : true,
	pickerId : null,
	picker: null,

	initComponent : function(){
		var me = this;

		Ext.apply(me, {
			showSeparator: false,
			plain: true,
			border: false,
			bodyPadding: 0, // remove the body padding from the monthpicker menu item so it looks like 3.3
			items: Ext.applyIf({
				cls: Ext.baseCSSPrefix + 'menu-date-item',
				id: me.pickerId,
				xtype: 'monthpicker'
			}, me.initialConfig)
		});

		me.callParent(arguments);

		me.picker = me.down('monthpicker');
		me.relayEvents(me.picker, ['select']);

		if (me.hideOnClick) {
			me.on('select', me.hidePickerOnSelect, me);
		}
	},

	hidePickerOnSelect: function() {
		Ext.menu.Manager.hideAll();
	}
});