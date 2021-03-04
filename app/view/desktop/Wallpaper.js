Ext.define('Journal.view.desktop.Wallpaper', {
	extend: 'Ext.Component',
	alias: 'widget.wallpaper',

	app: null,
	controller: null,

	image: Ext.BLANK_IMAGE_URL,
	strech: false,

	cls: 'desk-wallpaper',
	html: '<img src="'+Ext.BLANK_IMAGE_URL+'">',
	
	// Tehnically not possible to implement in Desktop controller
	afterRender: function () { // see Ext.util.Renderable.afterRender
		var me = this; // Wallpaper object
		me.callParent();
		me.setWallpaper(me.image, me.strech);
	},
	applyState: function () {// see Component.applyState
		// Changing image after state is restored
		// object state can be saved to the database to save user opened windows, used backgrounds etc
		var me = this, // Wallpaper object
				old = me.image;
		me.callParent(arguments);
		if (old != me.image) me.setWallpaper(me.image);
	},
	getState: function () { // see Component.getState
		// What we will save to state, see Component.getState
		// object state can be saved to the database to save user opened windows, used backgrounds etc
		return this.image && { image: this.image };
	},
	
	setWallpaper: function (wallpaper, stretch) {
		var me = this, // Wallpaper object
				imgEl, bkgnd;

		me.stretch = (stretch !== false);
		me.image = wallpaper;

		if (me.rendered) {
			imgEl = me.el.dom.firstChild;

			if (!me.image || me.image == Ext.BLANK_IMAGE_URL) {
				Ext.fly(imgEl).hide();
			} else if (me.stretch) {
				imgEl.src = me.image;

				me.el.removeCls('desk-wallpaper-tiled');
				Ext.fly(imgEl).setStyle({
					width: '100%',
					height: '100%'
				}).show();
			} else {
				Ext.fly(imgEl).hide();

				bkgnd = 'url('+wallpaper+')';
				me.el.addCls('ux-wallpaper-tiled');
			}

			me.el.setStyle({ backgroundImage: bkgnd || '' });
		}

		return me;
	}
});
