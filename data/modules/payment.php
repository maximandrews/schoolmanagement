<?php
global $MODULES_PATH,$APP_PATH;
include_once("$MODULES_PATH/base.php");
include_once("$MODULES_PATH/common.php");

class PaymentGW extends Base {
	
	var $host;
	var $script;
	var $port = 443;
	
	var $login;
	var $amount;
	var $card_num;
	var $exp_date;
	var $adc_url;
	var $delim;
	var $type;
	var $delim_char;
	var $test;
	
	var $results = Array();
	
	
	var $types = Array(
		'charge' => 'AUTH_CAPTURE',
		'auth_only' => 'AUTH_ONLY',
		'prior_auth_capture' => 'PRIOR_AUTH_CAPTURE'
		);
		
	var $type_params = Array(
		'charge' => Array('login', 'amount', 'card_num', 'exp_date', 'adc_url', 'delim')
	);
		
	//Based on eccx.com (authorize.net probably has the same)
	var $params_map = Array(
		'login' => 'x_Login',
		'amount' => 'x_Amount',
		'card_num' => 'x_Card_Num',
		'exp_date' => 'x_Exp_Date',
		'adc_url' => 'x_ADC_URL',
		'delim' => 'x_ADC_Delim_Data',
		'tran_key' => 'x_Tran_Key',
		'delim_char' => 'x_ADC_Delim_Character',
		'encaps_char' => 'x_ADC_Encapsulate_Character',
		'password' => 'x_Password',
		'version' => 'x_Version',
		'test' => 'x_Test_Request',
		'method' => 'x_Method',
		'type' => 'x_Type',
		'trans_id' => 'x_Trans_ID',
		'auth_code' => 'x_Auth_Code',
		'bank_name' => 'x_Bank_Name',
		'bank_acc_type' => 'x_Bank_Acct_Type',
		'bank_aba_code' => 'x_Bank_ABA_Code',
		'bank_acc_num' => 'x_Bank_Acct_Num',
		'tax' => 'x_Tax',
		'tax_exempt' => 'x_Tax_Exempt',
		'duty' => 'x_Duty',
		'freight' => 'x_Freight',
		'invoice_num' => 'x_Invoice_Num',
		'po_num' => 'x_PO_Num',
		'cust_id' => 'x_Cust_ID',
		'description' => 'x_Description',
		'first_name' => 'x_First_Name',
		'last_name' => 'x_Last_Name',
		'company' => 'x_Company',
		'address' => 'x_Address',
		'city' => 'x_City',
		'state' => 'x_State',
		'zip' => 'x_Zip',
		'country' => 'x_Country',
		'phone' => 'x_Phone',
		'fax' => 'x_Fax',
		'ship_first_name' => 'x_Ship_To_First_Name',
		'ship_last_name' => 'x_Ship_To_Last_Name',
		'ship_company' => 'x_Ship_To_Company',
		'ship_address' => 'x_Ship_To_Address',
		'ship_city' => 'x_Ship_To_City',
		'ship_state' => 'x_Ship_To_State',
		'ship_zip' => 'x_Ship_To_Zip',
		'ship_country' => 'x_Ship_To_Country',
		'email_customer' => 'x_Email_Customer',
		'email' => 'x_Email',
		'email_merchant' => 'x_Email_Merchant',
		'merchant_email' => 'x_Merchant_Email',
		'receipt_link' => 'x_Receipt_Link_URL',
		'receipt_method' => 'x_Receipt_Link_Method',
		'receipt_link_text' => 'x_Receipt_Link_Text',
		'color_text' => 'x_Color_Text',
		'color_link' => 'x_Color_Link',
		'color_background' => 'x_Color_Background',
		'logo_url' => 'x_Logo_URL',
		'background_url' => 'x_Background_URL',
		'rename' => 'x_Rename',
		'header_html' => 'x_Header_Html_Payment_Form',
		'footer_html' => 'x_Footer_Html_Payment_Form',
		'header_html_receipt' => 'x_Header_Html_Receipt',
		'footer_html_receipt' => 'x_Footer_Html_Receipt',
		'header_email_receipt' => 'x_Header_Email_Receipt',
		'footer_email_receipt' => 'x_Footer_Email_Receipt',
		'use_fraudscreen' => 'x_Use_Fraudscreen',
		'avs_filter' => 'x_AVS_Filter'
	);
		
	var $result_map = Array(
		0  => 'response_code',
		1  => 'response_subcode',
		2  => 'response_reason_code',
		3  => 'response_reason_text',
		4  => 'auth_code',
		5  => 'avs_code',
		6  => 'trans_id',
		7  => 'invoice_num',
		8  => 'descript_perlion',
		9  => 'amount',
		10 => 'method',
		11 => 'type',
		12 => 'cust_id',
		13 => 'first_name',
		14 => 'last_name',
		15 => 'company',
		16 => 'address',
		17 => 'city',
		18 => 'state',
		19 => 'zip',
		20 => 'country',
		21 => 'phone',
		22 => 'fax',
		23 => 'email',
		24 => 'ship_to_first_name',
		25 => 'ship_to_last_name',
		26 => 'ship_to_company',
		27 => 'ship_to_address',
		28 => 'ship_to_city',
		29 => 'ship_to_state',
		30 => 'ship_to_zip',
		31 => 'ship_to_country',
		32 => 'tax',
		33 => 'duty',
		34 => 'freight',
		35 => 'tax_exempt',
		36 => 'po_num',
		37 => 'md5_hash'
	);
		
	function PaymentGW($host, $script, $port=NULL) {
		$this->host = $host;
		$this->script = $script;
		if ($port != null)
			$this->port = $port;
	}
	
	function SetParams($params_array) {
		foreach ($params_array AS $key => $val)
		{
			$this->$key = $val;			
		}
	}
	
	function Capture() {
		$params = Array();
		/*
		foreach ($this->type_params['charge'] AS $key => $val) {
			$mapped_param = $this->params_map[$val];
			$params[$mapped_param] = $this->$val;
		}
		*/
		$mapped_param = $this->params_map['type'];
		$params[$mapped_param] = $this->types['charge'];
		
		//$mapped_param = $this->params_map['test'];
		//$params[$mapped_param] = $this->test;
		
		foreach ($this->params_map AS $key => $val)
		{
			if ($this->$key != null) 
				$params[$val] = $this->$key;
		}
		
		$res = $this->run_gw($params);
		$this->ParseResult($res);
		return $this->results['response_code'] == 1;
	}
	
	function run_gw($params) {
		$params['_host'] = $this->host;
		$params['_script'] = $this->script;
		$params['_port'] = $this->port;
		return run_gw($params);
	}
	
	function AuthOnly() {
		
	}
	
	function PriorAuthCapture() {
		
	}
	
	function ParseResult($res) {
		$result = explode($this->delim_char, $res);
		
		for($i=0; $i<=count($result); $i++)
		{
			$mapped_result = $this->result_map[$i];
			$this->results[$mapped_result] = $result[$i];
		}
	}

}



?>