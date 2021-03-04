<?php
include_once(APP_PATH.'/e_item.php');

class Pretendent extends EDBItem {
	function __construct($Id, $Owner) {
		$this->table_name = 'pretendents';
		$this->App =& KernelApplication::Instance();
		$this->DisplayQueries = 0;
		$this->DisplayErrors = 0;
		parent::__construct($Id, $Owner);
	}
// pt_id, pt_firstnamechild, pt_lastnamechild, pt_personcodechild,  pt_genderchild, pt_cl_level, pt_comment,
// pt_firstnameparent, pt_lastnameparent, pt_personcodeparent, pt_password, pt_phonenumber, pt_mailsms,
// pt_created, pt_modified

	function &GetValidator($name, $type, $error_msg = null) {
		switch ($name) {
			case 'pt_cl_level':
				$validator = new IntegerValidator($name, $this, $error_msg);
				$validator->minValue = 1;
				$validator->maxValue = 12;
			break;
			case 'pt_personcodechild':
			case 'pt_personcodeparent':
				$validator = new LVPersonCodeValidator($name, $this, $error_msg);
			break;
			case 'pt_phonenumber':
				$validator = new LVPhoneShortValidator($name, $this, $error_msg);
			break;
			case 'pt_mailsms': 
			case 'pt_emailchild':
			case 'pt_emailparent':
				$validator = new EmailValidator($name, $this, $error_msg);
			break;
			case 'pt_password': // plain-text password
				$validator = new PasswordValidator($name, $this);
				$validator->min_length = '4';
				$validator->max_length = '20';
				$validator->format = 'MD5';
				$validator->repass_field = 'repass_pt_password';
				$this->CreateField('repass_pt_password',null,1,0);
			break;
			default:
				$validator =& parent::GetValidator($name, $type, $error_msg);
			break;
		}
		return $validator;
	}
// pt_id, pt_firstnamechild, pt_lastnamechild, pt_personcodechild,  pt_genderchild, pt_cl_level, pt_comment,
// pt_firstnameparent, pt_lastnameparent, pt_personcodeparent, pt_password, pt_phonenumber, pt_mailsms,
// pt_created, pt_modified
	function &GetFormatter($name, $type, $format = null) {
		switch ($name) {
			case 'pt_created':
			case 'pt_modified':
				$formatter = new DateFormatter($name, $this);
				$formatter->format = 'd.m.Y';
			default:
				$formatter =& parent::GetFormatter($name, $type);
			break;
		}
		return $formatter;
	}
	
	function Update($fields_list=NULL) {
		$this->SetDBField('pt_modified', time());
		return parent::Update();
	}

	function Create() {
		$this->SetDBField('pt_created', time());
		$this->SetDBField('pt_modified', time());

		return parent::Create();
	}

}

class PretendentsList extends EFilteredDBList {			
	function __construct($sql, $query_now=0, $owner=null) {
		parent::__construct($sql, $query_now, $owner);

// pt_id, pt_firstnamechild, pt_lastnamechild, pt_personcodechild,  pt_genderchild, pt_cl_level, pt_comment,
// pt_firstnameparent, pt_lastnameparent, pt_personcodeparent, pt_password, pt_phonenumber, pt_mailsms,
// pt_created, pt_modified
		$this->sql = '	SELECT
							pt.pt_id,
							pt.pt_emailchild,
							pt.pt_firstnamechild,
							pt.pt_lastnamechild,
							pt.pt_personcodechild,
							SUBSTRING(pt.pt_personcodechild,1,6) pt_childbirthday,
							pt.pt_genderchild,
							pt.pt_cl_level,
							pt.pt_comment,
							pt.pt_emailparent,
							pt.pt_firstnameparent,
							pt.pt_lastnameparent,
							pt.pt_personcodeparent,
							pt.pt_password,
							pt.pt_phonenumber,
							pt.pt_mailsms,
							pt.pt_created,
							pt.pt_modified
						FROM pretendents pt';
		$this->DisplayQueries = 0;
	}
	
	function &NewItem() {
		$new = new Pretendent(NULL, $this->Owner);
		return $new;
	}

	function AddStdFilter($type, $field, $value, $having=false, $system=0) {
		// types
		// equals, not_equals, like, rangefrom, rangeto, datefrom, dateto
		switch($field) {
			case 'cl_level':
				$type = 'equals';
			break;
		}
		parent::AddStdFilter($type, $field, $value, $having, $system);
	}
}
?>