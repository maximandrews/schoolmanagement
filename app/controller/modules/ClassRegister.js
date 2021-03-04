Ext.define('Journal.controller.modules.ClassRegister', {
	extend: 'Journal.controller.Module',

	shortcutId: 'class-register',
	shortcutName: 'Klases žurnāls',
	shortcutIconCls: 'class-register-icon',
	viewClass: 'classregister', // widget name of main view

	views: [ 'modules.ClassRegisterView' ],
	models: [ 'ClassRegisterModel','ClassRegisterClassModel', 'ClassRegisterCellModel' ],
	stores: [ 'ClassRegisterStore','ClassRegisterClassStore' ],

	afterMainView: function (mView) {
		var me = this;

		var tabbar = mView.down('#cr-tabbar'),
				crcombo = mView.down('#cr-class-combo');
		if(crcombo) crcombo.select(crcombo.store.getAt(0));
		if(tabbar) tabbar.setActiveTab(tabbar.items.getAt(0));
		
		mView.items.getAt(0).getSelectionModel().on('select', me.onCellSelect, me);
	},

	tabSelector: function () {
		this.getMView().items.getAt(0).store.loadData(this.getClassData());
	},

	onCellSelect: function (cModel, rec, ri, ci, eOpts) {
		var me = this,
				cols = this.getMView().items.getAt(0).columns,
				cIndx = cols[ci+2].dataIndex,
				srec = rec.data[cIndx],
				firstlastname = rec.data.ps_firstname+' '+rec.data.ps_lastname,
				dForm = this.getMView().down('#day-mark-edit'),
				fwidth = dForm.getWidth(),
				fields = this.getMView().exFields;

		for(var i = 0;i < fields.length;i++)
			if(dForm.isAncestor(fields[i])) dForm.remove(fields[i], false);

		fields[srec[1]] = dForm.insert(5, fields[srec[1]]);
		dForm.loadRecord(new Journal.model.ClassRegisterCellModel({
			id:srec[0],
			pupil:firstlastname,
			date:cIndx.replace(new RegExp('_', 'ig'), '.'),
			theme: 'Lorem du mesum up tetum atema sepala',
			attendance: srec[1] == 1 ? true : false,
			mark: srec[1] == 0 ? (srec[2] == 1 ? true:false):(srec[2] == 0 ? '':srec[2]),
			comment: srec[3]
		}));

		dForm.show();
		//dForm.expand(true);
	},

	onCellFormCheckBoxClick: function (box, ckd) {
		var me = this,
				dForm = this.getMView().down('#day-mark-edit'),
				fields = this.getMView().exFields
				val = ckd ? 1:0;

		for(var i = 0;i < fields.length;i++)
			if(dForm.isAncestor(fields[i])) dForm.remove(fields[i], false);

		fields[val] = dForm.insert(5, fields[val]);
	},

	onCellCancelEdit: function () {
		var me = this,
				dForm = this.getMView().down('#day-mark-edit'),
				selModel = this.getMView().down('#cr-data-grid').getSelectionModel();

		//dForm.collapse(Ext.Component.DIRECTION_LEFT, true);
		dForm.hide();

		if(selModel.hasSelection() > 0) {
			selModel.deselectAll();
		}
	},

	onCellSaveEdit: function () {
		var me = this,
				dForm = this.getMView().down('#day-mark-edit'),
				dGrid = this.getMView().down('#cr-data-grid'),
				selModel = dGrid.getSelectionModel(),
				dModel,
				cols = dGrid.columns,
				cIndx = cols[selModel.getCurrentPosition().column+2].dataIndex,
				v = dForm.getValues(),
				vSave = [
					v.id,
					v.attendance ? 1:0,
					v.attendance && v.mark ? v.mark:(!v.attendance && v.mark?1:0),
					v.comment
				];

		//alert(vSave+'; '+(selModel.getCurrentPosition().column+2));

		dForm.collapse(Ext.Component.DIRECTION_RIGHT, true);
		dForm.hide();

		if(selModel.hasSelection() > 0) {
			dModel = selModel.getSelection()[0];
			dModel.set(cIndx, vSave);
			selModel.deselectAll();
		}
	},

	getClassData: function () {
		var sList = ['Zane Grike',
								 'Arturs Glaznieks',
								 'Martins Beljans',
								 'Krists Balins',
								 'Kristine Visocka',
								 'Kristine Taskane',
								 'Sandra Sarkanbarde',
								 'Liga Samohina',
								 'Elina Rutkovska',
								 'Reinis Rinmanis',
								 'Beate Paula',
								 'Agija Mezraupa',
								 'Janis Maculskis',
								 'Antra Lukina',
								 'Liene Lisenko',
								 'Sintija Liedeskrastina',
								 'Rolands Kreslins',
								 'Zanda Klavina',
								 'Agnese Jurkina',
								 'Livija Golosujeva'],
				marksGrid = [],
				names, att, mark, c = 1;

		for(var i = 0; i < sList.length;i++) {
			marksGrid[i] = [];
			names = sList[i].split(' ');
			marksGrid[i][0] = names[0];
			marksGrid[i][1] = names[1];
			for(var d = 2; d <= 16; d++) {
				marksGrid[i][d] = [];
				marksGrid[i][d][0] = c++;
				att = marksGrid[i][d][1] = Math.floor(Math.random()*11) > 3?1:0;
				mark = marksGrid[i][d][2] = Math.floor(Math.random()*(att > 0?11:2));
				if(att == 0 && mark == 0 || att == 1 && mark > 0)
					marksGrid[i][d][3] = 'Skolotāja komentārs par '+(att == 0 ? (mark == 0 ? 'neattaisnotu prombūtni':''):(mark > 0 ? 'atzīmi '+mark:''))+'.';
				else {
					marksGrid[i][d][3] = '';
				}
			}
		}
		return marksGrid;
	}
});
