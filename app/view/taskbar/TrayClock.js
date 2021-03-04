Ext.define('Journal.view.taskbar.TrayClock', {
	extend: 'Ext.toolbar.TextItem',
	alias: 'widget.trayclock',

	cls: 'taskbar-trayclock',
	html: '&#160;',
	tFormat: 'H:i',
	tfTick: 0,
	tpl: '{time}',
	timer: null,
	lText: null,

	initComponent: function () {
		var me = this;
		me.callParent();
		if (typeof(me.tpl) == 'string') me.tpl = new Ext.XTemplate(me.tpl);
	},

	afterRender: function () {
		var me = this;
		Ext.Function.defer(me.syncTime, 100, me);
		me.callParent();
	},

	onDestroy: function () {
		var me = this;
		if (me.timer) {
			window.clearTimeout(me.timer);
			me.timer = null;
		}
		me.callParent();
	},

	syncTime: function () {
		var me = this,
				time = Ext.Date.format(new Date(), me.tFormat),
				text = me.tpl.apply({ time: time });

		if (me.lText != text) {
			me.setText(text);
			me.lText = text;
		}
		me.timer = Ext.Function.defer(me.syncTime, 15000, me);
	}
});
