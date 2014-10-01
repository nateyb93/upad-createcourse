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
    //check the posted data for the termcode
    if(!empty($postData->termcode))
    {
        //set term data to form data and set session variables to access in other functions from other pages
        $term_data = array(
            'termcode' => $postData->termcode,
            'suffix' => $postData->suffix,
            'category_id' => $postData->categoryid
        );
        $SESSION->term_data = $term_data;
        $SESSION->courses = array();
        $SESSION->imports = up_import_courses();
        $step = $renderer::INDEX_PAGE_CONFIRMATION_STEP;
        $SESSION->currentpage = $step;
        
        //create confirmation form to add extra layer of security.
        $confirmationform = new confirmationform();

        //make sure we're getting data from the form
        /*
        if( UP_DEBUG )
        {
            var_dump($term_data); 
        } 
        */
        
        //render the page
        echo $renderer->index_page($confirmationform, $step);      
    }
}
//confirmation page
else if($SESSION->currentpage == $renderer::INDEX_PAGE_CONFIRMATION_STEP) {
    //retrieve the imported classes from the session variables, build the courses, and insert the courses into the database.
    $imports = $SESSION->imports;
    up_build_courses($imports);
    up_insert_courses();
    
    //set current page
    $step = $renderer::INDEX_PAGE_SUCCESS_STEP;
    $SESSION->currentpage = $step;
    
    //render page
    $successform = new successform();
    echo $renderer->index_page($successform, $step);
}
//other cases
else
{
    //set current page and render base index page.
    $step = $renderer::INDEX_PAGE_IMPORT_STEP;
    $SESSION->currentpage = $step;
    echo $renderer->index_page($createcourseform, $step);
    
}

?>