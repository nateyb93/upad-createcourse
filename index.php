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
global $OUTPUT;

/* configuration stuff */
require_login();

require_once __DIR__ . '/createcourse_form.php';

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

//conditionally initialize the session variable for this plugin.
if(empty($SESSION->tool_createcourse))
{
	$SESSION->tool_createcourse = new stdClass();
}

//check for data stuff
if($createcourseform->is_cancelled()) {
    //handle form cancel operation
    //pretty much want to do nothing here; cease operations
}
//has posted data; this would only be accessible from course creation form
//TODO: spice this up. add form validation to make sure all forms are filled
else if($postData = $createcourseform->get_data())
{
    //check the posted data for the termcode
    if(!empty($postData->termcode))
    {
        //set term data to form data and set session variables to access in other functions from other pages
        $term_data = new stdClass();
        $term_data->termcode = $postData->termcode;
        $term_data->suffix = $postData->suffix;
        $term_data->categoryid = $postData->categoryid;
        $term_data->hidden = $postData->hideterm;

        //make sure the category exists.
        up_category_exists($term_data->categoryid) or die("Category $term_data->categoryid does not exist in Moodle database. Add it or choose a different category.");


        //set session variables
        //term_data contains termcode, suffix, and category_id entered into the form
        //Courses contains built courses
        //Imports contains raw banner import data
        $SESSION->tool_createcourse->term_data = $term_data;
        $SESSION->tool_createcourse->courses = array();
        $SESSION->tool_createcourse->imports = up_import_courses();

        //make sure we're getting data from the form
        /*
        if( UP_DEBUG )
        {
            var_dump($term_data);
        }
        */

        //render the page
        echo $renderer->index_page($createcourseform);

        echo 'Starting...';

        //perform course insertion
        //TODO: look to move this elsewhere
        if(!up_term_exists($term_data->termcode, $term_data->suffix, $term_data->categoryid))
        {
        	$DB->insert_record('tool_createcourse', $SESSION->tool_createcourse->term_data) or die("Error");
        }
        else
        {
        	die ("Term data for the term you specified already exists. Please enter different data");
        }


        //builds and inserts courses into moodle database
        up_build_courses($SESSION->tool_createcourse->imports);
        up_insert_courses($SESSION->tool_createcourse->courses);//uses session variable, does not require a parameter

        echo 'Success!';

    }
}
//base case, root page
else
{
    //set current page and render base index page.
    echo $renderer->index_page($createcourseform);
}

?>