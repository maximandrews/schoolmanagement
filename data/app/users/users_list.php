<?php
include_once(APP_PATH.'/e_item.php');

class User extends EDBItem {

	function __construct($Id, $Owner) {
		$this->table_name = 'persons';
		$this->App =& KernelApplication::Instance();
		$this->CreateField('ps_ut_text',null,0,0);
		$this->CreateField('ps_cl_txt',null,0,0);
		$this->DisplayQueries = 0;
		$this->DisplayErrors = 0;
		parent::__construct($Id, $Owner);
	}
//ps_id, ps_email, ps_password, ps_ut_id, ps_ut_text, ps_firstname, ps_lastname, ps_birthdate, ps_mailsms, ps_cl_id, ps_cl_txt, ps_created, ps_modified
	function &GetValidator($name, $type, $error_msg = null) {
		switch ($name) {
			case 'ps_password': // plain-text password
				$validator = new PasswordValidator($name, $this);
				$validator->min_length = '4';
				$validator->max_length = '20';
				$validator->format = 'SHA1';
				$validator->repass_field = 'repass_ps_password';
				$this->CreateField('repass_ps_password',null,1,0);
			break;
			case 'ps_email': // unique username
			case 'ps_mailsms':
				$validator = new EmailValidator($name, $this, $error_msg);
			break;
			case 'ps_ut_id':
			case 'ps_cl_id':
				$validator = new OptionsValidator($name, $this, $error_msg);
			break;
			case 'ps_birthdate':
				$validator = new Validator($name, $this, $error_msg);
			break;
			case 'ps_personcode':
				$validator = new LVPersonCodeValidator($name, $this, $error_msg);
			break;
			default:
				$validator =& parent::GetValidator($name, $type, $error_msg);
		}
		return $validator;
	}
	
	function Load($Id, $IdField=NULL){
		parent::Load($Id, $IdField);
		$this->SetDBField('ps_password','');
	}

	function &GetFormatter($name, $type, $format = null) {
		switch ($name) {
			case 'ps_ut_id':
			case 'ps_cl_id':
				$formatter = new OptionsFormatter($name, $this);

				if($name == 'ps_ut_id')
					$sql = 'SELECT ut_id, ut_name FROM usertypes ORDER BY ut_id ASC';
				else
					$sql = 'SELECT cl_id, CONCAT(cl_level, cl_postfix) cl FROM classes ORDER BY cl ASC';

				$options = $this->CustomOptions($sql);

				$formatter->SetOptions($options);
			break;
			case 'ps_birthdate':
				$formatter = new MySQLDateFormatter($name, $this);
				$formatter->format = 'd.m.Y';
			break;
			case 'ps_created':
			case 'ps_modified':
				$formatter = new DateFormatter($name, $this);
				$formatter->format = 'd.m.Y';
			break;
			default:
				$formatter =& parent::GetFormatter($name, $type);
			break;
		}
		return $formatter;
	}
	
	function Update($fields_list=NULL) {
		$this->SetDBField('ps_modified', time());
		/*if ($this->GetField('ps_id') != $this->App->Session->GetField('ps_id') && $this->GetField('ps_id') == 1)
			return true;*/
		$this->SetPassword('update');
		return parent::Update();
	}
	
	function Delete() {
		$this->FieldErrors = Array();
		if ($this->id > 0 && $this->id == $this->App->Session->GetField('ps_id'))
			$this->FieldErrors['ps_id'] .= 'You can\'t delete yourself!\n';

		if($this->id > 0 && $this->id != $this->App->Session->GetField('ps_id'))
			return parent::Delete();
	}

	function SetPassword($action) {
		if ($action == 'update') {
			if($this->GetField('ps_password') == '') {
				$this->SetRequiredFields(Array('ps_password','repass_ps_password'), 0);
				$this->SetDbFields(Array('ps_password'), 0);
			}
		} else {
			$this->SetRequiredFields(Array('ps_password','repass_ps_password'), 1);
			$this->SetDbFields(Array('ps_password'), 1);
		}
	}

