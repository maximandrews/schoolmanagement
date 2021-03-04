<?php
include_once(MODULES_PATH.'/db/control_factory.php');
include_once(MODULES_PATH.'/http_query.php');
include_once(MODULES_PATH.'/utility/utilities.php');

/**
* Basic class for Kernel3-based Application
*
* This class is a Facade for any other class which needs to deal with Kernel3 framework.<br>
* The class incapsulates the main run-cycle of the script, provide access to all other objects in the framework.<br>
* <br>
* The class is a singleton, which means that there could be only one instance of KernelApplication in the script.<br>
* This could be guranteed by NOT calling the class constuctor directly, but rather calling KernelApplication::Instance() method, 
* which returns an instance of the application. The method gurantees that it will return exactly the same instance for any call.<br>
* See singleton pattern by GOF.
* @package kernel3
*/

class KernelApplication {
	/**
	* Holds internal Session-handling object
	* @access private
	* @var SimpleSession
	*/
	var $Session;
	/**
	* Holds parser output buffer
	* @access private
	* @var string
	*/
	var $OUT;
	var $Processors;
	var $DocRoot;
	var $BasePath;
	var $ModulesPath;
	var $Server;
	/**
	* The main Factory used to create different classes of Kernel-Based Application
	* @access private
	* @var ClassesFactory
	*/
	var $Factory;
	var $Objects = Array();
	/**
	* Holds internal Utilites object
	* @access private
	* @var Utilites
	*/
	var $Utils;
	/**
	* Holds internal HTTPQuery object - for getting script GET/POST/COOKIE parameters
	* @access private
	* @var HTTPQuery
	*/
	var $HTTPQuery;

	/**
	* Constucts KernelApplication - constructor is PRIVATE
	*
	* The constuructor of KernelApplication should NOT be called directly
	* To create KernelApplication, call its Instance() method
	* @see KerenelApplication::Instance
	* @access private
	*/
	function KernelApplication() {
		global $doc_root, $base_path, $MODULES_PATH, $protocol, $server;
		$this->DocRoot = $doc_root;
		$this->BasePath =  $base_path;
		$this->ModulesPath = $MODULES_PATH;
		$this->Protocol = $protocol;
		$this->Server = $server;
	}

	/**
	* Returns KernelApplication instance anywhere in the script.
 	*
 	* This method should be used to get single KernelApplication object instance anywhere in the 
 	* Kernel-based application. The method is guranteed to return the SAME instance of KernelApplication.
 	* Anywhere in the script you could write: 
 	* <code>
 	*		$application =& KernelApplication::Instance();
 	* </code>
 	* or in an object:
 	* <code>
 	*		$this->App =& KernelApplication::Instance();
 	* </code>
 	* to get the instance of KernelApplication. Not that we call the Instance method as STATIC - directly from the class.
 	* @static
 	* @access public
	* @return KernelApplication
	*/
	static function &Instance() {
		static $instance = false;
		
		if (!$instance) {
			$instance = new KernelApplication();
		}
		return $instance;
	}

	/**
	* Initializes the Application
 	*
 	* Creates Utilites instance, HTTPQuery, Session
 	* @access public
	* @see HTTPQuery
	* @see Session
	* @see TemplatesCache
	* @return void
	*/
	function Init() {
		$this->SetDefaultConstants();
				
		if (!defined('SERVER_NAME')) define('SERVER_NAME', $_SERVER['SERVER_NAME']);
		if (!defined('HIDE_SID')) setcookie('CookiesOn', 1, time()+3600);
		
		$this->Utils = new Utilites();
		$this->HTTPQuery = new HTTPQuery();

		$tmp = new newEmpty();
		$tmp->App =& $this;
		$ses_class = SESSION_CLASS;
		$this->Session = new $ses_class(NULL,$tmp);

		$this->Session->SetProperty('ses_timeout', SESSION_TIMEOUT);
		$this->Session->Init();

		$this->Factory = new ClassesFactory($this);
		$this->RegisterDefaultClasses();

		include_once(DOC_ROOT.BASE_PATH.PREFIXES);
	}

	/**
	* Registers default classes such as ItemController, GridController and LoginController
	*
	* Called automatically while initializing Application
	* @access private
	* @return void
	*/
	function RegisterDefaultClasses() {
		$this->RegisterClass('itemcontroller', 'ItemController', MODULES_PATH.'/db/controllers/item_controller.php');
		$this->RegisterClass('gridcontroller', 'GridController', MODULES_PATH.'/db/controllers/grid_controller.php');
		$this->RegisterClass(LOGIN_PREFIX, 'LoginController', MODULES_PATH.'/users/login_controller.php');
	}

