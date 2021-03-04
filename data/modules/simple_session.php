<?php
include_once(MODULES_PATH.'/db/dbitem.php');
include_once(MODULES_PATH.'/ses_dbitem.php');
include_once(MODULES_PATH.'/config_list.php');

/**
* Session handling class
*
* SimpleSession is used by Kernel3 instead of standard PHP session handling mechanism.
* The class handles storing and recalling data from session. The data is stored in the database (tables sessions & session_data)
* session_data table allows to have unlimited number of session variables tied to each record in the sessions table. Session identification
* is handled by using SID - Session ID, which is stored as cookie at user side and passed in the GET/POST requests
* (we are about to change this so that if the user has cookies on we would not pass SID in every GET/POST
*
* @package kernel3
*/

class SimpleSession extends DBItem {
	public $SessionData = Array();
	public $Owner = NULL;
	public $Config;
	public $Lang;
	public $CookiePath;
	public $UserClass = 'BaseUser';
	public $CheckIP = true;
	public $Logging = false;
	public $LogFile;
	public $log_h;
	public $CookiesEnabled;
	
	function __construct($Id, &$Owner) {
		$this->App =& KernelApplication::Instance();
		$this->Conn =& $this->App->GetADODBConnection();
		$this->table_name = 'sessions';
		$this->id_field = 'ss_id';
		$this->SelectSQL = 'SELECT ss_id, ss_sid, ss_ps_id, ss_expire, ss_created, ss_modified FROM '.(defined('TLB_PREFIX')?TLB_PREFIX:'') .'sessions WHERE %s';
		$this->DeleteSQL = 'DELETE FROM '.(defined('TLB_PREFIX')?TLB_PREFIX:'') .'sessions WHERE %s';
		$this->DisplayQueries = 0;
		$this->DisplayErrors = 0;
		$this->Owner =& $Owner;
		$this->SetDBField('ss_modified', time());
		$this->SetDBField('ss_created', time());
		$this->ClearExpired();
	}

	function LogMsg($msg) {
		if (!$this->Logging) return;
		$str = 'ss_ps_id';
		global $HTTP_SERVER_VARS;
		$ip = $HTTP_SERVER_VARS['REMOTE_ADDR'];
		fwrite($this->log_h, date('d/M/y H:i:s').' '.$ip.' '.$str.': ['.$this->GetField($str).'] '.$msg."\n");
	}

	function GetProperty($name) {
		return $this->App->HTTPQuery->Get($name);
	}
	
	function SetProperty($name, $val) {
		$this->App->HTTPQuery->Set($name, $val);
	}
	
	function CookieName() {
		if (defined('HIDE_SID') || defined('SEPARATE_ADMIN_COOKIE')) {
			if (defined('ADMIN') || strlen($this->App->RecallVar('admin_mode')) > 0) 
				return 'admin_sid';
			else
				return 'sid';
		}else
			return 'sid';
	}

	function Init() {
		if (!isset($this->CookiePath))
			$this->CookiePath = BASE_PATH;

		$this->CheckIfCookiesAreOn();

		if ($this->Logging)
			$this->log_h = fopen ($this->LogFile, "a");

		$this->ForceChangeSid();

		//print_pre($this->App->HTTPQuery->_Params);

		if ($this->CookiesEnabled && defined('HIDE_SID'))
			$sid = $this->App->HTTPQuery->Cookie[$this->CookieName()];
		else
			$sid = $this->App->GetVar('sid');

		if (isset($sid)) {
			//syslog(LOG_DEBUG, "Got sid from cookie: $sid");
			//$this->id = $sid;
			if ($this->Load($sid, 'ss_sid')) {
				//syslog(LOG_DEBUG, "loaded user id: ".$this->GetField('ss_ps_id')." refreshing session");
				$this->LoadVars();
				if ($this->CheckIP) {
					//If IP do not match AND additional cookie do not match - recreate session
					if (!$this->DoCheckIP() && !$this->CheckCookie()) {
						$this->SetSession();
						//echo "<!-- Session has been recreated !!! -->\n";
					}	else {
						//echo "ses ok<br>";
						$this->Refresh();	
						//echo "<!-- IP Check sucssessful!!! -->\n";
					}
				}	else
					$this->Refresh();
				
			} else {
				if (!isset($HTTP_POST_VARS) || !isset($HTTP_POST_VARS["submit"]) || $HTTP_POST_VARS["submit"] != "Login") {
					//syslog(LOG_DEBUG, "cannot load $sid from database - setting session");
				}
				$this->SetField('ss_ps_id', 0);
				$this->SetSession();
			}
		}else{
			//syslog(LOG_DEBUG, "No sid in cookie - setting session");
			$this->SetField('ss_ps_id', 0);
			$this->SetSession();
		}
		$this->LogProperties();	
	}

