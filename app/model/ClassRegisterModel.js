Ext.define('Journal.model.ClassRegisterModel', {
	extend: 'Ext.data.Model',
	fields: [
		'ps_firstname',
		'ps_lastname',
		{ name: '2012_03_01', sortType: function (val){ return val[1] == 0 ? val[2]*-1-1:val[2]; }},
		{ name: '2012_03_02', sortType: function (val){ return val[1] == 0 ? val[2]*-1-1:val[2]; }},
		{ name: '2012_03_03', sortType: function (val){ return val[1] == 0 ? val[2]*-1-1:val[2]; }},
		{ name: '2012_03_04', sortType: function (val){ return val[1] == 0 ? val[2]*-1-1:val[2]; }},
		{ name: '2012_03_05', sortType: function (val){ return val[1] == 0 ? val[2]*-1-1:val[2]; }},
		{ name: '2012_03_06', sortType: function (val){ return val[1] == 0 ? val[2]*-1-1:val[2]; }},
		{ name: '2012_03_07', sortType: function (val){ return val[1] == 0 ? val[2]*-1-1:val[2]; }},
		{ name: '2012_03_08', sortType: function (val){ return val[1] == 0 ? val[2]*-1-1:val[2]; }},
		{ name: '2012_03_09', sortType: function (val){ return val[1] == 0 ? val[2]*-1-1:val[2]; }},
		{ name: '2012_03_10', sortType: function (val){ return val[1] == 0 ? val[2]*-1-1:val[2]; }},
		{ name: '2012_03_11', sortType: function (val){ return val[1] == 0 ? val[2]*-1-1:val[2]; }},
		{ name: '2012_03_12', sortType: function (val){ return val[1] == 0 ? val[2]*-1-1:val[2]; }},
		{ name: '2012_03_13', sortType: function (val){ return val[1] == 0 ? val[2]*-1-1:val[2]; }},
		{ name: '2012_03_14', sortType: function (val){ return val[1] == 0 ? val[2]*-1-1:val[2]; }},
		{ name: '2012_03_15', sortType: function (val){ return val[1] == 0 ? val[2]*-1-1:val[2]; }}
	]
});
