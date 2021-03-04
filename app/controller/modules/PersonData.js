Ext.define('Journal.controller.modules.PersonData', {
	extend: 'Journal.controller.Module',

	shortcutId: 'person-data',
	shortcutName: 'Profila dati',
	shortcutIconCls: 'person-data-icon',
	viewClass: 'persondata', // widget name of main view

	views: [ 'modules.PersonDataView', 'modules.ChildPretendentsView' ],
	models: [ 'PersonReqChildModel', 'PersonAppChildModel' ],
	stores: [ 'PersonReqChildStore', 'PersonAppChildStore' ],

	personNames: ['Zane Griķe',
								'Artūrs Glāznieks',
								'Mārtiņš Beljāns',
								'Krists Bāliņš',
								'Kristīne Visocka',
								'Kristīne Taškāne',
								'Sandra Sarkanbārde',
								'Zane Griķe',
								'Artūrs Glāznieks',
								'Mārtiņš Beljāns',
								'Krists Bāliņš',
								'Kristīne Visocka',
								'Kristīne Taškāne',
								'Sandra Sarkanbārde'],


	afterMainView: function (mView) {
	var form = mView.items.getAt(0),
			tbp = mView.down('#tabpanel');
		mView.items.getAt(0).items.getAt(8).hide();
		Ext.getCmp('tabpanel').hide();
		if (mView.app.dData.userType == 2) {
			mView.items.getAt(0).items.getAt(8).show();
			if(tbp) {
				tbp.insert(1, mView.myTabs['child-to-submit']);
				tbp.items.getAt(0).store.loadData(this.getPersonData());//load data to apstiprināti bērni
				tbp.items.getAt(1).store.loadData(this.getPersonData());//load data to pieteiktie bērni
			}
			tbp.show();
		}
		form.getForm().setValues([
			{id:'ps_email', value: mView.app.dData.userLogin },
			{id:'ut_name', value: mView.app.dData.userTypeTxt },
			{id:'ps_firstname', value: mView.app.dData.userFirstName },
			{id:'ps_lastname', value: mView.app.dData.userLastName },
			{id:'ps_personcode', value: mView.app.dData.userPersonCode },
			{id:'ps_mailsms', value: mView.app.dData.userMailSms }
		]);
	},

	getPersonData: function () {
		var me = this,
				sList = me.personNames,
				count = Math.floor(Math.random()*8+1),
				personGrid = [];
		for(var i = 0; i < 5; i++){
			personGrid[i] = [];
			names = sList[i].split(' ');
			personGrid[i][0] = names[0];
			personGrid[i][1] = names[1];
			personGrid[i][2] = '00.00.00';
			personGrid[i][3] = Math.floor(Math.random()*11);
		}
		return personGrid;
	},

	addChild: function () {
		Ext.widget('childpredendents').show();
	},

	removeClickHandler: function(){
		var childGrid = Ext.getCmp('person-data-approved-child'),
			selection = childGrid.getView().getSelectionModel().getSelection();
			if (selection.length > 0) {
				childGrid.store.remove(selection);
				//this.getMView().items.getAt(0).store.sync();
			}
	},
	
	onFormSuccess: function(){
		
	}
});