	function CheckReferer() {
		$reg = '#^'.preg_quote((defined('PROTOCOL')?PROTOCOL:'').(defined('SERVER_NAME')?SERVER_NAME:'').$this->CookiePath).'#';
		$referer = isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'';
		return preg_match($reg, $referer);
	}

	function CheckIfCookiesAreOn() {
		$cookies_on = isset($this->App->HTTPQuery->Cookie['CookiesOn'])?$this->App->HTTPQuery->Cookie['CookiesOn']:'';
		if (!$cookies_on) { 
			//If referer is our server, but we don't have our cookies_on, it's definetly off
			if ($this->CheckReferer())
				$this->CookiesEnabled = false;
			else {
				//Otherwise we still suppose cookies are on, because may be it's the first time user visits the site
				//So we send cookies on to get it next time (when referal will tell us if they are realy off
				$this->CookiesEnabled = true;
				setcookie(
					'CookiesOn',
					1,
					time()+31104000, //one year should be enough
					$this->CookiePath,
					SERVER_NAME,
					0
				);
			}
		}else
			$this->CookiesEnabled = true;

		if (strlen($this->App->RecallVar('admin_mode')) > 0 || defined('ADMIN'))
			$this->AdminMode = true;
		else
			$this->AdminMode = false;

		return $this->CookiesEnabled;
	}

	function CheckCookie() {
		if ($this->GetProperty('addsid_cookie') == $this->RecallVar('addsid_cookie')) {
			//if cookie matched - storing new IP
			$this->StoreVar('creation_ip', getenv("REMOTE_ADDR"));
			$this->StoreVar('creation_sub_ip', getenv("HTTP_X_FORWARDED_FOR"));

			return true;
		}else
			return false;
	}

	function DoCheckIP() {
		return $this->RecallVar('creation_ip') == getenv("REMOTE_ADDR");
	}

	function LogProperties() {
		global $HTTP_SERVER_VARS;
		$msg = $HTTP_SERVER_VARS['REQUEST_METHOD'].' ';
		foreach ($this->Property as $name => $val) {
			if ($name == 'ses_timeout') continue;
			if (is_array($val)) {
				$a_val = join('; ', $val);
				$msg .= "$name=[$a_val] ";
			}	else
				$msg .= "$name=[$val] ";
		}
		$this->LogMsg($msg);
	}

	function ForceChangeSid() {
		//abstract
	}

	function GetParam($name) {
		global $$name;
		if (isset($$name)) 
			return $$name;
		else
			return false;
	}

	function CreateField($name, $value, $required=0, $db=1, $type='default') {
  	switch ($name) {
  		/*case 'ss_sid':
    		$this->Fields[$name] = new DigitsField($name, $value, 1, 1);
    		break;*/
    	case 'ss_expire':
    		//$this->Fields[$name] = new DateField($name, $value, 0, 1);
    		if ($value == '') $value = time();
    		parent::CreateField($name, $value, $required, $db);
    		break;
    	/*case 'ss_ps_id':
    		$this->Fields[$name] = new DigitsField($name, $value, 1, 1);
    		break;*/
    	default:
    		parent::CreateField($name, $value, $required, $db);
   	}
  }

