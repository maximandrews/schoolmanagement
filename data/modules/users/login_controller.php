<?php
if (defined('USERS_LIST'))	require_once(APP_PATH.USERS_LIST);
include_once(MODULES_PATH.'/users/login_view.php');

class LoginController extends Controller {
	public $User;

	function InitModel() {
		parent::InitModel();
		$object = USER_MODEL;
		$this->User = new $object(null,$this);
	}

	function &GetUser() {
		if(!$this->User->loaded)
			$this->User->Load($this->App->Session->GetField('ss_ps_id'));
		return $this->User;
	}

	function LoggedIn() {
		return $this->App->Session->GetDBField('ss_ps_id') > 0;
	}

	function CheckPermissions($params=Array()) {
		return $this->User->CheckPermissions($params);
	}

	function CheckPermissionsRedirect($params=Array()) {
		$this->User->CheckPermissionsRedirect($params);
	}

	function CreateView() {
		$this->AddView(new LoginView($this));
	}

	function actLogin() {
		return $this->User->Login($this->App->GetVar('username'),$this->App->GetVar('password'));
	}
	
	function actLogout() {
		$this->App->DestroySession();
		$this->User->Logout();
	}
	
	function actClean_Cookies() {
		$path = $_SERVER["PHP_SELF"];
		
		//echo "dir: $path<br>";
		
		$elems = explode('/', $path);
		//print_pre($elems);
		
		$cookies = $_COOKIE;
		$cookies['admin_sid'] = 'x';
		$cookies['sid'] = 'x';

		$path = '';		
		foreach ($elems as $path_item) {
			foreach ($cookies as $name => $value) {
				//echo SERVER_NAME."$path [$name]<br>";
				setcookie(
					$name, //name
					'', //value
					0, //expire
					$path, //full path
					SERVER_NAME, //domain
					0 //secure
				);
			}
			$path .= ($path != '/' ? '/' : '').$path_item;
		}
	}
}

?>