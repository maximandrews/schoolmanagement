	<?php
$app =& KernelApplication::Instance();

// Configure Options
$app->RegisterClass('config', 'ConfOptionsGridController', APP_PATH.'/config/options/confoptions_controller.php');
$app->RegisterClass('config', 'ConfOptionsItemController', APP_PATH.'/config/options/confoptions_controller.php');

$app->RegisterClass('usersgrid', 'UsersGridController', APP_PATH.'/users/users_controller.php');
$app->RegisterClass('classaddchild', 'UsersGridController', APP_PATH.'/users/users_controller.php');
$app->RegisterClass('classregchildren', 'UsersGridController', APP_PATH.'/users/users_controller.php');
$app->RegisterClass('classparents', 'UsersGridController', APP_PATH.'/users/users_controller.php');
$app->RegisterClass('parentschildren', 'UsersGridController', APP_PATH.'/users/users_controller.php');
$app->RegisterClass('parentschildrenadd', 'UsersGridController', APP_PATH.'/users/users_controller.php');
$app->RegisterClass('childparents', 'UsersGridController', APP_PATH.'/users/users_controller.php');
$app->RegisterClass('childparentsadd', 'UsersGridController', APP_PATH.'/users/users_controller.php');
$app->RegisterClass('usersitem', 'UsersItemController', APP_PATH.'/users/users_controller.php');

$app->RegisterClass('classesgrid', 'ClassesGridController', APP_PATH.'/classes/classes_controller.php');
$app->RegisterClass('classpupiladd', 'ClassesGridController', APP_PATH.'/classes/classes_controller.php');
$app->RegisterClass('classesitem', 'ClassesItemController', APP_PATH.'/classes/classes_controller.php');
$app->RegisterClass('classchildrenadd', 'ClassesGridController', APP_PATH.'/classes/classes_controller.php');

$app->RegisterClass('classescoursesgrid', 'ClassCoursesGridController', APP_PATH.'/classcourses/classcourses_controller.php');
$app->RegisterClass('classescourses', 'ClassCoursesGridController', APP_PATH.'/classcourses/classcourses_controller.php');
$app->RegisterClass('classescoursesitem', 'ClassCoursesItemController', APP_PATH.'/classcourses/classcourses_controller.php');

$app->RegisterClass('pretendentsgrid', 'PretendentsGridController', APP_PATH.'/pretendents/pretendents_controller.php');
$app->RegisterClass('pretendentsitem', 'PretendentsItemController', APP_PATH.'/pretendents/pretendents_controller.php');

$app->RegisterClass('personcoursesgrid', 'PersonCoursesGridController', APP_PATH.'/personcourses/personcourses_controller.php');
$app->RegisterClass('personcoursesadd', 'PersonCoursesGridController', APP_PATH.'/personcourses/personcourses_controller.php');
$app->RegisterClass('personcoursesitem', 'PersonCoursesItemController', APP_PATH.'/personcourses/personcourses_controller.php');

$app->RegisterClass('coursesgrid', 'CoursesGridController', APP_PATH.'/courses/courses_controller.php');
$app->RegisterClass('levelcourses', 'CoursesGridController', APP_PATH.'/courses/courses_controller.php');
$app->RegisterClass('coursesitem', 'CoursesItemController', APP_PATH.'/courses/courses_controller.php');

$app->RegisterClass('schedulegrid', 'ScheduleGridController', APP_PATH.'/schedule/schedule_controller.php');
$app->RegisterClass('scheduleitem', 'ScheduleItemController', APP_PATH.'/schedule/schedule_controller.php');
?>