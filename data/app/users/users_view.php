<?php
include_once(APP_PATH.'/e_view.php');

class UsersGridView extends EGridView {
}

class UsersItemView extends EItemView {

	function loggedUserModules() {
		$loginctrl =& $this->App->GetLoginController();
		$user =& $loginctrl->GetUser();

		return Array('modules' => $user->AllowedModules());
	}
}

?>