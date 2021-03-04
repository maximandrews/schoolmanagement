<?php
include_once(MODULES_PATH.'/utility/params.php');

class HTTPQuery extends Params {
	var $Post;
	var $Get;
	var $Cookie;
	var $Server;
	var $Env;
	var $Order;

	function HTTPQuery($order='CGPF') {
		$this->Order = $order;
		$this->AddAllVars();
	}
	
	function AddAllVars() {
		for ($i=0; $i < strlen($this->Order); $i++) {
			$current = $this->Order[$i];
			switch ($current) {
				case 'G':
					$this->Get = $this->AddVars($_GET);
					break;
				case 'P':
					$this->Post = $this->AddVars($_POST);
					break;
				case 'C':
					$this->Cookie = $this->AddVars($_COOKIE);
					break;
				case 'E';
					$this->Env = $this->AddVars($_ENV);
					break;
				case 'S';
					$this->Server = $this->AddVars($_SERVER);
					break;
				case 'F';
					$this->Server = $this->AddVars($_FILES);
					break;
			}
		}
	}

	function AddVars($array) {
		foreach ($array as $key => $val)
			$this->Set($key, $val);
		return $array;
	}
}

?>