	/**
	* Defines default constants if it's not defined before - in config.php
	*
	* Called automatically while initializing Application and defines:
	* LOGIN_CONTROLLER etc.
	* @access private
	* @return void
	*/
	function SetDefaultConstants() {
		if (!defined('LOGIN_CONTROLLER')) define('LOGIN_CONTROLLER', 'LoginController');
		if (!defined('LOGIN_PREFIX')) define('LOGIN_PREFIX', 'login');
		if (!defined('USERS_LIST') && defined('LOGIN_REQUIRED')) define('USERS_LIST', '/users/users.php');
		if (!defined('USER_MODEL')) define('USER_MODEL', 'Users');
		if (!defined('SESSION_CLASS')) define('SESSION_CLASS', 'Session');
		if (!defined('PREFIXES')) define('PREFIXES','/config/prefixes.php');
		if (!defined('DEFAULT_FIRST_PAGE')) define('DEFAULT_FIRST_PAGE', 'index');
		if (!defined('SESSION_TIMEOUT')) define('SESSION_TIMEOUT', 3600);
	}

	/**
	* Actually runs the parser against current template and stores parsing result
	*
	* This method gets t variable passed to the script, loads the template given in t variable and
	* parses it. The result is store in {@link $this->HTML} property.
	* @access public
	* @return void
	*/
	function &GetLoginController() {
		static $LoginController;
		if(!$LoginController) {
			$LoginController = $this->Factory->MakeClass(LOGIN_PREFIX);
			$LoginController->Init();
		}
		return $LoginController;
	}

	function ValidatePermissions($params=Array()) {
		if (defined('LOGIN_REQUIRED')) {
			$login_controller =& $this->GetLoginController();
			return $login_controller->CheckPermissions($params);
		}else
			return defined('ADMIN') || $this->RecallVar('admin_mode') ? true : false;
	}

	function IsLoggedIn() {
		if (defined('LOGIN_REQUIRED')) {
			$login_controller =& $this->GetLoginController();
			return $login_controller->LoggedIn();
		}else
			return true;
	}

	/**
	* Runs the application 
	*
	* by parsing template passed to index.php and storing the result in OUT property of application
	* @access public
	*/
	function Run() {
		$this->OUT = Array();
		$loggendIn = $this->IsLoggedIn();

		$t = $this->GetVar('t');
		if (!$t) {
			$t = 'index/main';
			$this->SetVar('t', $t);
		}

		if($loggendIn || $t == 'login/main') {
			if(is_int(strpos($t, '/'))) list($registred, $viewMetod) = explode('/', $t);
			if(isset($registred) && strlen($registred)) {
				$controller = $this->Factory->MakeClass($registred);
				if($controller) {
					$controller->Init();
					if(!strlen($viewMetod)) $viewMetod = 'main';
	
					$aView =& $controller->FindMethodInViews($viewMetod);
					if($aView !== false) $this->OUT = $aView->$viewMetod();
					else  die('<b>Fatal error: Metod '.$viewMetod.' is not found for Prefix '.$registred.'</b><br>');
				}else
					die('<b>Fatal error: can\'t create controller for PREFIX '.$registred.'</b><br>');
			}else
				die('<b>Fatal error: no PREFIX passed!</b><br>');
		}

		$this->OUT['sid'] = $this->Session->GetField('ss_sid');
		$this->OUT['expire'] = $this->Session->GetField('ss_expire') + SESSION_TIMEOUT;
		$this->OUT['loggendIn'] = $this->IsLoggedIn();
	}

	/**
	* Send the parser results to browser
	*
	* Actually send everything stored in {@link $this->HTML}, to the browser by echoing it.
	* @access public
	* @return void
	*/
	function Done() {
		header('Content-Type: text/json; charset=UTF-8');
		echo json_encode($this->OUT);
	}

	function &StoreObject($name, &$an_object) {
		$this->Objects[$name] =& $an_object;
		return $an_object;
	}

	function &RecallObject($name) {
		return $this->Objects[$name];
	}

	//	Facade
	/**
	* Registers the class and the filename where it is stored
	*
	* This method should be used in prefixes.php to let the kernel know about your new class.
	* When the kernel would need it the internal {@link Factory} will include the given filename and 
	* create the class.
	*
	* This is needed for delayed include of class files, only when the kernel realy needs it.
	* @param string $class Class name
	* @param string $path Full path to the file where given class is described
	* @access public
	* @return void
	*/
	function RegisterClass($prefix, $class, $path) {
		$this->Factory->RegisterClass($prefix, $class, $path);
	}

