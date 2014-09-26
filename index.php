<?php
require('../../../config.php');


global $CFG;
global $PAGE;
global $SESSION;

require_login();


require_once __DIR__ . '/createcourse_form.php';
require_once __DIR__ . '/confirmation_form.php';

error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once($CFG->libdir . '/blocklib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/accesslib.php');
require_once($CFG->libdir . '/weblib.php');
require_once(__DIR__ . '/upcustomlib.php');

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




if($createcourseform->is_cancelled()) {
    //handle form cancel operation
} else if($postData = $createcourseform->get_data()) {
    
    //check to make sure some form data got submitted; the least we need is a termcode.
    if(!empty($postData->termcode))
    {
        $term_data = array(
            'termcode' => $postData->termcode,
            'suffix' => $postData->suffix,
            'category_id' => $postData->categoryid
        );
        $_SESSION['term_data'] = $term_data;
        $_SESSION['courses'] = array();
        
        $courses = up_import_courses();
        
        $confirmationform = new confirmationform();
        echo $renderer->index_page($confirmationform, $renderer::INDEX_PAGE_CONFIRMATION_STEP);
        
    }
    //handle data submitted with form
    
} else {
    echo $renderer->index_page($createcourseform, $renderer::INDEX_PAGE_IMPORT_STEP);
    
}


?>