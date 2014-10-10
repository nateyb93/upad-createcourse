<?php
/*
 * index.php defines the entry point for the createcourse admin tool.
 * Each different step of the course import process can be accessed from this file
 * Course import/creation functions called here
 */
require('../../../config.php');
global $CFG;
global $PAGE;
global $SESSION;
global $DB;

/* configuration stuff */
require_login();

require_once __DIR__ . '/createcourse_form.php';
require_once __DIR__ . '/confirmation_form.php';
require_once __DIR__ . '/success_form.php';

error_reporting(E_ALL);
ini_set('display_errors', 'On');

require_once($CFG->libdir . '/blocklib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/accesslib.php');
require_once($CFG->libdir . '/weblib.php');
require_once(__DIR__ . '/upcustomlib.php');

admin_externalpage_setup('tool_createcourse_create');
/* end configuration stuff */

//base index page rendering
$createcourseform = new createcourse_form();
$renderer = $PAGE->get_renderer('tool_createcourse');


//check for data stuff
if($createcourseform->is_cancelled()) {
    //handle form cancel operation
}
//has posted data; this would only be accessible from course creation form
//TODO: spice this up. add form validation to make sure all forms are filled
else if($postData = $createcourseform->get_data())
{    
    if(!empty($postData->confirmimport))
    {
        //insert term data into moodle database
        $DB->insert_record('tool_createcourse', $SESSION->term_data);
        up_build_courses($SESSION->imports);
        up_insert_courses();
        
        
        //render success page
        $step = $renderer::INDEX_PAGE_SUCCESS_STEP;
       
        $successform = new successform();
        echo $renderer->index_page($successform, $step);
    }
    //check the posted data for the termcode
    else if(!empty($postData->termcode))
    {
        //set term data to form data and set session variables to access in other functions from other pages
        $term_data = new stdClass();
        $term_data->termcode = $postData->termcode;
        $term_data->suffix = $postData->suffix;
        $term_data->categoryid = $postData->categoryid;
        
        //set session variables
        $SESSION->term_data = $term_data;
        $SESSION->courses = array();
        $SESSION->imports = up_import_courses();
        $step = $renderer::INDEX_PAGE_CONFIRMATION_STEP;

        //make sure we're getting data from the form
        /*
        if( UP_DEBUG )
        {
            var_dump($term_data); 
        } 
        */
        
        //render the page
        echo $renderer->index_page($createcourseform, $step);    
    }
}
//base case, root page
else
{
    //set current page and render base index page.
    $step = $renderer::INDEX_PAGE_IMPORT_STEP;
    echo $renderer->index_page($createcourseform, $step);
}

?>