	/**
	* Returns current session id (SID)
	* @access public
	* @return longint
	*/
	function GetSID() {
		return $this->Session->GetID();
	}

	function DestroySession() {
		$this->Session->DestroySession();
	}

	/**
	* Returns variable passed to the script as GET/POST/COOKIE
	*
	* @access public
	* @param string $var Variable name
	* @return mixed
	*/
	function GetVar($var) {
		return $this->HTTPQuery->Get($var);
	}

	/**
	* Returns ALL variables passed to the script as GET/POST/COOKIE
	*
	* @access public
	* @return array
	*/
	function GetVars() {
		return $this->HTTPQuery->GetParams();
	}

	/**
	* Set the variable 'as it was passed to the script through GET/POST/COOKIE'
	*
	* This could be useful to set the variable when you know that 
	* other objects would relay on variable passed from GET/POST/COOKIE
	* or you could use SetVar() / GetVar() pairs to pass the values between different objects.<br>
	*
	* This method is formerly known as $this->Session->SetProperty.
	* @param string $var Variable name to set
	* @param mixed $val Variable value
	* @access public
	* @return void
	*/
	function SetVar($var, $val) {
		// echo " SetVar : $var : $val <br>";
		$this->HTTPQuery->Set($var, $val);
	}

	function RemoveVar($var) {
		return $this->Session->RemoveVar($var);
	}

	/**
	* Returns session variable value
	*
	* Return value of $var variable stored in Session. An optional default value could be passed as second parameter.
	*
	* @see SimpleSession
	* @access public
	* @param string $var Variable name
	* @param mixed $default Default value to return if no $var variable found in session
	* @return mixed
	*/
	function RecallVar($var, $default='') {
		return $this->Session->RecallVar($var, $default);
	}

	/**
	* Stores variable $val in session under name $var
	*
	* Use this method to store variable in session. Later this variable could be recalled.
	* @see RecallVar
	* @access public
	* @param string $var Variable name
	* @param mixed $val Variable value
	*/
	function StoreVar($var, $val) {
		$this->Session->StoreVar($var, $val);
	}

	function StoreVarDefault($var, $val) {
		$this->Session->StoreVarDefault($var, $val);
	}

	/**
	* Links HTTP Query variable with session variable 
	*
	* If variable $var is passed in HTTP Query it is stored in session for later use. If it's not passed it's recalled from session.
	* This method could be used for making sure that GetVar will return query or session value for given
	* variable, when query variable should overwrite session (and be stored there for later use).<br>
	* This could be used for passing item's ID into popup with multiple tab - 
	* in popup script you just need to call LinkVar('id', 'current_id') before first use of GetVar('id').
	* After that you can be sure that GetVar('id') will return passed id or id passed earlier and stored in session
	* @access public
	* @param string $var HTTP Query (GPC) variable name
	* @param mixed $ses_var Session variable name
	* @param mixed $default Default variable value
	*/
	function LinkVar($var, $ses_var=null, $default='') {
		if (!isset($ses_var)) $ses_var = $var;
		if ($this->GetVar($var) !== false)
			$this->StoreVar($ses_var, $this->GetVar($var));
		else
			$this->SetVar($var, $this->RecallVar($ses_var, $default));
	}

	/**
	* Returns variable from HTTP Query, or from session if not passed in HTTP Query
	*	
	* The same as LinkVar, but also returns the variable value taken from HTTP Query if passed, or from session if not passed.
	* Returns the default value if variable does not exist in session and was not passed in HTTP Query
	*
	* @see LinkVar
	* @access public
	* @param string $var HTTP Query (GPC) variable name
	* @param mixed $ses_var Session variable name
	* @param mixed $default Default variable value
	* @return mixed
	*/
	function GetLinkedVar($var, $ses_var=null, $default='') {
		if (!isset($ses_var)) $ses_var = $var;
		$this->LinkVar($var, $ses_var, $default);
		return $this->GetVar($var);
	}

	function ConfigOption($option, $default=NULL) {
		// echo " ConfigOption for '$option' <br>";
		return $this->Session->Config->GetOption($option, $default);
	}

	function SetConfigOption($option,$value) {
		// echo " ConfigOption for '$option' <br>";
		return $this->Session->Config->SetOption($option,$value);
	}

