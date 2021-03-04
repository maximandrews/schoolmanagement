<?php
include_once(MODULES_PATH."/db/controllers/grid_controller.php");
include_once(MODULES_PATH."/db/controllers/item_controller.php");
include_once(APP_PATH."/config/fieldopt/fieldopt_list.php");
include_once(APP_PATH."/config/fieldopt/fieldopt_view.php");
//include_once(APP_PATH."/e_view.php");

class FieldOptGridController extends GridController {
	function FieldOptGridController($params) {
		parent::GridController($params);
	}
	
	function CreateView() {
		$this->AddView(new FieldOptGridView($this));
	}
}


class FieldOptItemController extends ItemController {
	function FieldOptItemController($params) {
		parent::ItemController($params);
	}
	
	function CreateView() {
		$this->AddView(new FieldOptItemView($this));
	}

}
?>