	function Create() {
		$this->SetDBField('ps_created', time());
		$this->SetDBField('ps_modified', time());
		$this->SetPassword('create');
		return parent::Create();
	}

	function AllowedModules() {
		$aModules = Array();
		$type = $this->GetDBField('ps_ut_id');

		switch ($type) {
			case 1:
				$aModules = Array(
					'modules.RecordBook',
					'modules.PersonData'
				);
			break;
			case 2:
				$aModules = Array(
					'modules.RecordBook',
					'modules.PersonData'
				);
			break;
			case 3:
				$aModules = Array(
					'modules.ClassRegister',
					'modules.PersonData',
					'modules.Schedule'
				);
			break;
			case 4:
				$aModules = Array(
					'modules.ClassRegister',
					'modules.PersonData',
					'modules.UserReg',
					'modules.ClassReg',
					'modules.PretendentList',
					'modules.LessonList',
					'modules.Schedule'
				);
			break;
		}
		
		return $aModules;
	}
	
	function CheckPermissions($prs=Array()) {
		if(!isset($prs['t'])) $prs['t'] = strtolower($this->App->GetVar('t'));

		$sys_tpls = Array('login', 'access', 'index', 'top', 'hidden_script', 'close_win', 'close_popup');
		if(is_int(array_search($prs['t'], $sys_tpls)))
			return true;

		if(!$this->loaded)
			$this->Load($this->App->Session->GetField('user_id'));

		$user_type = $this->GetDBField('user_type');
		
		if($this->loaded && $user_type > 1) {
			
			$all['close_win']['*']['*']['*']['*']['*'] = 1;
			$all['close_popup']['*']['*']['*']['*']['*'] = 1;
				
			if($user_type == 3) {
				$all['shop/orders/private/list']['orders']['*']['private']['*']['*'] = 1;
				$all['shop/orders/private/edit']['orders']['*']['private']['*']['*'] = 1;
				$all['shop/orders/private/edit']['orderitems']['grid']['shoppingcart']['*'][''] = 1;
				
				$all['shop/orders/private/items']['orders']['*']['private']['*']['*'] = 1;
				$all['shop/orders/private/items']['orders']['*']['private']['*']['*'] = 1;
				$all['shop/orders/private/items']['orderitems']['*']['']['*']['*'] = 1;
			}
			
			$i = 0;
			if(isset($all[$prs['t']])) $a[$i++] = $prs['t'];
			elseif(isset($all['*'])) $a[$i++] = '*';
			else{
				//print_pre($prs);
				return false;
			}
			
			if(isset($all[$a[0]][$prs['prefix']])) $a[$i++] = $prs['prefix'];
			elseif(isset($all[$a[0]]['*'])) $a[$i++] = '*';
			else{
				//print_pre($prs);
				return false;
			}
			
			if(isset($all[$a[0]][$a[1]][$prs['prefix_type']])) $a[$i++] = $prs['prefix_type'];
			elseif(isset($all[$a[0]][$a[1]]['*'])) $a[$i++] = '*';
			else{
				//print_pre($prs);
				return false;
			}
			
			if(isset($all[$a[0]][$a[1]][$a[2]][$prs['special']])) $a[$i++] = $prs['special'];
			elseif(isset($all[$a[0]][$a[1]][$a[2]]['*'])) $a[$i++] = '*';
			else{
				//print_pre($prs);
				return false;
			}
			
			if(isset($all[$a[0]][$a[1]][$a[2]][$a[3]][$prs['action_type']])) $a[$i++] = $prs['action_type'];
			elseif(isset($all[$a[0]][$a[1]][$a[2]][$a[3]]['*'])) $a[$i++] = '*';
			else{
				//print_pre($prs);
				return false;
			}
			
			if(isset($all[$a[0]][$a[1]][$a[2]][$a[3]][$a[4]][$prs['action']])) $a[$i++] = $prs['action'];
			elseif(isset($all[$a[0]][$a[1]][$a[2]][$a[3]][$a[4]]['*'])) $a[$i++] = '*';
			else{
				//print_pre($prs);
				return false;
			}
			
			if($i === 6) return true;
		}

		return false;
	}

