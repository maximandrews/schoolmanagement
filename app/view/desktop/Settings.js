Ext.define('Journal.view.desktop.Settings', {
	extend: 'Ext.window.Window',
	alias: 'widget.desktopsettings',

	app: null,
	controller: null,

	uses: [
		'Ext.tree.View',
		'Ext.layout.container.Border',
		'Ext.tree.Panel',
		'Ext.layout.container.Anchor',
		'Ext.form.field.Checkbox'
	],

	layout: 'anchor',
	title: 'Nomainīt darbvirsmas attēlu',
	modal: true,
	width: 640,
	height: 480,
	border: false,

	initComponent: function () {
		var me = this;

		me.selected = me.app.getDesktop().getWallpaper().image;
		me.stretch = me.app.getDesktop().getWallpaper().stretch;

		me.preview = Ext.widget('wallpaper', { app: me.app, controller: me.controller });
		me.preview.setWallpaper(me.selected);
		me.tree = me.createTree();

		me.buttons = [
			{ text: 'Labi', handler: me.onOK, scope: me },
			{ text: 'Atcelt', handler: me.close, scope: me }
		];

		me.items = [{
			anchor: '0 -30',
			border: false,
			layout: 'border',
			items: [
				me.tree,
				{
					xtype: 'panel',
					title: 'Priekšskatījums',
					region: 'center',
					layout: 'fit',
					items: [ me.preview ]
				}
			]
		},{
			xtype: 'checkbox',
			boxLabel: 'Izstiept',
			checked: me.stretch,
			listeners: {
				change: function (comp) {
					me.stretch = comp.checked;
				}
			}
		}];

		me.callParent();
	},

	createTree : function() {
		var me = this;

		function child (img) {
			return { img: img, text: me.getTitleOfWallpaper(img), iconCls: '', leaf: true };
		}

		var tree = new Ext.tree.Panel({
			title: 'Darbvirsmas fons',
			rootVisible: false,
			lines: false,
			autoScroll: true,
			width: 150,
			region: 'west',
			split: true,
			minWidth: 100,
			listeners: {
				afterrender: { fn: this.setInitialSelection, delay: 100 },
				select: this.onSelect,
				scope: this
			},
			store: new Ext.data.TreeStore({
				model: 'Journal.model.WallpaperModel',
				root: {
					text:'Attēls',
					expanded: true,
					children:[
						{ text: "None", iconCls: '', leaf: true },
						child('school.jpg'),
						child('Blue-Sencha.jpg'),
						child('Dark-Sencha.jpg'),
						child('Wood-Sencha.jpg'),
						child('blue.jpg'),
						child('desk.jpg'),
						child('desktop.jpg'),
						child('desktop2.jpg')
					]
				}
			})
		});

		return tree;
	},

	getTitleOfWallpaper: function (path) {
		var text = path, slash = path.lastIndexOf('/');
		if (slash >= 0) text = text.substring(slash+1);
		var dot = text.lastIndexOf('.');
		text = Ext.String.capitalize(text.substring(0, dot));
		text = text.replace(/[-]/g, ' ');
		return text;
	},

	onOK: function () {
		var me = this;
		if (me.selected) me.app.getDesktop().setWallpaper(me.selected, me.stretch);
		me.destroy();
	},

	onSelect: function (tree, record) {
		var me = this;

		if (record.data.img) me.selected = 'i/wallpapers/' + record.data.img;
		else me.selected = Ext.BLANK_IMAGE_URL;

		me.preview.setWallpaper(me.selected);
	},

	setInitialSelection: function () {
		var me = this,
				s = me.app.getDesktop().getWallpaper().image;
		if (s) {
			var path = 'i/wallpapers/' + me.getTitleOfWallpaper(s);
			me.tree.selectPath(path, 'text');
		}
	}
});
