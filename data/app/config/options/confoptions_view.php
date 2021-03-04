<?php
include_once(APP_PATH."/e_view.php");

class ConfOptionsGridView extends EGridView
{
	function ConfOptionsGridView(&$controller) {
		parent::GridView($controller);
	}
}

class ConfOptionsItemView extends EItemView 
{
	
}

?>