	function Login($login, $password) {
		$conn =& $this->App->GetADODBConnection();
		$query = sprintf("SELECT
												ps.ps_id,
												ps.ps_email,
												ps.ps_ut_id,
												ut.ut_name AS ps_ut_txt
											FROM %1\$s ps
											LEFT JOIN usertypes ut ON ut.ut_id=ps.ps_ut_id
											WHERE ps.ps_email = %2\$s AND ps.ps_password = SHA1(%3\$s)",
											$this->table_name,
											$conn->qstr($login),
											$conn->qstr($password));
		if ($this->DisplayQueries) echo get_class($this).": LOGIN query is ".$query."<br>";
		$rs = $conn->Execute($query);

		if ($rs === false && $this->DisplayQueries) echo "Error executing statement: ".$conn->ErrorMsg()."<br>";
		
		if ($rs && !$rs->EOF) {
			$this->App->StoreVar('ps_email', $rs->fields['ps_email']);
			$this->App->StoreVar('ps_ut_id', $rs->fields['ps_ut_id']);
			$this->App->StoreVar('ps_ut_txt', $rs->fields['ps_ut_txt']);
			$this->App->StoreVar('admin_mode', 1);
			$this->App->Session->SetField('ss_ps_id',$rs->fields['ps_id']);
			$this->App->Session->Update();
			return true;
		}

		return false;
	}
	
	function Logout() {
		$this->App->Session->SetField('ss_ps_id', 0);
		$this->App->RemoveVar('ps_email');
	}
}

