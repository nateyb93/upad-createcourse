<?php
require('../../../config.php');


global $CFG;
global $PAGE;
global $SESSION;

require_login();


require_once __DIR__ . '/createcourse_form.php';

error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once($CFG->libdir . '/blocklib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/accesslib.php');
require_once($CFG->libdir . '/weblib.php');

admin_externalpage_setup('tool_createcourse_create');
//
//$option = optional_param('option', NULL, PARAM_TEXT);
//if(!$option) {
//    if(optional_param('clearselection', false, PARAM_TEXT)) {
//        $option = 'clearselection';
//    } else if(optional_param('createcourse', false, PARAM_TEXT)) {
//        $option = 'createcourse';
//    }
//}

$createcourseform = new createcourse_form();
$renderer = $PAGE->get_renderer('tool_createcourse');
$data = $createcourseform->get_data();

echo $renderer->index_page($createcourseform);


if($createcourseform->is_cancelled()) {
    //handle form cancel operation
} else if($fromform = $createcourseform->get_data()) {
    //in this case you process validated data. $mform->get_data() returns data posted in form.
} else {
    //$toform;
    //$createcourseform->set_data($toform);
    
}


?>