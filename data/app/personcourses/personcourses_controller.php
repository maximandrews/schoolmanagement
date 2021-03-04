<?php
include_once(APP_PATH.'/e_controller.php');
include_once(APP_PATH.'/personcourses/personcourses_list.php');
include_once(APP_PATH.'/personcourses/personcourses_view.php');

class PersonCoursesGridController extends EGridController {
	function CreateView() {
		$this->AddView(new PersonCoursesGridView($this));
	}

	function CreateModel() {
		$this->Grid = new PersonCoursesList('', 0, $this);
	}
}

class PersonCoursesItemController extends EItemController {
	function CreateView() {
		$this->AddView(new PersonCoursesItemView($this));
	}
	
	function CreateModel() {
		$this->Item = new PersonCourse(null, $this);
	}
}
?>