	function RandomSID() {
  	list($usec, $sec) = explode(" ",microtime()); 

		$sid_part_1 = substr($usec, 4, 4);
		$sid_part_2 = mt_rand(1,9);
		$sid_part_3 = substr($sec, 6, 4);
		$digit_one = substr($sid_part_1, 0, 1);
		if ($digit_one == 0) {
			$digit_one = mt_rand(1,9);
			$sid_part_1 = preg_replace("/^0/s",'',$sid_part_1);
			$sid_part_1=$digit_one.$sid_part_1;
		}
		return $sid_part_1.$sid_part_2.$sid_part_3;
  }
  
	function GenSid() {
  	global $sid;

		$sid = $this->RandomSID();	
		if ($this->Load($sid, 'ss_sid')) 
			$this->GenSid();
		else {
	  	$this->SetField('ss_sid', $sid);
	  	//$this->id = $sid;
		}
  	//syslog(LOG_DEBUG, "Genereated sid $sid");
  }

	function SetSessionCookie() {
  	global $HTTP_SERVER_VARS;
  	//echo "Self is ".$_SERVER['PHP_SELF']."<br>";
  	//echo "dirname is ".dirname($_SERVER['PHP_SELF'])."<br>";
  	//echo "Setting cookie<br>";
		if ($this->GetProperty('CookiesOn')) {
			//echo "Cookies on<br>";
			//echo "Setting cookie ".$this->GetField('sid')."<br>";
			setcookie(
				$this->CookieName(), //name
				$this->GetField('ss_sid'), //value
				time()+$this->GetProperty('ses_timeout'), //expire
				$this->CookiePath, //full path
				SERVER_NAME, //domain
				0 //secure
			);
			//echo "set cookie to path: ".$this->CookiePath.'<br>';
		}
  }

  function SetAdditionalCookie() {
  	$add_sid = $this->RandomSID();
  	$add_sid = md5($add_sid);

  	$this->StoreVar('addsid_cookie', $add_sid);

  	//echo "setting additional cookie $add_sid<br>";

  	if ($this->GetProperty('CookiesOn')) {
  		//echo "cookies are On<br>";
			if (!isset($this->CookiePath)) 
				$this->CookiePath = $HTTP_SERVER_VARS['PHP_SELF'];
			setcookie(
				"addsid_cookie", //name
				$add_sid, //value
				time()+$this->GetProperty('ses_timeout'), //expire
				$this->CookiePath, //full path
				SERVER_NAME, //domain
				0 //secure
			);
		}
  }

	function SetInsertID() {
  	//we don't need to set InsertID! - Doing nothing
  }

	function SetSession() {
		$this->GenSid();
		$this->SetField('ss_ps_id',0);
		$this->SetField('ss_expire', time());

		$this->SetSessionCookie();

		$this->Create();
	}

	function Refresh() {
		if (($this->GetDBField('ss_expire') + $this->GetProperty('ses_timeout') > time())) {
			$this->SetField('ss_expire', time());
			$this->SetDBField('ss_modified', time());
		}else{
			//echo "expired: setting ss_ps_id to 0<br>";
			$this->DestroySession();
			$this->SetSession();
			//$this->SetField('ss_ps_id', 0);
		}

		$this->SetSessionCookie();
		$this->Update();
	}

	function LoggedIn() {
		return $this->GetField('ss_ps_id') > 0;
	}

	//abstract - do anything what is needed after succsessful login
	function AfterLogin() {

	}
	
	// Session Data functions
	function LoadVars() {
		$adodbConnection = GetADODBConnection();

		$query = sprintf("SELECT * FROM ".(defined('TLB_PREFIX')?TLB_PREFIX:'') ."session_data WHERE sd_ss_id = %s", $this->id);

		$result = $adodbConnection->Execute($query);

    if ($result === false)
    	return false;

    while ($row = $result->FetchRow()) {
			unset($row['sd_ss_id']);
			$this->SessionData[$row['sd_var_name']] = $row;
    }
	}

	function &GetVarAsObject($name) {
		$tmp = new SessionData(NULL, NULL, $this->Owner);
    $tmp->SetField('sd_ss_id', $this->id);

    $data = $this->SessionData[$name];

		$tmp->SetField('sd_var_name', $data['sd_var_name']);
		$tmp->SetField('sd_var_data', $data['sd_var_data']);

		return $tmp;
	}

