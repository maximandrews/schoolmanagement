<?php
include_once(APP_PATH.'/e_controller.php');
include_once(APP_PATH.'/courses/courses_list.php');
include_once(APP_PATH.'/courses/courses_view.php');

class CoursesGridController extends EGridController {
	public $errors = Array();
	function CreateView() {
		$this->AddView(new CoursesGridView($this));
	}

	function CreateModel() {
		$this->Grid = new CoursesList('', 0, $this);
	}

	function actCreate($notskip=0) {
		if(!defined('ADMIN') && !$notskip)
			return false;
		
		$ids = json_decode($this->App->GetVar('ids'), true);
		$anItem =& $this->Grid->GetDummy(); // Model -> Course -> courses_list.php
		if(array_key_exists($anItem->id_field, $ids))
			$ids = Array($ids);

		foreach ($ids as $rec) {
			$anItem->SetFieldsFromHash($rec);
			if(!$anItem->Create()) {
				$this->errors[] = $anItem->GetErrors();
			}
		}
	}

	function actUpdate($notskip=0) {
		if(!defined('ADMIN') && !$notskip)
			return false;
		
		$ids = json_decode($this->App->GetVar('ids'), true);
		$anItem =& $this->Grid->GetDummy(); // Model -> Course -> courses_list.php
		if(array_key_exists($anItem->id_field, $ids))
			$ids = Array($ids);

		foreach ($ids as $rec) {
			if(array_key_exists($anItem->id_field, $rec) && $rec[$anItem->id_field] > 0) $anItem->Load($rec[$anItem->id_field]);
			$anItem->SetFieldsFromHash($rec);
			if(!$anItem->Update()) {
				$this->errors[] = $anItem->GetErrors();
			}
		}
	}
}
//$this->Item->SetFieldsFromHash($this->App->GetVars());
//return $this->Item->Create();
class CoursesItemController extends EItemController {
	function CreateView() {
		$this->AddView(new CoursesItemView($this));
	}
	
	function CreateModel() {
		$this->Item = new Course(null, $this);
	}
}
?>