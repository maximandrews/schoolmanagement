Ext.define('Journal.controller.modules.RecordBook', {
	extend: 'Journal.controller.Module',

	shortcutId: 'record-book',
	shortcutName: 'Dienasgrāmata',
	shortcutIconCls: 'record-book-icon',
	viewClass: 'recordbook',

	views: [ 'modules.RecordBookView' ],
	models: [ 'RecordBookChildModel', 'RecordBookModel', 'MarkSummaryModel' ],
	stores: [ 'RecordBookChildStore', 'RecordBookStore', 'MarkSummaryStore' ],

	weekBtnPressedState: true,
	coursesNames: ['Latviešu valoda un literatūra',
								 'Matemātika',
								 'Vēsture',
								 'Pirmā svešvaloda (angļu valoda)',
								 'Otrā svešvaloda (vācu vai krievu valoda)',
								 'Sports',
								 'Informātika',
								 'Biznesa ekonomiskie pamati',
								 'Ģeogrāfija',
								 'Fizika',
								 'Ķīmija',
								 'Bioloģija',
								 'Veselības mācība',
								 'Mūzika',
								 'Kultūras vēsture',
								 'Programmēšanas pamati',
								 'Psiholoģija',
								 'Politika un tiesības',
								 'Tehniskā grafika',
								 'Angļu valoda',
								 'Latviešu literatūra',
								 'Krievu valoda',
								 'Fizika',
								 'Ķīmija',
								 'Matemātika',
								 'Latviešu valoda',
								 'Avotu mācība/Mājturība',
								 'Sports',
								 'Franču valoda',
								 'Geogrāfija',
								 'Bioloģija',
								 'Krievu literatūra',
								 'Vizuāla māksla',
								 'Mūzika',
								 'Vēsture',
								 'Informātika'],
	recordBookDates: new Date(),

	init: function () {
		var me = this,
				tmpDay = me.recordBookDates.getDay();
		me.callParent();

		if(tmpDay == 0) me.recordBookDates.setDate(me.recordBookDates.getDate()-2);
		if(tmpDay == 6) me.recordBookDates.setDate(me.recordBookDates.getDate()-1);
	},

	afterMainView: function (mView) {
		var crcombo = mView.down('#combo-pupils'),
				crcombo1 = mView.down('#combo-pupils1');

		if(crcombo) crcombo.select(crcombo.store.getAt(0));
		if(crcombo1) crcombo1.select(crcombo1.store.getAt(0));
	},

	// View handlers  --------------------------- START
	datePickerHandler: function(dp, date){
		var me = this,
				rbd = me.recordBookDates;

		rbd.setYear(date.getFullYear());
		rbd.setMonth(date.getMonth(), date.getDate());
		me.rebuildRB();
	},

	weekButtonHandler: function (btn, e){
		var me = this;

		if(!me.weekBtnPressedState) {
			me.rebuildRB();
			me.weekBtnPressedState = true;
		}
	},

	dayButtonHandler: function (btn, e) {
		var me = this;

		if(me.weekBtnPressedState) {
			me.app.changeDate(me.recordBookDates, 1-me.recordBookDates.getDay());
			me.rebuildRB();
			me.weekBtnPressedState = false;
		}
	},

	prevButtonHandler: function() {
		var me = this,
				weekButton = me.getMView().down('#rb-week-button'),
				rbd = me.recordBookDates,
				dt = rbd.getDate(); // 2012.03.02 -> 2

		if(weekButton && weekButton.pressed){
			me.app.changeDate(rbd, -7);
			//rbd.setDate(dt - 7); // 2 - 7 = -5 -> 24
		}else if(weekButton && !weekButton.pressed){
			me.app.changeDate(rbd, (rbd.getDay()==1 ? -3:-1));
			//rbd.setDate(dt - (rbd.getDay()==5 ? 3:1));
		}
		me.rebuildRB();
	},

	nextButtonHandler: function() {
		var me = this,
				weekButton = me.getMView().down('#rb-week-button'),
				rbd = me.recordBookDates,
				date = rbd.getDate();

		if(weekButton && weekButton.pressed){
			me.app.changeDate(rbd, 7);
			//rbd.setDate(date + 7);
		}else if(weekButton && !weekButton.pressed){
			me.app.changeDate(rbd, (rbd.getDay()==5 ? 3:1));
			//rbd.setDate(date + (rbd.getDay()==5 ? 3:1));
		}
		me.rebuildRB();
	},

	changeChild: function () {
		this.getMView().items.getAt(0).items.getAt(1).items.getAt(0).store.loadData(this.getMarksSummary());
	},
	// View handlers  --------------------------- END

  getGridData: function () {
		var me = this,
				cList = me.coursesNames,
				recordGrid = [],
				tDate = me.recordBookDates,
				cwDay = tDate.getDay(),
				cDate = tDate.getDate(),
				shortBreak=10,
				longBreak=20,
				lesson=45,
				dStart = 1,
				dEnd = 6,
				weekButt = me.getMView().down('#rb-week-button');

		cwDay = cwDay == 0 ? 7:cwDay;
		if(weekButt && !weekButt.pressed) {
			dStart = cwDay;
			dEnd = cwDay + 1;
		}
		var goTo = -(cwDay-dStart);
		if(goTo != 0) me.app.changeDate(tDate, goTo);
		for(var i = dStart; i < dEnd ;i++) {
			//tDate.setDate(cDate-(cwDay-i));
			var date = tDate.getDate(),
					month = tDate.getMonth()+1;

			if(month < 10) { month = '0'+month; }
			if(date < 10) { date = '0'+date; }

			num = Math.floor(4+(8-4)*Math.random());
			tDate.setHours(8);
			tDate.setMinutes(0);

			for (var d = 0; d < num; d++) {
				var hour = tDate.getHours(),
						minutes = tDate.getMinutes();
				tDate.setMinutes(minutes+lesson);
				var endHours = tDate.getHours(),
						endMinutes = tDate.getMinutes();
				hour = (hour < 10 ? '0':'')+hour;
				minutes = (minutes < 10 ? '0':'')+minutes;

				var c = recordGrid.length;
				recordGrid[c] = [];
							recordGrid[c][0] = tDate.getFullYear()+'.'+month+'.'+date;
				recordGrid[c][1] = hour+':'+minutes+'-'+(endHours < 10 ? '0'+endHours:endHours)+':'+(endMinutes < 10 ? '0'+endMinutes:endMinutes);
							recordGrid[c][2] = cList[Math.floor(Math.random()*cList.length)];
				recordGrid[c][3] = '';
							recordGrid[c][4] = '';
				recordGrid[c][5] = Math.floor(Math.random()*11);
				if(recordGrid[c][5] == 0) recordGrid[c][5] = '';
				recordGrid[c][6] = '';

				tDate.setMinutes(tDate.getMinutes()+(d%4 == 0 ? longBreak:shortBreak));
			}
			if(i+1 < dEnd) me.app.changeDate(tDate, 1);
		}
		return recordGrid;
	},

	getMarksSummary: function(){
		var me = this,
				sList = me.coursesNames,
				marksGrid = [];

		for(var i = 0;i < sList.length;i++) {
			marksGrid[i] = [];
			marksGrid[i][0] = sList[i];
			for(var d = 1; d < 16; d++) {
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
		for(var i = 0;i < marksGrid.length; i++) {
			var count = 0,
				sum = 0,
				txtMarks = '';
			for(var j = 1;j < marksGrid[i].length; j++){
				if(marksGrid[i][j][1] == 1 && marksGrid[i][j][2] > 0) {
					sum += marksGrid[i][j][2];
					//txtMarks += (txtMarks.length ? ' + ':'')+marksGrid[i][j][2];
					count++;
				}
				//if(i == 0 && count > 0) alert(txtMarks+'\n'+sum+' / '+count+' = '+(sum/count));
			}
			marksGrid[i][marksGrid[i].length] = count > 0 ? sum / count:'';
			//if(i == 0) alert(marksGrid[i][marksGrid[i].length-1]);
		}
		return marksGrid;
	},

	rebuildRB: function () {
		var me = this,
				week = me.getMView().down('#rb-week-button'),
				interval = me.getMView().down('#rb-week-interval'),
				intText,
				rbd = me.recordBookDates,
				dateMenu = me.getMView().dateMenu;

		if(week && interval) {
			if(week.pressed) {
				var cDate = rbd.getDate(), //2012.03.02 -> 2
					day = rbd.getDay();//5
					day = day == 0 ? 7:day;
				me.app.changeDate(rbd, -(day-1));
				//rbd.setDate(cDate-(day-1));//2-(5-1) = -2 -> 27
				var month1 = rbd.getMonth()+1,//2012.02.27 -> 2
					year1 = rbd.getFullYear(),
					date1 = rbd.getDate();//2012.02.27 -> 27 (old :2-(5-5) = 2, rbd -> 2012.02.27 -> 2 -> 2012.02.02)
				//alert(dateMenu);
				dateMenu.picker.setValue(rbd);
				cDate = rbd.getDate(); //2012.02.27 -> 27
				day = rbd.getDay();//1
				day = day == 0 ? 7:day;
				me.app.changeDate(rbd, -(day-5));
				//rbd.setDate(cDate-(day-5));// 27-(1-5) = 31 -> 2
				var month2 = rbd.getMonth()+1,
					year2 = rbd.getFullYear(),
					date2 = rbd.getDate();
				intText = year1+'.'+(month1 < 10 ? '0':'')+month1+'.'+(date1 < 10 ? '0':'')+date1+' - '+year2+'.'+(month2 < 10 ? '0':'')+month2+'.'+(date2 < 10 ? '0':'')+date2
			}else{
				var month1 = rbd.getMonth()+1,
					date1 = rbd.getDate();
				dateMenu.picker.setValue(rbd);
				intText = rbd.getFullYear()+'.'+(month1 < 10 ? '0':'')+month1+'.'+(date1 < 10 ? '0':'')+date1;
			}
			interval.setText(intText);
		}
		this.getMView().items.getAt(0).items.getAt(0).items.getAt(0).store.loadData(me.getGridData());
	}
});
