Ext.Loader.setConfig({ enabled:true });

Ext.form.field.Date.prototype.startDay = 1;
Ext.picker.Date.prototype.startDay = 1;
Ext.picker.Month.prototype.okText = 'Labi';
Ext.picker.Month.prototype.cancelText = 'Atcelt';
Ext.form.field.Date.prototype.altFormats = 'd/m/Y|d.m.Y';
Ext.grid.RowEditor.prototype.cancelBtnText = 'Atcelt';
Ext.Msg.buttonText.cancel = 'Atcelt';
Ext.grid.RowEditor.prototype.saveBtnText = 'Atjaunināt';
Ext.grid.RowEditor.prototype.errorsText = 'Kļūda';
Ext.grid.RowEditor.prototype.dirtyText = 'Ir nepieciešams atjaunināt vai atcelt izmaiņas';
Ext.Msg.buttonText.yes = 'Jā';
Ext.Msg.buttonText.no = 'Nē, dienai';
Ext.Msg.buttonText.ok = 'Labi';

Ext.application({
	requires: [
		'Ext.container.Viewport',
		'Journal.view.JournalLogin'
	],

	name: 'Journal',
	appFolder: 'app',

	modules: {},
	modulesList: [
		'Desktop','TaskBar' //system controllers (always required)
	],
	desktop: null,
	useQuickTips: true,
	dData: {
		sid: null,
		sessionExpire: null,
		userId: null,
		userName: null,
		userFirstName: null,
		userLastName: null,
		userPersonCode: null,
		userMailSms: null,
		userLogin: null,
		userType: null,
		userTypeTxt: null
	},
	loginWin: null,
	loginWinOn: false,
	pendingRequests: [],

	controllers: ['Desktop'],

	launch: function() {
		var me = this;

		Ext.Ajax.on('beforerequest', function (conn, ops, eOpts) {

			if(me.dData.sid > 0) {
				if(!ops.params) ops.params = {};
				ops.params.sid = me.dData.sid;
			}

			if(ops.url && !ops.url.match(/t=login\/main/)) {
				var oldOps = {}, 
						oldSuccess, oldScope;

				for(var a in ops) {
					oldOps[a] = ops[a];
				}

				if(ops.success) oldSuccess = ops.success;
				if(ops.scope) oldScope = ops.scope;

				ops.success = function (xhr) {
					try {
						var res = Ext.JSON.decode(xhr.responseText);
					} catch (e) {}
		
					if(res) {
						if(res.sid) me.dData.sid = res.sid;
						if(res.expire) me.dData.sessionExpire = res.expire;
						if(res.loggendIn && typeof oldSuccess == 'function') {
							if(oldScope)
								oldSuccess.call(oldScope, xhr);
							else
								oldSuccess(xhr);
						}else if(!res.loggendIn) {
							me.pendingRequests.push(oldOps);
							if(!me.loginWinOn) {
								me.loginWinOn = true;
								me.showLogin();
							}
						}
					}
				}
			}
		});

		Ext.Ajax.request({
			url: 'data/index.php?t=login/main',
			timeout: 3000,
			method: 'POST',
			success: function(xhr) {
				try {
					var rs = Ext.JSON.decode(xhr.responseText);
				} catch (e) {}

				if(rs) {
					if(rs.userName) me.dData.userName = rs.userName;
					if(rs.userFirstName) me.dData.userFirstName = rs.userFirstName;
					if(rs.userLastName) me.dData.userLastName = rs.userLastName;
					if(rs.userPersonCode) me.dData.userPersonCode = rs.userPersonCode;
					if(rs.userMailSms) me.dData.userMailSms = rs.userMailSms;
					if(rs.userLogin) me.dData.userLogin = rs.userLogin;
					if(rs.userId) me.dData.userId = rs.userId;
					if(rs.userType) me.dData.userType = rs.userType;
					if(rs.userTypeTxt) me.dData.userTypeTxt = rs.userTypeTxt;
				}

				me.loadModulesList();
			}
		});
	},

	run: function() {
		var me = this;

		//alert('run');
		me.initModules();

		Ext.create('Ext.container.Viewport', {
			layout: 'fit',
			items: me.getDesktop().getMainView()
		});
	},

	loadModulesList: function() {
		var me = this;

		Ext.Ajax.request({
			url: 'data/index.php?t=usersitem/loggedUserModules',
			timeout: 3000,
			method: 'POST',
			success: function(xhr) {
				try {
					var rs = Ext.JSON.decode(xhr.responseText);
				} catch (e) {}

				if(rs && rs.modules && rs.modules.length > 0)
					me.modulesList = me.modulesList.concat(rs.modules);

				me.run();
			}
		});
	},

	showLogin: function() {
		var me = this;

		me.loginWin = Ext.widget('journallogin', { app: me });
		if(me.dData.userId > 0 && me.dData.userLogin != null) {
			var userField = me.loginWin.items.getAt(0).items.getAt(0);
			userField.setValue(me.dData.userLogin);
			userField.setReadOnly(true);
		}

		me.loginWin.show();
	},

	doLogin: function (btn, e) {
		var me = this,
				frm = btn.up('form').getForm();

		frm.submit({
			url: 'data/index.php?t=login/main&action=login',
			timeout: 3000,
			method: 'POST',
			success: function(pForm, act) {
				var rs = act.result;
				if(rs) {
					if(me.dData.userLogin && (!rs.userLogin || me.dData.userLogin != rs.userLogin))
						window.location.href=window.location.href;
					if(rs.userName) me.dData.userName = rs.userName;
					if(rs.userFirstName) me.dData.userFirstName = rs.userFirstName;
					if(rs.userLastName) me.dData.userLastName = rs.userLastName;
					if(rs.userPersonCode) me.dData.userPersonCode = rs.userPersonCode;
					if(rs.userMailSms) me.dData.userMailSms = rs.userMailSms;
					if(rs.userLogin) me.dData.userLogin = rs.userLogin;
					if(rs.userId) me.dData.userId = rs.userId;
					if(rs.userType) me.dData.userType = rs.userType;
					if(rs.userTypeTxt) me.dData.userTypeTxt = rs.userTypeTxt;
				}

				me.loginWin.close();
				me.loginWinOn = false;
				me.loginWin = null;
				me.processPending();
			}
		});
	},
	
	onLogout: function() {
		Ext.MessageBox.show({
			title: 'Iziet',
			msg: 'Vai tiesam velāties iziet?',
			icon: Ext.MessageBox.QUESTION,
			buttons: Ext.MessageBox.YESNO,
			buttonText:{ 
				yes: 'Jā, iziet', 
				no: 'Ne' 
			},
			fn: function(btn) {
				if(btn == 'yes') {
					Ext.Ajax.request({
						url: 'data/index.php?t=login/main&action=logout',
						timeout: 3000,
						method: 'POST',
						success: function(xhr) {
							try {
								var rs = Ext.JSON.decode(xhr.responseText);
							} catch (e) {}

							//alert(xhr.responseText); //
							if(rs && !rs.loggendIn) {
								window.location.href=window.location.href;
								me.dData.userName = null;
								me.dData.userFirstName = null;
								me.dData.userLastName = null;
								me.dData.userPersonCode = null;
								me.dData.userMailSms = null;
								me.dData.userLogin = null;
								me.dData.userId = null;
								me.dData.userType = null;
								me.dData.userTypeTxt = null;
							}
						}
					});
				}
			}
		});
	},

	processPending: function () {
		var me = this,
				req = Ext.clone(me.pendingRequests);

		//alert('pending: '+req.length);
		me.pendingRequests = [];
		
		for(var i = 0;i < req.length;i++) {
			Ext.Ajax.request(req[i]);
		}
	},

	initModules: function() {
		var me = this,
				ms = me.modules;

		Ext.each(me.modulesList, function (mName) {
			var module = me.getController(mName);
			if(!module || ms[module.id])
				return false;

			if(module.shortcutId)
				module.id = module.shortcutId + '-controller';
			if(module.id) {
				ms[module.id] = module;
				ms[module.id].app = me;
				if(!ms[module.id].isInited) ms[module.id].init();
			}else
				alert('Module(controller) '+mName+' id property is not set! Please fix!');
		});
	},

	getModule: function(name) {
		var ms = this.modules;
		return ms[name] || null;
	},

	getDesktop: function() {
		var me = this;
		if(!me.desktop) {
			me.desktop = me.getController('Desktop');
			me.desktop.app = me;
			if(!me.desktop.isInited) me.desktop.init();
			if(me.desktop.mConf && !me.desktop.mConf.app) me.desktop.mConf.app = me;
		}
		return me.desktop;
	},

	getShortcuts: function () {
		var me = this,
				ms = me.modules,
				sc = [];

		if(ms == undefined || ms == null) return sc;

		for(m in ms) {
			if(ms[m].isShortcut)
				sc.push([
					ms[m].shortcutName || 'Undefined module name',
					ms[m].shortcutIconCls || '',
					ms[m].shortcutId || ''
				]);
		}

		return sc;
	},
	
	changeDate: function (objDate, dt) {
		var me = this,
				i = 0/*,
			odt = dt*/;
		do {
			//alert('iteration: '+i+'; dt:'+dt);
			var date = objDate.getDate(),
				month = objDate.getMonth(),
				year = objDate.getFullYear(),
				count = me.DaysInMonth(year, month+1);
			
			/*if(odt == dt && i > 0) {
				alert(date+' + '+dt+' <= '+count+' && '+date+' + '+dt+' > 0 -> '+(date + dt)+' <= '+count+' && '+(date+dt)+' > 0');
			}*/
			if (date + dt <= count && date + dt >= 0) {
				objDate.setDate(date + dt);
				break;
			}else if (date + dt < 0) {
				if(month == 0) {
					objDate.setMonth(11);
					objDate.setFullYear(year-1);
				}else {
					objDate.setDate(1);
					objDate.setMonth(month-1);
				}

				objDate.setDate(me.DaysInMonth(objDate.getFullYear(), objDate.getMonth()+1));
				dt = dt + date;
			}else if(date + dt > count) {
				objDate.setDate(1);
				if(month == 11) {
					objDate.setMonth(0);
					objDate.setFullYear(year+1);
				}else
					objDate.setMonth(month+1);

				dt = dt + date - count - 1;
			}
			i++;
		} while (true);
	},

	DaysInMonth: function (y,m) {
		return new Date(y,m,0).getDate();
	},

	baloon: function(text) { //http://www.sencha.com/forum/showthread.php?109102-Balloon-Popups
		Ext.create('widget.notification', {
			position: 't',
			cls: 'ux-notification-light',
			closable: false,
			title: 'Paziņojums',
			iconCls:'ux-notification-icon-information',
			html: text
		}).show();
	},
	
	removeAlert: function(funcToCall, scope){
		Ext.Msg.show({
			title:'Dzēst?',
			msg: ' Vai Jūs tiešām vēlaties dzēst ierakstu?',
			modal:true,
			buttons: Ext.Msg.YESNO,
			icon: Ext.Msg.QUESTION,
			fn: function (buttonId, text, opt) {
				if(buttonId == 'yes')
					funcToCall.call(scope);
			}
		});	
	},

	childAddFiltration: function(){
		var me = this,
				psstore = me.items.getAt(0).store;

		if(psstore) {
			psstore.clearFilter(true);
			var name = me.down('#bar-ps-firsname').getValue(),
				surname = me.down('#bar-ps-lastname').getValue(),
				clname = me.down('#bar-cl-name').getValue(),
				pk = me.down('#bar-ps-pk').getValue(),
				filters = [];
			if(name) filters.push({ property:'ps_firstname', value:name });
			if(surname) filters.push({ property:'ps_lastname', value:surname });
			if(pk) filters.push({ property:'ps_personcode', value:pk });
			if(clname) filters.push({ property:'ps_cl_txt', value:clname });
			if(filters.length > 0) {
				psstore.clearFilter();
				psstore.filter(filters);
			} else
				psstore.clearFilter();
		}
	}
	
	
	/*,

	onUnload: function(e) {
		if(this.fireEvent('beforeunload', this) === false) {
			e.stopEvent();
		}
	}*/
});