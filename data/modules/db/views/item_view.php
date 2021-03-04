<?php

include_once(MODULES_PATH.'/db/views/view.php');

class ItemView extends View {
	public $Item;

	function __construct(&$controller) {
		parent::__construct($controller);

		$this->Item =& $this->Controller->Item;
	}
}

?>