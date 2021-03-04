 Ext.define('Journal.view.taskbar.TaskBar', {
	extend: 'Ext.toolbar.Toolbar',
	alias: 'widget.taskbar',

	requires: [ 'Ext.button.Button' ],

	cls: 'taskbar',

	app: null,
	controller: null,
	d: null,

	initComponent: function () {
		var me = this,
				d = me.app.getDesktop();

		d.windowMenu = me.windowMenu = new Ext.menu.Menu({
			defaultAlign: 'br-tr',
			items: [
				{ text: 'Restore', handler: d.onWindowMenuRestore, scope: d },
				{ text: 'Minimize', handler: d.onWindowMenuMinimize, scope: d },
				{ text: 'Maximize', handler: d.onWindowMenuMaximize, scope: d },
				'-',
				{ text: 'Close', handler: d.onWindowMenuClose, scope: d }
			],
			listeners: {
				beforeshow: d.onWindowMenuBeforeShow,
				hide: d.onWindowMenuHide,
				scope: d
			}
		});

		me.controller.sysButtons = new Ext.toolbar.Toolbar({
			minWidth: 20,
			width: 150,
			enableOverflow: true,
			items: [
				{ xtype: 'button', text:'Settings', iconCls:'settings', handler: me.app.getDesktop().onSettings, scope: me.controller },
				{ xtype: 'button', text:'Logout', iconCls:'logout', handler: me.app.onLogout, scope: me.app }
			]
		});
		me.controller.windowBar = new Ext.toolbar.Toolbar({
			flex: 1,
			cls: 'ux-desktop-windowbar',
			items: [ '&#160;' ],
			layout: { overflowHandler: 'Scroller' }
		});
		me.controller.tray = new Ext.toolbar.Toolbar({
			width: 50,
			items: { xtype: 'trayclock', flex: 1 }
		});

		me.items = [
			me.controller.tray,
			'-',
			me.controller.windowBar,
			'-',
			me.controller.sysButtons
		];

		me.callParent();
	},

	afterLayout: function () {
		var me = this;
		me.callParent();
		me.controller.windowBar.el.on('contextmenu', me.controller.onButtonContextMenu, me.controller);
	}
});
