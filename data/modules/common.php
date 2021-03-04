<?php
global $adodb_ver;

global $protocol, $doc_root, $base_path, $ses_timeout, $sql_server, $sql_user, $sql_pass, $sql_db;

if (!isset($protocol) || $protocol == '') 
	$protocol = 'http://';

//include_once("$doc_root$base_path/adodb/adodb.inc.php");


/*$conn=&ADONewConnection($sql_type);
$conn->Connect($sql_server, $sql_user, $sql_pass, $sql_db);
$conn->SetFetchMode(ADODB_FETCH_ASSOC);
*/

//print_r($conn);

function &GetADODBConnection() {
	$app =& KernelApplication::Instance();
	return $app->GetADODBConnection();
}

function get_global($param) {
	global $$param;
	return $$param;
}

function print_pre($value, $msg=NULL) { 
	if ($msg != NULL) echo "$msg<Br>";
	echo "<pre>";
	print_r($value);
	echo "</pre>";
}

function CutLastComma($expr) {
	$ret = preg_replace("/,[ ]*$/is", '', $expr);
	return $ret;
}

function COL_login($username, $password) {
	global $session;
	if( strlen($username) < 3 OR  strlen($password) < 3 ) return -1;
	$password = md5($password);
	$sql = "SELECT * FROM ".(defined('TLB_PREFIX')?TLB_PREFIX:'') ."users WHERE user_login='$username' and user_pass='$password' and user_pend!=1 AND user_vis=1";
	$adodbConnection = GetADODBConnection();
	$res = $adodbConnection->Execute($sql);
	//echo "$sql<br>";
	if (!$res->EOF) {
		$user_id = $res->fields["user_id"];
		if ( $user_id > 0 ) {
			//echo "returning $user_id";
			return $user_id;
		}else
			return -1;
	}else
		return -1;
}


// used to remove numbered entries from hashes which ADODB returns ($res->fields);
function ClearHash($hash) {
	foreach ($hash as $key => $val) {
		echo "checking $key<BR>";
		if (eregi("^[0-9]*$", $key)) unset($hash[$key]);
	}
	return $hash;
}

function getmicrotime(){ 
    list($usec, $sec) = explode(" ",microtime()); 
    //echo "Usec: $usec, sec: $sec<br>";
    return ((float)$usec + (float)$sec); 
	} 

function mem_size(&$var) {
	$s_var = serialize($var);
	$s_var = strlen($s_var);
	return $s_var;
}

function Get_post_files() {
	global $HTTP_POST_FILES;
	return $HTTP_POST_FILES;
}

// seed with microseconds
		function make_seed() {
    	list($usec, $sec) = explode(' ', microtime());
    	return (float) $sec + ((float) $usec * 100000);
		}

		function run_gw($params) {
				global $MODULES_PATH;
				$script_perl = $MODULES_PATH.'/post.pl';
		
				foreach ($params as $key => $value) {
					$params_str.= escapeshellarg ("$key=$value").' ';
				}
				
				$run_line = $script_perl.' '.$params_str;
				//echo "$run_line<br>";
				$exit_code = exec ($run_line, $rets);
			
				return join("\n", $rets);;	
}
	
function curl_post($url, $post)
{
	// submits $url with $post as POST
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$ret = curl_exec($ch);
	curl_close($ch);
	return $ret;
}

function ExtractDate($timestamp)
{
	return	mktime(0,0,0,
			date('m',$timestamp),
			date('d',$timestamp),
			date('Y',$timestamp));	
}

function mes($str)
{
	return mysql_escape_string($str);
}
?>