<?php
global $MODULES_PATH;
include_once(MODULES_PATH."/db/dbitem.php");

class BaseUser extends DBItem {

	function BaseUser($Id, &$Owner) {
		$tmp = Array(
			'user_id' => '',
			'username' => '',
			'password' => '',
			'password_repeat' => '',
			'email' => '',
			'first_name' => '',
			'last_name' => '',
			'created' => '',
			'last_modified' => '',
		);
		$this->CreateFieldsFromHash($tmp);
		$this->id_field = 'user_id';
		$this->table_name = 'users';

		parent::DBItem($Id, $Owner);

		$this->SetRequiredFields(Array('username', 'email'));
		$this->SetDBFields(Array('username', 'email', 'first_name', 'last_name', 'created', 'last_modified'));
		//$this->DisplayQueries = 1;
		//$this->DisplayErrors = 1;
	}
/*
	function CreateField($name, $value, $required=0, $db=0, $show=0)
  {
  	switch ($name) {
  		case 'created':
				$this->Fields[$name] = new DateField($name,$value,1,1,$show);
				$this->Fields[$name]->SetFormat('d/m/Y H:i');
  			break;
  		case 'last_modified':
				$this->Fields[$name] = new DateField($name,$value,1,1,$show);
				$this->Fields[$name]->SetFormat('d/m/Y H:i');
  			break;
  		case 'password':
  			$this->Fields[$name] = new PasswordField($name,$value,0,1,$show);
  			break;
  		default:
  			parent::CreateField($name, $value, 0, 0);
  	}
	}
*/
	function SetRepass() {
		$this->Fields['password']->SetRepeat( $this->GetFieldRef('password_repeat') );
	}

	function Update() {
		if ($this->GetField('password') == '')
			$this->Fields['password']->SetDb(0);
		else
			$this->Fields['password']->SetRequired(1);

		$this->Fields['last_modified']->SetValue('');
		return parent::Update();
	}

	function Login($username, $password) {
		$conn = GetADODbConnection();
		$query = sprintf("SELECT %s FROM ".(defined('TLB_PREFIX')?TLB_PREFIX:'') ."%s WHERE username = %s AND password = %s",
										 $this->id_field,
										 $this->table_name,
										 $conn->qstr($username, 0),
										 $conn->qstr($password, 0));

		if ($this->DisplayQueries) echo "User-&gt;Login: $query<br>";

		$res = $conn->Execute($query);
		if ($res === false) {
      if ($this->DisplayQueries)
    		echo "Error executing statement: ".$conn->ErrorMsg()."<br>";
    }
    if ($res && !$res->EOF) {
    	$user_id = $res->fields[$this->id_field];
    	return $user_id;
    }
    else
    	return false;
	}
	
	function CheckPermissions($params) {
		return true;
	}
	
	function CheckPermissionsRedirect($params=Array()) {
		if(!$this->CheckPermissions($params))
			$this->Owner->Redirect('login');
	}

}

class UsersList extends FilteredDBList {
	function &NewItem() {
		$new = new User(NULL, $this->Owner);
		return $new;
	}
}