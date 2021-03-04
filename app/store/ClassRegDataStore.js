Ext.define('Journal.store.ClassRegDataStore', {
	extend: 'Ext.data.ArrayStore',

	model: 'Journal.model.ClassRegDataModel',

	data:  [
		['Zane','Griķe','0000.00.00', 'Kristīne Taškāne'],
		['Artūrs','Glāznieks','0000.00.00', 'Zane Griķe'],
		['Mārtiņš','Beljāns','0000.00.00', 'Arvīds Cicurins'],
		['Krists','Bāliņš','0000.00.00', 'Ieva Rozentāle'],
		['Kristīne','Taškāne','0000.00.00', 'Marina Murnika']
	]

});