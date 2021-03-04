Ext.define('Journal.controller.Base', {
	extend: 'Ext.app.Controller',

	app: null,
	mConf: null,
	isInited: false,
	mainView: null,

	init: function() {
		var me = this;

		me.mConf = {
			app: me.app,
			controller:me
		};

		me.isInited = true;
	}
});
