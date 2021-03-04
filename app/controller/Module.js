Ext.define('Journal.controller.Module', {
	extend: 'Journal.controller.Base',

	//all of this props should be implemented in child classes
	id: null,
	isShortcut: true,
	shortcutName: null,
	shortcutId: null,
	shortcutIconCls: null,
	viewClass: null,

	mainView: null, //here will be stored object

	getMView: function () {
		var me = this,
				cfg = me.mConf || { app: me.app, controller: me };

		me.mainView = me.app.getDesktop().getWindow(me.shortcutId+'-view');
		if(!me.mainView) {
			Ext.apply(cfg, {
				id: me.shortcutId+'-view',
				title: me.shortcutName,
				iconCls: me.shortcutIconCls
			});
			me.mainView = me.app.getDesktop().createWindow(me.viewClass, cfg);
			me.afterMainView(me.mainView);
		}
		me.mainView.show();
		return me.mainView;
	},

	saveHandler: function(btn, e) {
		var me = this,
				form = btn.up('form');
				frm = form.getForm();

		if (frm.isValid()) {
			var vals = frm.getValues();

			frm.submit({
				url: 'data/index.php?t='+form.phpcontroller+'/'+(form.phpviewmethod ? form.phpviewmethod:'main')+'&action='+(vals[form.idProperty] ? 'update' : 'create'),
				method: 'POST',
				submitEmptyText:false,
				success: function(pForm, action) {
					me.app.baloon('Ieraksts veiksmīgi saglabats');
					if(typeof form.onSuccess == 'function')
						form.onSuccess.call(form.scope ? form.scope : form);
				},
				failure: function(pForm, action) {
					me.app.baloon('Ieraksts netika saglabāts');
					if(action.result && action.result.errors && action.result.errors.length > 0) {
						form.getForm().markInvalid(action.result.errors);
					}

					if(typeof form.onFailure == 'function')
						form.onFailure.call(form.scope ? form.scope : form);
				}
			});
		}
	},

	afterMainView: Ext.emptyFn
});
