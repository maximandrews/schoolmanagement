<?php
include_once(APP_PATH."/e_controller.php");
include_once(APP_PATH."/config/options/confoptions_list.php");
include_once(APP_PATH."/config/options/confoptions_view.php");
//include_once(APP_PATH."/e_view.php");

class ConfOptionsGridController extends EGridController {
	function ConfOptionsGridController($params) {
		parent::GridController($params);
	}
	
	function CreateView() {
		$this->AddView(new ConfOptionsGridView($this));
	}
}


class ConfOptionsItemController extends EItemController {
	function ConfOptionsItemController($params) {
		parent::ItemController($params);
	}
	
	function CreateView() {
		$this->AddView(new ConfOptionsItemView($this));
	}

}
?>