	function StoreVarDefault($name, $value) {
		$tmp = $this->RecallVar($name);
		if ($tmp === false || $tmp == '')
			$this->StoreVar($name, $value);
	}

	function StoreVar($name, $value) {
		if ($this->RecallVar($name) === $value) return;
		if (isset($this->SessionData[$name])) {
			$tmp =& $this->GetVarAsObject($name);
			$tmp->SetField('sd_var_data', $value);
			$tmp->Update();
			$this->SessionData[$name] = $tmp->GetFieldsArray();
		}else{
			$tmp = new SessionData(NULL, NULL, $this->Owner);
			$tmp->SetField('sd_ss_id', $this->id);
			$tmp->SetField('sd_var_name', $name);
			$tmp->SetField('sd_var_data', $value);
			$tmp->Create();
			$this->SessionData[$name] = $tmp->GetFieldsArray();
		}
		unset($tmp);
	}

	function RecallVar($name, $default='') {
		if ($default !== '') $this->StoreVarDefault($name, $default);
		if (isset($this->SessionData[$name])) {
			$tmp =& $this->GetVarAsObject($name);
			return $tmp->GetField('sd_var_data');
		}else
			return false;
	}

	function RemoveVar($name) {
		if (isset($this->SessionData[$name])) {
			$tmp =& $this->GetVarAsObject($name);
			$tmp->Delete();
			unset($this->SessionData[$name]);
		}
	}

	function GetSessionData() {
		$tmp = Array();
		foreach ($this->SessionData AS $name => $val) {
			$tmp[$name] = $this->SessionData[$name]['sd_var_data'];
		}
		// print_pre($tmp);
		return $tmp;
	}

	// This routine checks passed browser parameters and set corresponding session data to this or default
	function GetLinkedProperty($property_name, $ses_var_name, $def_value=NULL, $no_value=NULL) {
		//storing default value for session variable if default value is given
		if (isset($def_value)) $this->StoreVarDefault($ses_var_name, $def_value);

		//trying to get value passed from browser (as query string/cookie/post...)
		$this->SetLinkedProperty($property_name, $ses_var_name, $no_value);

		//finally recalling value - 
		//this will get value in any case (default, from browser or stored in session earlier)
		$prop_value = $this->RecallVar($ses_var_name);

		return $prop_value;
	}

	function SetLinkedProperty($property_name, $ses_var_name, $no_value=NULL) {
		$prop_value = $this->GetProperty($property_name);
		if (isset($prop_value))
			$this->StoreVar($ses_var_name, $prop_value); //storing this value if present
		elseif (isset($no_value))
			$this->StoreVar($ses_var_name, $no_value); //storing the default 'no_value' value
	}

	function Delete() {
		parent::Delete();
		$adodbConnection = GetADODBConnection();
		$query = sprintf("DELETE FROM ".(defined('TLB_PREFIX')?TLB_PREFIX:'') ."session_data WHERE sd_ss_id = %s", $this->id);
		$adodbConnection->Execute($query);
	}

	function DestroySession() {
		$this->Delete();
	}
	
	function &GetUser() {
		$user_class = $this->UserClass;
		$user = new $user_class($this->GetField('ss_ps_id'), $this->Owner);
		return $user;
	}

	function ClearExpired() {
		$conn =& $this->App->GetADODBConnection();
		$query = sprintf("SELECT ss_id FROM ".(defined('TLB_PREFIX')?TLB_PREFIX:'') ."sessions WHERE ss_expire + %s > %s",
											3600,
											time());
		$array = $conn->GetCol($query);
		if(is_array($array))
			$ids = join(',', $array);

		if(isset($ids)) {
			$query = "DELETE FROM ".(defined('TLB_PREFIX')?TLB_PREFIX:'') ."sessions WHERE ss_id NOT IN ($ids)";
			$conn->Execute($query);
			$query = "DELETE FROM ".(defined('TLB_PREFIX')?TLB_PREFIX:'') ."session_data WHERE sd_ss_id NOT IN ($ids)";
			$conn->Execute($query);
		}
	}
}
?>