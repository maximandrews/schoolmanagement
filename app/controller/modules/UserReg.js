Ext.define('Journal.controller.modules.UserReg', {
	extend: 'Journal.controller.Module',

	shortcutId: 'user-reg',
	shortcutName: 'Lietotāji',
	shortcutIconCls: 'user-reg-icon',
	viewClass: 'userreg',

	views: [ 'modules.UserRegView', 'modules.AddChildView', 'modules.AddCourseView' ],
	models: [ 'UserRegDataModel', 'UserRegTypeModel', 'UserRegAddCourseModel', 'ClassRegModel', 'UserRegChildModel', 'AddCourseClassModel', 'AddChildViewModel'],
	stores: [ 'UserRegDataStore', 'UserRegTypeStore', 'UserRegAddCourseStore', 'ClassRegStore', 'UserRegChildStore', 'AddCourseClassStore', 'AddChildViewStore', 'UserRegParentStore'],

	afterMainView: function (mView) {
		var me = this;
			//myMask = new Ext.LoadMask(Ext.getBody(), {msg:"Please wait...", store :mView.down('#user-reg-person-rel-pupil').store});
		//myMask.show();
	},

	removeTabs: function(){
		var tTabs = this.getMView().items.getAt(1).items.getCount(),
			tabsToRemove = tTabs > 1 ? this.getMView().items.getAt(1).items.getRange(1, tTabs-1):[];
		for(var i = 0; i < tabsToRemove.length; i++) {
			this.getMView().items.getAt(1).remove(tabsToRemove[i], false);
		}
	},

	userTypes: function(){
		var me = this,
				tbPan = me.getMView().items.getAt(1),//Panel
				formEl = tbPan.items.getAt(0);//bookmak

		tbPan.show();
		//tbPan.expand();
		me.removeTabs();

		var userType = formEl.items.getAt(4).getValue(),//Uzzinam tipu
			psid = formEl.items.getAt(0).getValue(),//Uzzinam vai lietotājs eksiste, vai tam piešķirts ID (ps_id)
			fieldCount = formEl.items.getCount();

		for (var i = 0; i < fieldCount; i++){ //Nepieciešams lai attelot laukus pēc jauna lietotāja pievienošanas
			formEl.items.getAt(i).setDisabled(false);
			formEl.items.getAt(i).show();
		}

		switch (parseInt(userType,10)){
			case 1: // Skolēns
				formEl.items.getAt(10).hide();
				formEl.items.getAt(10).clearInvalid();
				formEl.items.getAt(10).setDisabled(true);
				tbPan.insert(1, me.getMView().myTabs['pupil-rel']);
			break;
			case 2: // Vecāks
				formEl.items.getAt(9).hide();
				formEl.items.getAt(9).clearInvalid();
				formEl.items.getAt(9).setDisabled(true);
				if(psid) tbPan.insert(1, me.getMView().myTabs['rel-pupil']);
				break;
			case 3: // Skolotājs
				formEl.items.getAt(9).hide();
				formEl.items.getAt(9).clearInvalid();
				formEl.items.getAt(9).setDisabled(true);
				formEl.items.getAt(10).hide();
				formEl.items.getAt(10).clearInvalid();
				formEl.items.getAt(10).setDisabled(true);
				if(psid) tbPan.insert(1, me.getMView().myTabs['teacher-course']);
			break;
			case 4: // Sk. adm. darbinieks
				formEl.items.getAt(9).hide();
				formEl.items.getAt(9).clearInvalid();
				formEl.items.getAt(9).setDisabled(true);
				formEl.items.getAt(10).hide();
				formEl.items.getAt(10).clearInvalid();
				formEl.items.getAt(10).setDisabled(true);
			break;
		}
	},

	// View handlers  --------------------------- START
	addUserHandler: function() {
		var me = this,
				frm = me.getMView().items.getAt(1).items.getAt(0).getForm(),
				fieldCount = me.getMView().items.getAt(1).items.getAt(0).items.getCount();
		me.getMView().items.getAt(1).show();
		frm.reset();

		for (var i = 7; i < fieldCount; i++){
			me.getMView().items.getAt(1).items.getAt(0).items.getAt(i).hide();
		}
		me.removeTabs();
	},

	changedTypeHandler: function (cmb, newVal, oldVal) {
		var me = this,
				psstore = me.getMView().items.getAt(0).store;
		if(psstore) {
			newVal = parseInt(newVal,10);
			if(!isNaN(newVal)) {
				psstore.clearFilter(true);
				psstore.filter('ps_ut_id', newVal);
			}else
				psstore.clearFilter();

			if(newVal == 1)
				me.getMView().items.getAt(0).columns[6].setVisible(true);
			if(parseInt(oldVal) == 1)
				me.getMView().items.getAt(0).columns[6].setVisible(false);
		}
	},

	showInfoHandler: function(model, records) {
		var me = this;

		me.getMView().down('#delete-person').setDisabled(records.length === 0);

		if (records.length > 0 && records[0]) {
			me.getMView().items.getAt(1).show();
			//me.getMView().items.getAt(1).expand();
			me.getMView().items.getAt(1).items.getAt(0).loadRecord(records[0]);
			me.userTypes();
		}else{
			//me.getMView().items.getAt(1).collapse(Ext.Component.DIRECTION_BOTTOM, true);
			me.getMView().items.getAt(1).hide();
		}
	},

	hideInfoHandler: function(){
		var me = this;

		var selModel = me.getMView().items.getAt(0).getSelectionModel();
		if(selModel.getSelection().length > 0)
			selModel.deselectAll();
		
		if(!me.getMView().items.getAt(1).isHidden()) { 
			//me.getMView().items.getAt(1).collapse(Ext.Component.DIRECTION_BOTTOM, true);
			me.getMView().items.getAt(1).hide();
		}
	},

	addChild: function () {
		var me = this,
				win = Ext.widget('addchild', { app: me.app, controller: me });
		
		win.items.getAt(0).getSelectionModel().on('selectionchange', function (selModel, selections) {
			this.down('#accept').setDisabled(selections.length === 0);
		}, win);
		win.items.getAt(0).store.load();
		win.show();
	},
	
	parentsChildSelectionChange: function(selModel, selections) {
		var me = this;

		me.down('#delete-puplil').setDisabled(selections.length === 0);
	},

	addCourse: function () {
		var me = this;

		Ext.widget('addcourse', { app: me.app, controller: me }).show();
	},

	removeClickHandler: function(){
		var selection = this.getMView().items.getAt(0).getView().getSelectionModel().getSelection();
			if (selection.length > 0) {
				this.getMView().items.getAt(0).store.remove(selection);
				this.getMView().items.getAt(0).store.sync();
			}
	},

	onFormSuccess: function() {
		var me = this,
				storeObj = me.getMView().items.getAt(0).store;
		storeObj.loadPage(storeObj.currentPage);
		me.hideInfoHandler();
	},

	removeChildHandler: function(){
		var childGrid = Ext.getCmp('user-reg-person-rel-pupil'),
			selection = childGrid.getView().getSelectionModel().getSelection();
		if (selection.length > 0) {
			childGrid.store.remove(selection);
			childGrid.store.sync();
			//childGrid.store.loadPage(childGrid.store.currentPage);
		}
	},

	removeCourseHandler: function(){
		var childGrid = Ext.getCmp('user-reg-teacher-course'),
			selection = childGrid.getView().getSelectionModel().getSelection();
		if (selection.length > 0) {
			childGrid.store.remove(selection);
			childGrid.store.sync();
			//childGrid.store.loadPage(childGrid.store.currentPage);
		}
	},
	
	onPupilsTabClick: function(btn, e) {
		var childGrid = Ext.getCmp('user-reg-person-rel-pupil');

		if(childGrid && childGrid.store) {
			childGrid.store.load();
		}
	},
	
	onParentsTabClick: function(btn, e) {
		var relativeGrid = Ext.getCmp('user-reg-person-pupil-rel');

		if(relativeGrid && relativeGrid.store) {
			relativeGrid.store.load();
		}
	},

	addChildren2Parent: function(btn, e) {
		var me = this,
				selection = me.items.getAt(0).getView().getSelectionModel().getSelection(),
				pForm = Ext.getCmp('user-reg-user-edit-form'),
				vals = pForm.getValues(),
				postparams = {};
		if (selection.length > 0 && vals.ps_id > 0) {
			postparams.parentId = vals.ps_id;
			postparams.chldrn = [];
			for(var i = 0;i < selection.length;i++) {
				vals = selection[i].getData();
				postparams.chldrn.push(vals.ps_id);
			}
			
			if(postparams.chldrn.length > 0) {
				postparams.chldrn = Ext.JSON.encode(postparams.chldrn);
				Ext.Ajax.request({
					url: 'data/index.php?t=parentschildrenadd/modify&action=addchildren',
					params: postparams,
					timeout: 3000,
					method: 'POST',
					success: function(xhr) {
							var childGrid = Ext.getCmp('user-reg-person-rel-pupil');
							if(childGrid) childGrid.store.loadPage(childGrid.store.currentPage);
							
					    me.close();
					}
				});
			}
		}
	}
	// View handlers  --------------------------- END
});