class UsersList extends EFilteredDBList {
	function __construct($sql, $query_now=0, $owner=null) {
		parent::__construct($sql, $query_now, $owner);

		$prefix = $owner->prefix;
		$parent = $this->App->GetVar('parentId');
		$class = $this->App->GetVar('classId');
		$child = $this->App->GetVar('childId');
		$childclass = $this->App->GetVar('childClass');
		switch($prefix) {
			case 'classparents':
//pr_id, pr_parent, pr_child, pr_created, pr_modified
				$this->sql = 'SELECT
												ps.ps_id,
												ps.ps_firstname,
												ps.ps_lastname,
												ps.ps_birthdate,
												ps.ps_email
											FROM persons ps
											LEFT JOIN `parents` pr ON pr.pr_parent=ps.ps_id
											LEFT JOIN `persons` ps1 ON pr.pr_child=ps1.ps_id';
				$this->AddFilter(new EqualsFilter('ps.ps_ut_id', 2), 'onlypare', 1);
				$this->AddFilter(new EqualsFilter('ps1.ps_cl_id', intval($class)), 'onlyp', 1);
				$this->setGroupByClause('ps.ps_id');
			break;
			case 'parentschildren':
//pr_id, pr_parent, pr_child, pr_created, pr_modified
				$this->sql = 'SELECT
												ps.ps_id,
												ps.ps_firstname,
												ps.ps_lastname,
												ps.ps_birthdate,
												ps.ps_cl_id,
												CONCAT(cl.cl_level, \'.\', cl.cl_postfix) ps_cl_txt,
												ps.ps_email
											FROM persons ps
											LEFT JOIN `classes` cl ON cl.cl_id=ps.ps_cl_id
											LEFT JOIN `parents` pr ON pr.pr_child=ps.ps_id';
				$this->AddFilter(new EqualsFilter('ps_ut_id', 1), 'onlypupil', 1);
				$this->AddFilter(new EqualsFilter('pr_parent', intval($parent)), 'pr_parentKMwkds', 1);
				$this->setGroupByClause('ps.ps_id');
			break;
			case 'parentschildrenadd':
//pr_id, pr_parent, pr_child, pr_created, pr_modified
				$this->sql = 'SELECT
												ps.ps_id,
												ps.ps_firstname,
												ps.ps_lastname,
												ps.ps_personcode,
												ps.ps_birthdate,
												ps.ps_cl_id,
												CONCAT(cl.cl_level, \'.\', cl.cl_postfix) ps_cl_txt
											FROM persons ps
											LEFT JOIN `classes` cl ON cl.cl_id=ps.ps_cl_id
											LEFT JOIN `parents` pr ON pr.pr_child=ps.ps_id AND pr.pr_parent='.intval($parent);
				$this->AddFilter(new EqualsFilter('ps_ut_id', 1), 'onlypupil', 1);
				$this->AddFilter(new IsNullFilter('pr_id'), 'pr_parentKMwkds', 1);
				$this->setGroupByClause('ps.ps_id');
			break; 
			case 'classaddchild':
				$this->sql = 'SELECT
												ps.ps_id,
												ps.ps_firstname,
												ps.ps_lastname,
												ps.ps_birthdate,
												ps.ps_personcode,
												ps.ps_email,
												ps.ps_cl_id,
												CONCAT(cl.cl_level, \'.\', cl.cl_postfix) ps_cl_txt
											FROM persons ps
											LEFT JOIN `classes` cl ON cl.cl_id=ps.ps_cl_id';
				$this->AddFilter(new EqualsFilter('ps_ut_id', 1), 'onlypupil', 1);
				$this->AddFilter(new NotEqualsFilter('ps_cl_id', intval($class)), 'selected_class', 1);
				$this->setGroupByClause('ps.ps_id');
			break;
			case 'childparents':
//pr_id, pr_parent, pr_child, pr_created, pr_modified
				$this->sql = 'SELECT
												ps.ps_id,
												ps.ps_firstname,
												ps.ps_lastname,
												ps.ps_birthdate,
												ps.ps_personcode,
												ps.ps_email
											FROM persons ps
											LEFT JOIN `parents` pr ON pr.pr_parent=ps.ps_id';
				$this->AddFilter(new EqualsFilter('ps_ut_id', 2), 'onlypupil', 1);
				$this->AddFilter(new EqualsFilter('pr_child', intval($child)), 'pr_parentKMwkds', 1);
				$this->setGroupByClause('ps.ps_id');
			break;
			case 'classregchildren':
//pr_id, pr_parent, pr_child, pr_created, pr_modified
				$this->sql = 'SELECT
												ps.ps_id,
												ps.ps_firstname,
												ps.ps_lastname,
												ps.ps_birthdate,
												ps.ps_email
											FROM persons ps';
				$this->AddFilter(new EqualsFilter('ps_ut_id', 1), 'onlypupil1', 1);
				$this->AddFilter(new EqualsFilter('ps_cl_id', intval($childclass)), 'pupilsclass', 1);
				$this->setGroupByClause('ps.ps_id');
			break;
//ps_id, ps_email, ps_password, ps_ut_id, ps_ut_text, ps_firstname, ps_lastname, ps_birthdate, ps_mailsms, ps_cl_id, ps_cl_txt, ps_created, ps_modified
			default:
				$this->sql = 'SELECT
												ps.ps_id,
												ps.ps_email,
												ps.ps_ut_id,
												ut.ut_name ps_ut_text,
												ps.ps_firstname,
												ps.ps_lastname,
												ps.ps_birthdate,
												ps.ps_personcode,
												ps.ps_mailsms,
												ps.ps_cl_id,
												CONCAT(cl.cl_level, \'.\', cl.cl_postfix) ps_cl_txt,
												ps.ps_created,
												ps.ps_modified
											FROM persons ps
											LEFT JOIN `usertypes` ut ON ut.ut_id=ps.ps_ut_id
											LEFT JOIN `classes` cl ON cl.cl_id=ps.ps_cl_id';
		}
		$this->DisplayQueries = 0;
	}
	
	function &NewItem() {
		$new = new User(NULL, $this->Owner);
		return $new;
	}

	function AddStdFilter($type, $field, $value, $having=false, $system=0) {
		// types
		// equals, not_equals, like, rangefrom, rangeto, datefrom, dateto
		switch($field) {
			case 'ps_ut_id':
				$type = 'equals';
			break;
			case 'ps_cl_txt':
				$having = true;
			case 'ps_firstname':
			case 'ps_lastname':
			case 'ps_personcode':
			case 'ps_personcode':
				$type = 'like';
			break;
		}
		parent::AddStdFilter($type, $field, $value, $having, $system);
	}
}
?>