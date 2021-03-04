<?php
include_once(APP_PATH.'/e_controller.php');
include_once(APP_PATH.'/users/users_list.php');
include_once(APP_PATH.'/users/users_view.php');

class UsersGridController extends EGridController {
	function CreateView() {
		$this->AddView(new UsersGridView($this));
	}

	function CreateModel() {
		$this->Grid = new UsersList('', 0, $this);
	}

	function actAddChildren() {
		$parent = $this->App->GetVar('parentId');
		$children = json_decode($this->App->GetVar('chldrn'), true);
		
		if(!is_array($children) || !count($children) || !$parent) {
			$this->errors[] = 'Nav datu';
			return false;
		}

		$sql = 'SELECT ps_id FROM persons WHERE ps_id='.intval($parent).' AND ps_ut_id=2';
		//echo $sql."\n";
		$parent = $this->Conn->GetOne($sql);
		if(!$parent || !($parent > 0)) {
			$this->errors[] = 'Vecâks izvçlçts nepareizi';
			return false;
		}

		$ch = Array();
		foreach($children as $chn) {
			$ch[] = intval($chn);
		}

		if(!count($ch)) {
			$this->errors[] = 'Bçrni nav izvçlçti';
			return false;
		}

		$sql = 'SELECT ps.ps_id FROM persons ps
						LEFT JOIN `parents` pr ON pr.pr_child=ps.ps_id AND pr.pr_parent='.intval($parent).'
						WHERE ps.ps_ut_id=1 AND ps.ps_id IN ('.implode(',', $ch).') AND pr.pr_id IS NULL';
		//echo $sql."\n";
		$ch = $this->Conn->GetCol($sql);

		if(!count($ch)) {
			$this->errors[] = 'Bçrni nav izvçlçti';
			return false;
		}

		if(is_array($ch))
			foreach($ch as $chd) {
				$sql = 'INSERT INTO parents SET pr_parent='.intval($parent).', pr_child='.intval($chd).', pr_created=UNIX_TIMESTAMP(), pr_modified=UNIX_TIMESTAMP()';
				//echo $sql."\n";
				$this->Conn->Execute($sql);
			}
	}
	
	function actDeleteChild() {
		$parent = $this->App->GetVar('parentId');
		$ids = json_decode($this->App->GetVar('ids'), true);

		if(!is_array($ids) || !count($ids) || !$parent) {
			$this->errors[] = 'Nav datu';
			return false;
		}

		$dummy =& $this->Grid->GetDummy();
		if(array_key_exists($dummy->id_field, $ids))
			$ids = Array($ids);

		$sql = 'SELECT ps_id FROM persons WHERE ps_id='.intval($parent).' AND ps_ut_id=2';
		//echo $sql."\n";
		$parent = $this->Conn->GetOne($sql);
		if(!$parent || !($parent > 0)) {
			$this->errors[] = 'Vecâks izvçlçts nepareizi';
			return false;
		}

		$ch = Array();
		foreach($ids as $chn) {
			if(is_array($chn) && array_key_exists($dummy->id_field, $chn))
				$ch[] = intval($chn[$dummy->id_field]);
		}

		if(!count($ch)) {
			$this->errors[] = 'Bçrni nav izvçlçti';
			return false;
		}

		$sql = 'SELECT ps.ps_id FROM persons ps
						LEFT JOIN `parents` pr ON pr.pr_child=ps.ps_id AND pr.pr_parent='.intval($parent).'
						WHERE ps.ps_ut_id=1 AND ps.ps_id IN ('.implode(',', $ch).') AND pr.pr_id > 0';
		//echo $sql."\n";
		$ch = $this->Conn->GetCol($sql);

		if(!count($ch)) {
			$this->errors[] = 'Bçrni nav izvçlçti';
			return false;
		}

		if(is_array($ch) && count($ch) > 0) {
			$sql = 'DELETE FROM parents WHERE pr_parent='.intval($parent).' AND pr_child IN ('.implode(',', $ch).')';
			//echo $sql."\n";
			$this->Conn->Execute($sql);
		}
	}
}

class UsersItemController extends EItemController {
	function CreateView() {
		$this->AddView(new UsersItemView($this));
	}
	
	function CreateModel() {
		$this->Item = new User(null, $this);
	}
}
?>