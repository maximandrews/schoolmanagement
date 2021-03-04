Ext.define('Journal.controller.Desktop', {
	extend: 'Journal.controller.Base',

	taskBar: null,
	wallpaper: null,
	windows: null,

	uses: [
		'Ext.util.MixedCollection',
		'Ext.window.Window'
	],

	views: [ 'desktop.View','desktop.Wallpaper','desktop.Settings' ],
	models: [ 'ShortcutModel' ],
	stores: [ 'ShortcutsStore' ],

	init: function() {
		var me = this;
		me.callParent();

		me.windows = new Ext.util.MixedCollection();
	},

	getMainView: function () {
		var me = this;
		if(!me.mainView) me.mainView = Ext.widget('desktopview', me.mConf);
		return me.mainView;
	},

	getTaskBar: function() {
		var me = this;
		if(!me.taskBar) me.taskBar = me.app.getController('TaskBar');
		return me.taskBar;
	},

// System handlers
	onSettings: function () {
		var dlg = Ext.widget('desktopsettings', {
			app: this.app,
			controller: this
		});
		dlg.show();
	},

// Wallpaper events & methods
	getWallpaper: function (cfg) {
		var me = this;
		if(!me.wallpaper) {
			Ext.apply(cfg, me.mConf);
			me.wallpaper = Ext.widget('wallpaper', cfg);
		}
		return me.wallpaper;
	},

	setWallpaper: function(wallpaper, stretch) {
		this.getWallpaper().setWallpaper(wallpaper, stretch);
	},

// Desktop events
	onDesktopMenu: function (e) {
		var me = this,
				menu = me.getMainView().contextMenu;
		e.stopEvent();
		if (!menu.rendered) menu.on('beforeshow', me.onDesktopMenuBeforeShow, me);

		menu.showAt(e.getXY());
		menu.doConstrain();
	},

	onDesktopMenuBeforeShow: function (menu) {
		var me = this,
				count = me.windows.getCount();

		menu.items.each(function (item) {
			var min = item.minWindows || 0;
			item.setDisabled(count < min);
		});
	},

// Window context menu handlers
	onWindowMenuBeforeShow: function (menu) {
		var items = menu.items.items, win = menu.theWin;
		items[0].setDisabled(win.maximized !== true && win.hidden !== true); // Restore
		items[1].setDisabled(!win.minimizable || win.minimized === true); // Minimize
		items[2].setDisabled(!win.maximizable || !win.resizable || win.maximized === true || win.hidden === true); // Maximize
	},

	onWindowMenuClose: function () {
		var me = this, win = me.windowMenu.theWin;
		win.close();
	},

	onWindowMenuHide: function (menu) {
		menu.theWin = null;
	},

	onWindowMenuMaximize: function () {
		var me = this, win = me.windowMenu.theWin;
		win.maximize();
	},

	onWindowMenuMinimize: function () {
		var me = this, win = me.windowMenu.theWin;
		win.minimize();
	},

	onWindowMenuRestore: function () {
		var me = this, win = me.windowMenu.theWin;
		me.restoreWindow(win);
	},

// Windows management
	setTickSize: function(xTickSize, yTickSize) {
		var me = this,
				xt = me.xTickSize = xTickSize,
				yt = me.yTickSize = (arguments.length > 1) ? yTickSize : xt;

		me.windows.each(function(win) {
			var dd = win.dd, resizer = win.resizer;
			dd.xTickSize = xt;
			dd.yTickSize = yt;
			resizer.widthIncrement = xt;
			resizer.heightIncrement = yt;
		});
	},

	cascadeWindows: function() {
		var x = 0, y = 0,
				zmgr = this.app.getDesktop().getDesktopZIndexManager();

		zmgr.eachBottomUp(function(win) {
			if (win.isWindow && win.isVisible() && !win.maximized) {
				win.setPosition(x, y);
				x += 20;
				y += 20;
			}
		});
	},

	createWindow: function(wdg, config) {
		var me = this, win,
				cfg = Ext.applyIf(config || {}, {
					stateful: false,
          isWindow: true,
          constrainHeader: true,
          minimizable: true,
          maximizable: true
				});

		win = Ext.widget(wdg, cfg);
		me.getMainView().add(win);
		me.windows.add(win);

		win.taskButton = me.getTaskBar().addTaskButton(win);
		win.animateTarget = win.taskButton.el;

		win.on({
			activate: me.updateActiveWindow,
			beforeshow: me.updateActiveWindow,
			deactivate: me.updateActiveWindow,
			minimize: me.minimizeWindow,
			destroy: me.onWindowClose,
			scope: me
		});

		win.on({
			boxready: function () {
				win.dd.xTickSize = me.xTickSize;
				win.dd.yTickSize = me.yTickSize;

				if (win.resizer) {
					win.resizer.widthIncrement = me.xTickSize;
					win.resizer.heightIncrement = me.yTickSize;
				}
			},
			single: true
		});

		// replace normal window close w/fadeOut animation:
		win.doClose = function ()  {
			win.doClose = Ext.emptyFn; // dblclick can call again...
			win.el.disableShadow();
			win.el.fadeOut({
				listeners: {
					afteranimate: function () {
						win.destroy();
					}
				}
			});
		};

		return win;
	},

	getActiveWindow: function () {
		var win = null,
				zmgr = this.app.getDesktop().getDesktopZIndexManager();

		if (zmgr) {
			// We cannot rely on activate/deactive because that fires against non-Window
			// components in the stack.

			zmgr.eachTopDown(function (comp) {
				if (comp.isWindow && !comp.hidden) {
					win = comp;
					return false;
				}
				return true;
			});
		}

		return win;
	},

	getDesktopZIndexManager: function () {
		var windows = this.windows;
		// TODO - there has to be a better way to get this...
		return (windows.getCount() && windows.getAt(0).zIndexManager) || null;
	},

	getWindow: function(id) {
		return this.windows.get(id);
	},

	minimizeWindow: function(win) {
		win.minimized = true;
		win.hide();
	},

	restoreWindow: function (win) {
		if (win.isVisible()) {
			win.restore();
			win.toFront();
		} else {
			win.show();
		}
		return win;
	},

	tileWindows: function() {
		var me = this,
				d = me.app.getDesktop();
				availWidth = d.getMainView().body.getWidth(true),
				x = d.xTickSize,
				y = d.yTickSize,
				nextY = y;

		d.windows.each(function(win) {
			if (win.isVisible() && !win.maximized) {
				var w = win.el.getWidth();

				// Wrap to next row if we are not at the line start and this Window will
				// go off the end
				if (x > d.xTickSize && x + w > availWidth) {
					x = d.xTickSize;
					y = nextY;
				}

				win.setPosition(x, y);
				x += w + d.xTickSize;
				nextY = Math.max(nextY, y + win.el.getHeight() + d.yTickSize);
			}
		});
	},

	updateActiveWindow: function () {
		var me = this,
				activeWindow = me.getActiveWindow(),
				last = me.lastActiveWindow;
		if (activeWindow === last) return;

		if (last) {
			if (last.el.dom) {
				last.addCls(me.inactiveWindowCls);
				last.removeCls(me.activeWindowCls);
			}
			last.active = false;
		}

		me.lastActiveWindow = activeWindow;

		if (activeWindow) {
			activeWindow.addCls(me.activeWindowCls);
			activeWindow.removeCls(me.inactiveWindowCls);
			activeWindow.minimized = false;
			activeWindow.active = true;
		}

		me.getTaskBar().setActiveButton(activeWindow && activeWindow.taskButton);
	},

	onWindowClose: function(win) {
		var me = this;
		me.windows.remove(win);
		me.getTaskBar().removeTaskButton(win.taskButton);
		me.updateActiveWindow();
	},

	onDeskResize: function (obj, newWidth, newHeight, opts) {
		var cmp = Ext.getCmp('tools-desk'),
				nds = cmp.getNodes(),
				l = nds.length,
				tWidth = 0;
	
		if(l > 0) {
			for(var i = 0;i < l;i++) {
	  		tWidth += nds[i].offsetWidth+16;
	  	}
	  	cmp.setWidth(tWidth);
	  }
	},

	onShortcutItemClick: function (dataView, record) {
		var me = this, module = me.app.getModule(record.data.moduleId+'-controller'),
				win = module && module.getMView();

		if (win) me.restoreWindow(win);
	}
});
