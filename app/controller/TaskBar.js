Ext.define('Journal.controller.TaskBar', {
	extend: 'Journal.controller.Base',

	views: [
		'taskbar.TaskBar',
		'taskbar.TrayClock'
	],
	
	models: [
		'WallpaperModel'
	],

	sysButtons: null,
	windowBar: null,
	tray: null,

	init: function() {
		var me = this;

		me.callParent();
	},

	getMainView: function () {
		var me = this;
		if(!me.mainView) {
			me.mainView = Ext.widget('taskbar', me.mConf);
		}
		return me.mainView;
	},

	getWindowBtnFromEl: function (el) {
		var c = this.windowBar.getChildByElement(el);
		return c || null;
	},

	onButtonContextMenu: function (e) {
		var me = this,
				t = e.getTarget(),
				btn = me.getWindowBtnFromEl(t);
		if (btn) {
			e.stopEvent();
			me.getMainView().windowMenu.theWin = btn.win;
			me.getMainView().windowMenu.showAt(e.getXY());
		}
	},

	onWindowBtnClick: function (btn) {
		var win = btn.win;

		if (win.minimized || win.hidden) {
			win.show();
		} else if (win.active) {
			win.minimize();
		} else {
			win.toFront();
		}
	},

	addTaskButton: function(win) {
		var config = {
			iconCls: win.iconCls,
			enableToggle: true,
			toggleGroup: 'all',
			pressed:true,
			width: 140,
			text: Ext.util.Format.ellipsis(win.title, 20),
			listeners: {
				click: this.onWindowBtnClick,
				scope: this
			},
			win: win
		};

		return this.windowBar.add(config);
	},

	removeTaskButton: function (btn) {
		var found, me = this;
		me.windowBar.items.each(function (item) {
			if (item === btn) found = item;
			return !found;
		});
		if (found) me.windowBar.remove(found);
		return found;
	},

	setActiveButton: function(btn) {
		if (btn) {
			btn.toggle(true);
		} else {
			this.windowBar.items.each(function (item) {
				if (item.isButton) item.toggle(false);
			});
		}
	}
});
