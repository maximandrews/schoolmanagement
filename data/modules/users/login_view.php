<?php
include_once(MODULES_PATH.'/db/views/item_view.php');

class LoginView extends ItemView {
	public $User;

	function __construct(&$controller) {
		parent::__construct($controller);

		$this->User=& $this->Controller->User;
	}

	function main() {
		$User =& $this->Controller->GetUser();
		// ps_email, ps_ut_id, ps_firstname, ps_lastname, ps_birthdate, ps_personcode, ps_mailsms, ps_cl_id
		return $this->Controller->LoggedIn() ? Array(
			'userName' => $this->User->GetDBField('ps_firstname').' '.$this->User->GetDBField('ps_lastname'),
			'userFirstName' => $this->User->GetDBField('ps_firstname'),
			'userLastName' => $this->User->GetDBField('ps_lastname'),
			'userPersonCode' => $this->User->GetDBField('ps_personcode'),
			'userMailSms' => $this->User->GetDBField('ps_mailsms'),
			'userId' => $this->User->GetId(),
			'userLogin' => $this->User->GetDBField('ps_email'),
			'userType' => $this->User->GetDBField('ps_ut_id'),
			'userTypeTxt' => $this->User->GetField('ps_ut_id'),
			'success' => true
		):Array(
			'success' => false,
			'errors' => Array(
				Array('id' => 'username', 'msg' => 'Ir ievadits nepareizs e-pasts vai parole'),
				Array('id' => 'password', 'msg' => 'Ir ievadits nepareizs e-pasts vai parole'),
			)
		);
	}
}

?>