	function GetFileMime($fullpath) {
		//echo $fullpath;
		if(extension_loaded('mime_magic') && function_exists('mime_content_type'))
			$mime = mime_content_type($fullpath);
		
		if((function_exists('finfo_open') || extension_loaded('fileinfo')) && (!isset($mime) || !$mime)) {
			static $fInfo;
			
			if(!$fInfo)
				$fInfo = finfo_open(FILEINFO_MIME, MODULES_PATH.'/magic/magic');
			
			$mime = finfo_file($fInfo, $fullpath);
		}
		
		if(!$mime) {
			echo "<b>mime_magic</b> or <b>fileinfo</b> extension required for Mime Type definition not installed<br>\n";
			exit;
		}
		
		return $mime;
	}

	function Sendmail($params) {
		$incpath = ini_get('include_path');
		if((!class_exists('Mail_mime') || !class_exists('Mail')) && !is_int(strpos($incpath, 'pear')))
			ini_set('include_path', MODULES_PATH.DIRECTORY_SEPARATOR.'pear'.DIRECTORY_SEPARATOR.PATH_SEPARATOR.ini_get('include_path'));

		$tmperrrep = ini_get('error_reporting');
		ini_set('error_reporting', E_ALL & ~E_DEPRECATED);

		include_once('Mail.php');
		include_once('Mail/mime.php');
 
		$mparams['host'] = DEFINED('MAIL_HOST')?MAIL_HOST:'localhost';
		$mparams['port'] = DEFINED('MAIL_PORT')?MAIL_PORT:25;
		$mparams['auth'] = DEFINED('MAIL_AUTH')?MAIL_AUTH:false;
		$mparams['username'] = DEFINED('MAIL_USER')?MAIL_USER:'';
		$mparams['password'] = DEFINED('MAIL_PASS')?MAIL_PASS:'';

		$hdrs['From'] = $params['from'];
		//$hdrs['Reply-To'] = isset($params['reply_to'])?$params['reply_to']:$params['from'];
		$hdrs['Subject'] = $params['subject'];
		$hdrs['To'] = $params['send_to'];
		$hdrs['X-Mailer'] = 'W PRO Mailer v1.0';

		$mime = new Mail_mime("\r\n");
		$mime->_build_params['html_charset'] = isset($params['charset'])?$params['charset']:'ISO-8859-1';
		$mime->_build_params['text_charset'] = isset($params['charset'])?$params['charset']:'ISO-8859-1';
		$mime->_build_params['head_charset'] = isset($params['charset'])?$params['charset']:'ISO-8859-1';
		$mime->_build_params['head_encoding'] = 'base64';

		$mime->setTXTBody($params["text_body"]);
		if(isset($params["html_body"])) $mime->setHTMLBody($params["html_body"]);

		if(isset($params['files']) && is_array($params['files']))
			foreach($params['files'] as $file) $mime->addAttachment($file, isset($params['files_mime'][basename($file)]) ? $params['files_mime'][basename($file)]:$this->GetFileMime($file));
		elseif(isset($params['files']))
			$mime->addAttachment($params['files'], isset($params['files_mime']) ? $params['files_mime']:$this->GetFileMime($params['files']));

		$body = $mime->get();
		$mail = Mail::factory('smtp', $mparams);

		$ret = false;
		$headers = $mime->headers($hdrs);

		$ret = $mail->send($params['send_to'], $headers, $body);
		ini_set('error_reporting', $tmperrrep);

 		return $ret;
	}

	/**
	* Return ADODB Connection object
	*
	* Returns ADODB Connection object already connected to the project database, configurable in config.php
	* @access public
	* @return ADODBConnection
	*/
	function &GetADODBConnection() { 
		static $conn;

		if (!$conn) {
			$conn = ADONewConnection(SQL_TYPE);
			$conn->Connect(SQL_SERVER, SQL_USER, SQL_PASS, SQL_DB);
			$conn->SetFetchMode(ADODB_FETCH_ASSOC);

			if (defined('MYSQL_CHARSET'))
				$conn->Execute("SET NAMES '".MYSQL_CHARSET."'");
		}

		return $conn;
	}

	function UserError($msg) {
		error_reporting(E_ALL);
		trigger_error($msg, E_USER_WARNING  );
	}

	function IsDebugMode() {
		// returns debug mode constant status (set in config)
		return defined('DEBUG_MODE') ? 1 : 0;	
	}
	
}

class newEmpty {
	var $Owner = NULL;
}

?>
