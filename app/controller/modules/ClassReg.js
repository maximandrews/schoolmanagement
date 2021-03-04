Ext.define('Journal.controller.modules.ClassReg', {
	extend: 'Journal.controller.Module',

	shortcutId: 'class-reg',
	shortcutName: 'Klases',
	shortcutIconCls: 'class-reg-icon',
	viewClass: 'classreg',

	views: [ 
		'modules.ClassRegView',
		'modules.AddPersonCourseView',
		'modules.AddChildView'
	],
	models: [ 
		'ClassRegModel',
		'UserRegDataModel',
		'AddChildViewModel',
		'ClassRegDataModel',
		'ClassRegLevelModel',
		'UserRegChildModel',
		'LessonListModel'
	],
	stores: [
		'ClassRegStore',
		'ClassRegTeacherStore',
		'AddChildViewStore',
		'ClassRegDataStore',
		'ClassRegLevelStore',
		'UserRegChildStore',
		'ClassRegAddCourseStore',
		'ClassAddChildStore',
		'ClassRegChildStore',
		'ClassRegParentStore',
		'AddCourseCourseStore',
		'ChildInGroupStore'
	],

	afterMainView: function (mView) {
		var me = this;
	},

	removeTabs: function(){
		var tTabs = this.getMView().items.getAt(1).items.getCount(),
			tabsToRemove = tTabs > 1 ? this.getMView().items.getAt(1).items.getRange(1, tTabs-1):[];
		for(var i = 0; i < tabsToRemove.length; i++) {
			this.getMView().items.getAt(1).remove(tabsToRemove[i], false);
		}
	},

	addClassTabs: function(){
		var me = this;

		this.getMView().items.getAt(1).insert(1, me.getMView().myTabs['class-courses']);
		this.getMView().items.getAt(1).insert(1, me.getMView().myTabs['class-parents']);
		this.getMView().items.getAt(1).insert(1, me.getMView().myTabs['class-pupils']);
	},
	
	// View handlers  --------------------------- START
	filterLevelHandler: function (cmb, newVal, oldVal) {
		var me = this,
				psstore = me.getMView().items.getAt(0).store;
		if(psstore) {
			psstore.clearFilter(true);
			if(newVal) psstore.filter('cl_level', newVal);
			else psstore.clearFilter();
		}
	},

	afilterLevelHandler: function (cmb, newVal, oldVal) {
		var me = this,
			win = Ext.widget('addpersoncourse', { app: me.app, controller: me }),
			psstore = win.items.getAt(0).store;

		if(psstore) {
			psstore.clearFilter();
			if(newVal != 'visi') {psstore.filter('class', new RegExp('^'+newVal+'\[.]?\[a-z]?\$','m'));}						
		}
	},

	classDataHandler: function(model, records) {
		var me = this;

		me.removeTabs();//hide tabs
		me.addClassTabs();//add necessory tabs
		var fieldCount = me.getMView().items.getAt(1).items.getAt(0).items.getCount();
		for (var i = 0; i < fieldCount; i++){//show fields after new class was added
			me.getMView().items.getAt(1).items.getAt(0).items.getAt(i).show();
		}
		if (records.length > 0 && records[0]) {
			me.getMView().items.getAt(1).show();
			me.getMView().items.getAt(1).expand();
			me.getMView().items.getAt(1).items.getAt(0).loadRecord(records[0]);
		}else{
			me.getMView().items.getAt(1).hide();
		}
	},

	editCourseHandler: function(grid, rowIndex, colIndex) {
		var me = this,
				rec = grid.getStore().getAt(rowIndex),
				editCourse = Ext.widget('addcourse', { app: me.app, controller: me });
		editCourse.items.getAt(0).getForm().loadRecord(rec);
		editCourse.show();
	},

	deleteCourseHandler: function(grid, rowIndex, colIndex) {
		var me = this,
				rec = grid.getStore().getAt(rowIndex);
		grid.getSelectionModel().select(rec);
		this.app.removeAlert(me.removeCourseHandler, me);
	},

	addClass: function(){
		var me = this,
				tab = me.getMView().items.getAt(1),
				form = tab.items.getAt(0),
				fieldCount = form.items.getCount();

		tab.show();
		tab.expand();
		form.getForm().reset();

		for (var i = 4; i < fieldCount; i++){
			form.items.getAt(i).hide();
		}

		form.show();
		me.removeTabs();
	},

	hideInfoHandler: function(){
		this.getMView().items.getAt(1).hide();
	},

	addCourse: function () {
		var me = this,
			vals = Ext.getCmp('class-reg-user-add-form').getValues();
		win = Ext.widget('addcourse', { app: me.app, controller: me });
		win.items.getAt(0).getForm().setValues({ cc_cl_id: vals.cl_id, cc_cl_txt: vals.cl_level + '.' + vals.cl_postfix });
		win.show();
	},

	addPersonCourse: function () {
		var me = this,
			win = Ext.widget('addpersoncourse', { app: me.app, controller: me });

			win.items.getAt(0).getSelectionModel().on('selectionchange', function (selModel, selections) {
			this.down('#accept').setDisabled(selections.length === 0);
		}, win);
		win.show();
	},

	addChild: function () {
		var me = this,
			win = Ext.widget('addchild', { app: me.app, controller: me, classPupils: 'ClassAddChildStore'});
		win.items.getAt(0).getSelectionModel().on('selectionchange', function (selModel, selections) {
			this.down('#accept').setDisabled(selections.length === 0);
		}, win);
		win.items.getAt(0).store.load();
		win.show();
	},

	onFormSuccess: function() {
		var me = this,
				storeObj = me.getMView().items.getAt(0).store;

		storeObj.loadPage(storeObj.currentPage);

		me.removeTabs();
		me.addClassTabs();
		me.hideInfoHandler();
	},

	onFormFilledIn: function() {
		var me = this,
				storeObj = Ext.getCmp('class-reg-courses').store,
				pForm = Ext.getCmp('class-reg-user-add-form');

		storeObj.loadPage(storeObj.currentPage);
		this.close();
	},
	
	savePersonCoursesHandler: function(btn, e) {
		var me = this,
				form = btn.up('form');
				frm = form.getForm(),
				selection = Ext.getCmp('class-reg-data-grid').getView().getSelectionModel().getSelection(),
				postparams = {};

		if (frm.isValid()) {
			var vals = frm.getValues();
			
			postparams.ids = [];

			for(var i = 0;i < selection.length;i++)
				postparams.ids.push(selection[i].getData().pc_ps_id);

			postparams.ids = Ext.JSON.encode(postparams.ids);
			frm.submit({
				url: 'data/index.php?t='+form.phpcontroller+'/'+(form.phpviewmethod ? form.phpviewmethod:'main')+'&action='+(vals[form.idProperty] ? 'update' : 'create'),
				method: 'POST',
				params: postparams,
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

	removeClickHandler: function(){
		var selection = this.getMView().items.getAt(0).getView().getSelectionModel().getSelection();
			if (selection.length > 0) {
				this.getMView().items.getAt(0).store.remove(selection);
				this.getMView().items.getAt(0).store.sync();
			}
	},

	removePupilHandler: function(){
		var childGrid = Ext.getCmp('class-reg-pupils'),
			selection = childGrid.getView().getSelectionModel().getSelection();
		if (selection.length > 0) {
			childGrid.store.remove(selection);
			childGrid.store.sync();
			childGrid.store.loadPage(childGrid.store.currentPage);
		}
	},

	removeCourseHandler: function(){
		var childGrid = Ext.getCmp('class-reg-courses'),
			selection = childGrid.getView().getSelectionModel().getSelection();
		if (selection.length > 0) {
			childGrid.store.remove(selection);
			childGrid.store.sync();
			childGrid.store.loadPage(childGrid.store.currentPage);
		}
	},

	onPupilTabClick: function(){
		var pupilsGrid = Ext.getCmp('class-reg-pupils');

		if(pupilsGrid && pupilsGrid.store) {
			pupilsGrid.store.load();
		}
	},

	onParentsTabClick: function(btn, e) {
		var relativeGrid = Ext.getCmp('class-parents-grid');

		if(relativeGrid && relativeGrid.store) {
			relativeGrid.store.load();
		}
	},

	onCourseTabClick: function(btn, e) {
		var relativeGrid = Ext.getCmp('class-reg-courses');
		if(relativeGrid && relativeGrid.store) {
			relativeGrid.store.load();
		}
	},

	addChildren2Parent: function(btn, e) {
		var me = this,
			selection = me.items.getAt(0).getView().getSelectionModel().getSelection(),
			pForm = Ext.getCmp('class-reg-user-add-form'),
			vals = pForm.getValues(),
			postparams = {};
		if (selection.length > 0 && vals.cl_id > 0) {
			postparams.classId = vals.cl_id;
			postparams.cls = [];
			for(var i = 0;i < selection.length;i++) {
				vals = selection[i].getData();
				postparams.cls.push(vals.ps_id);
			}
			if(postparams.cls.length > 0) {
				postparams.cls = Ext.JSON.encode(postparams.cls);
				Ext.Ajax.request({
					url: 'data/index.php?t=classchildrenadd/modify&action=addchildclass',
					params: postparams,
					timeout: 3000,
					method: 'POST',
					success: function(xhr) {
							var childGrid = Ext.getCmp('class-reg-pupils');
							if(childGrid) childGrid.store.loadPage(childGrid.store.currentPage);

							me.close();
					}
				});
			}
		}
	},

	loadPupilStore:function() {
		Ext.getCmp('class-reg-data-grid').store.load();
	},

	statussChange: function(cmb, newVal, oldVal) {
		var me = this,
			pupilsGrid = Ext.getCmp('class-reg-data-grid');
		if (newVal == 2) {
			me.setHeight(370); 
			pupilsGrid.show();

			if(pupilsGrid && pupilsGrid.store) {
				pupilsGrid.store.load();
			}
		} else {
			me.setHeight(220); 
			Ext.getCmp('class-reg-data-grid').hide(); 
		}
		me.center();
	}
	// View handlers  --------------------------- END
});
