<?php
include_once(APP_PATH.'/e_controller.php');
include_once(APP_PATH.'/schedule/schedule_list.php');
include_once(APP_PATH.'/schedule/schedule_view.php');

class ScheduleGridController extends EGridController {
	public $errors = Array();
	function CreateView() {
		$this->AddView(new ScheduleGridView($this));
	}

	function CreateModel() {
		$this->Grid = new ScheduleList('', 0, $this);
	}
}

class ScheduleItemController extends EItemController {
	function CreateView() {
		$this->AddView(new ScheduleItemView($this));
	}
	
	function CreateModel() {
		$this->Item = new Schedule(null, $this);
	}
}
?>