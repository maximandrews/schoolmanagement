Ext.define('Journal.store.RecordBookChildStore', {
	extend: 'Ext.data.ArrayStore',

	model: 'Journal.model.RecordBookChildModel',

	data : [
		['1', 'Zane Griķe'],
		['2', 'Artūrs Glāznieks'],
		['3', 'Krists Bāliņš'],
		['4', 'Kristīne Visocka']
	]
});