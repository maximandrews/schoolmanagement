Ext.define('Journal.view.desktop.View', {
	extend: 'Ext.panel.Panel',
	alias: 'widget.desktopview',

	uses: [
		'Ext.view.View' // dataview
	],

	activeWindowCls: 'ux-desktop-active-win',
	inactiveWindowCls: 'ux-desktop-inactive-win',

	border: false,
	html: '&#160;',
	layout: 'fit',

	xTickSize: 1,
	yTickSize: 1,

	app: null,
	controller: null,

	shortcutItemSelector: 'div.journal-desk-shortcut',
	shortcutTpl: [
		'<tpl for=".">',
			'<div class="journal-desk-shortcut" id="{name}-shortcut">',
				'<div class="journal-desk-shortcut-icon {iconCls}">',
					'<img src="',Ext.BLANK_IMAGE_URL,'" title="{name}">',
				'</div>',
				'<span class="journal-desk-shortcut-text">{name}</span>',
			'</div>',
		'</tpl>',
		'<div class="x-clear"></div>'
	],

	contextMenu: null,

	initComponent: function () {
		var me = this,
				ctrl = me.controller;

		me.tbar = ctrl.getTaskBar().getMainView();

		me.items = [
			ctrl.getWallpaper({ id: me.id+'_wallpaper' }),
			{
				xtype: 'panel',
				baseCls: 'desktop-area',
				layout: {
					type: 'vbox',
					align : 'stretch',
					pack  : 'start'
				},
				style: { position: 'absolute' },
        x: 0,
        y: 0,
				items: [
					{ xtype: 'panel', baseCls: 'desktop-area', flex:1 },
					{
						xtype: 'panel',
						baseCls: 'desktop-area',
						height:120,
						items: {
							xtype: 'dataview',
							id: 'tools-desk',
							cls: 'tools-desk',
							overItemCls: 'x-view-over',
							trackOver: true,
							itemSelector: me.shortcutItemSelector,
							store: 'ShortcutsStore',
							tpl: new Ext.XTemplate(me.shortcutTpl),
							listeners: {
								viewready: ctrl.onDeskResize,
								itemclick: ctrl.onShortcutItemClick,
								scope: ctrl
							}
						}
					},
				]
			}
		];

		me.listeners = {
    	resize: ctrl.onDeskResize,
    	render: ctrl.onDeskResize,
    	scope: ctrl
    };

    me.contextMenu = new Ext.menu.Menu({
    	items: [
				{ text: 'Change Settings', handler: me.controller.onSettings, scope: me.controller },
    		'-',
				{ text: 'Tile', handler: me.controller.tileWindows, scope: me.controller, minWindows: 1 },
				{ text: 'Cascade', handler: me.controller.cascadeWindows, scope: me.controller, minWindows: 1 }
    	]
    });

		me.callParent();

		/*
		var wallpaper = me.wallpaper;
		me.wallpaper = me.items.getAt(0);
		if(wallpaper) me.setWallpaper(wallpaper, me.wallpaperStretch);
		*/
	},

	afterRender: function () {
		var me = this,
				sView = me.down('#tools-desk');

		me.callParent();
		me.el.on('contextmenu', me.controller.onDesktopMenu, me.controller);
		if(sView) sView.store.loadData(me.app.getShortcuts());
	}
});