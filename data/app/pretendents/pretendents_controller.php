<?php
include_once(APP_PATH.'/e_controller.php');
include_once(APP_PATH.'/pretendents/pretendents_list.php');
include_once(APP_PATH.'/pretendents/pretendents_view.php');

class PretendentsGridController extends EGridController {
	function CreateView() {
		$this->AddView(new PretendentsGridView($this));
	}

	function CreateModel() {
		$this->Grid = new PretendentsList('', 0, $this);
	}
}


class PretendentsItemController extends EItemController {
	function CreateView() {
		$this->AddView(new PretendentsItemView($this));
	}
	
	function CreateModel() {
		$this->Item = new Pretendent(null, $this);
	}
}
?>