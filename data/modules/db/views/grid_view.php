<?php

include_once(MODULES_PATH.'/db/views/view.php');

class GridView extends View {
	public $Grid;

	function __construct(&$controller) {
		parent::__construct($controller);

		$this->Grid =& $this->Controller->Grid;
		$this->Queried = false;
	}
}

?>