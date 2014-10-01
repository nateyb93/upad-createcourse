<?php 
/**
 * upcustomlib.php defines custom functions to use for inserting imported courses into moodle's database
 * 
 */

/* initialization stuff */
defined('MOODLE_INTERNAL') || die ;
require_once("$CFG->libdir/externallib.php");

define( 'COURSE_ALREADY_EXISTS', -1 );
define( 'COURSE_CREATION_FAILED', 0 );
define( 'COURSE_CREATION_SUCCEEDED', 1  );

require_once( "$CFG->libdir/blocklib.php" );
require_once( "$CFG->libdir/pagelib.php" );

error_reporting(E_ALL);
ini_set('display_errors', 'ON');

require_once( __DIR__ . '/upconf.inc.php' );

require_once( __DIR__ . '/timer.php' );

//   define('CLI_SCRIPT', true);

set_time_limit( 3600 );
define( 'UP_DEBUG', true );

// Load libs
// Call moodle path
// TODO: figure out if this is needed; doesn't seem to do anything at the moment.
//$moodlepath = '/var/www/html/moodle';
//require_once( $moodlepath . '/config.php' );

/* end initialization stuff */


/**
 * Retrives database information for the banner database containing course information
 * MAKE SURE YOU CHECK FOR NULL WHEN USING
 * @global type $DB
 * @return type
 */
function get_db_info()
{
    global $DB;
    
    $infoArr = array(
        'dbinfo_protocol',
        'dbinfo_hostname',
        'dbinfo_portno',
        'dbauth_login',
        'dbauth_password',
        'dbauth_sid',
        'importsettings_maxcourses'
    );
    //get database information from moodle's configuration/settings table
    $banner_query = "SELECT name,value FROM {config} WHERE name= ? OR name= ? OR name= ? OR name= ? OR name= ? OR name= ? OR name= ?"; 

    $records = $DB->get_records_sql($banner_query, $infoArr);
    
    if(count($records) == 0) {
        //
        echo "Please provide valid database information by visiting the settings page for this plugin.\n";
    }
    else
    {
        //testing values
        //var_dump($records);
        
        $banner_protocol = $records['dbinfo_protocol']->value;
        $banner_hostname = $records['dbinfo_hostname']->value;
        $banner_portno = $records['dbinfo_portno']->value;
        
        $banner_username = $records['dbauth_login']->value;
        $banner_password = $records['dbauth_password']->value;
        $banner_sid = $records['dbauth_sid']->value;
        
        $banner_maxcourses = $records['importsettings_maxcourses']->value;
        
        //create banner connection string from database rows
        $banner_db = "(DESCRIPTION =
                (ADDRESS =
                    (PROTOCOL = $banner_protocol)
                    (HOST = $banner_hostname)
                    (PORT = $banner_portno)
                )
            (CONNECT_DATA = (SID = $banner_sid))
        )";

        $db_connect_info = array(
            'db' => $banner_db,
            'username' => $banner_username,
            'password' => $banner_password,
            'maxcourses' => $banner_maxcourses
        );

        return $db_connect_info; 
    }
}


/**
 * Gets all course information from the current semester's termcode from the banner database
 * @return type array
 */
function up_import_courses()
{
    // Connect to Banner and retrieve tables
    $query = "SELECT VW_UPM_COURSES.CRN,VW_UPM_COURSES.TERMCODE,VW_UPM_COURSES.COURSE_LONG_NAME,VW_UPM_COURSES.COURSE_SHORT_NAME,VW_UPM_COURSES.START_DATE,VW_UPM_COURSES.END_DATE,TBL_UPM_COURSE_SYNC.CREATED FROM UP_MOODLE.VW_UPM_COURSES LEFT OUTER JOIN UP_MOODLE.TBL_UPM_COURSE_SYNC ON VW_UPM_COURSES.CRN = TBL_UPM_COURSE_SYNC.CRN WHERE TBL_UPM_COURSE_SYNC.CRN is null";
    $dbinfo = get_db_info();
    $oc    = oci_connect( $dbinfo['username'], $dbinfo['password'], $dbinfo['db'] ) or die( "could not connect to banner" );
    $sql   = oci_parse( $oc, $query );

    oci_execute( $sql );


    // Set total rows to be processed. Set to 0 to get all rows.
    //in plugin settings
    $totalrows = $dbinfo['maxcourses']; 

    //get rows from table
    $rows = oci_fetch_all( $sql,$results,0,$totalrows,OCI_FETCHSTATEMENT_BY_ROW );
    
    if( UP_DEBUG )	{
	print "$rows classes queried<br />\n";
    }
    
    return $results;
}


/**
 * Creates a course using the provided information
 * @global type $CFG
 * @global type $DB
 * @global type $oc
 * @param type $courserequestnumber
 * @param type $shortname
 * @param type $fullname
 * @param type $startdate
 * @param type $enddate
 * @param type $termcode
 */
function up_build_course( $courserequestnumber, $shortname, $fullname, $startdate, $enddate, $termcode )
{	
    global $SESSION;
    if( UP_DEBUG ){ 
            print "calling :: build_course( $courserequestnumber, $shortname, $fullname, $startdate, $termcode )<br />\n"; 
    }

    // Retrieve the term information codes fetched earlier from banner and stored in the session variable 'term_data'
    $term_data = $SESSION->term_data;


    // Blow up if false
    if (!$term_data) { 
            print 'FAIL<br/>';
            print 'No term code category has been set in the database.';
            exit;
    }

    // Set the suffix and category
    $course_sn_postfix = $term_data->suffix;
    $form->category = $term_data->category_id;

    if( UP_DEBUG ){	
            print 'SUFFIX:' . $term_data->suffix . '<br/>';
            print 'CATEGORY:' . $term_data->category_id . '<br/>';
    }


    // Set course ID number, Short and Fullname, and Course Summary
    $form->idnumber  = $courserequestnumber;
    $form->shortname = $shortname . ' - ' . $course_sn_postfix;
    $form->fullname  = $form->shortname . ' - ' . $fullname;
    $form->summary   = "Welcome to $fullname." ;


    // Reformat the date. It is returned in the format 29-AUG-05 from banner
    $form->startdate = strtotime( $startdate );

    $enddate = strtotime( $enddate );

    // Calculate the total number of weeks for the course
    $total_weeks =  $enddate - $form->startdate;
    $total_weeks = ceil($total_weeks/86400/7);
    if( $total_weeks < 1 ){ $total_weeks = 1; }
    if( $total_weeks > 16 ){ $total_weeks = 16; }


    $form->format      = "weeks";
    $form->numsections = $total_weeks;  // The total number of weeks in the course 
    $form->showgrades  = 1;

    $form->password       = '';
    $form->guest          = 0;
    $form->cost           = '';
    $form->newsitems      = 5;
    $form->groupmode      = 0;
    $form->groupmodeforce = 0;
    // $form->category  = 0;   // set default category //
    $form->id        = '';
    $form->visible   = 1;
    $form->teacher   = get_string("defaultcourseteacher");
    $form->teachers  = get_string("defaultcourseteachers");
    $form->student   = get_string("defaultcoursestudent");
    $form->students  = get_string("defaultcoursestudents");
    $form->enrollable = 0;
    $form->timecreated = time();
    $form->timemodified  = $form->timecreated;

    $SESSION->courses[] = $form;

 } // end of function build_course //
 
 
 /**
  * Cycles through list of imported courses and builds them for insertion into moodle's database.
  * @param type $courses array containing course information from banner database
  */
 function up_build_courses($courses)
 {
     foreach($courses as $course)
     {
         up_build_course($course['CRN'], $course['COURSE_SHORT_NAME'], $course['COURSE_LONG_NAME'],
                         $course['START_DATE'], $course['END_DATE'], $course['TERMCODE']);
     }
 }


/**
 * Checks to see if a course already exists in the moodle database
 * @global type $CFG
 * @global type $DB
 * @param type $courserequestnumber
 * @param type $category
 * @return boolean
 */
function up_course_exists( $courserequestnumber, $category )
{
    global $CFG, $DB;
    if(  strlen($courserequestnumber) == 0  )
    {
        return false;
    }

    if ( UP_DEBUG ){ 
        "calling: course_exists( $courserequestnumber )<br />\n"; 
    }

    if (  $exists = $DB->get_records_select( 'course', "idnumber='$courserequestnumber' AND category='$category'" )  )
    {
        return true;
    }
    else
    {
        return false;
    }

}// end of function up_course_exists //
  

/**
 * Attempts to insert a course into the moodle database
 * @param type $course
 * @return string
 */
function up_insert_course($course)
{
    global $DB, $oc;
    $courserequestnumber = $course->idnumber;
    $category = $course->category;
    // Check if the course exists
    if( up_course_exists( $courserequestnumber, $category ) )
    {
        add_to_log( '1', 'course', 'error', '', "Course already exists: BannerID: $courserequestnumber", '','4');

        // Mark course as added in banner
        $totalrows = 0;
        $query = "INSERT INTO UP_MOODLE.TBL_UPM_COURSE_SYNC VALUES ('$courserequestnumber','$termcode','Y')";
        $sql   = oci_parse( $oc, $query );

        oci_execute( $sql );

        $rows = oci_fetch_all( $sql, $results, 0, $totalrows, OCI_FETCHSTATEMENT_BY_ROW );

        if ($rows == false) {
            print 'Insert to TBL_UPM_COURSE_SYNC failed. Sync may be broken.';
            exit;
        }

      return COURSE_ALREADY_EXISTS;
    }

    // Set up new course
    //insert course
    if( $newcourseid = $DB->insert_record('course', $form)  ) 
    { 

        if ( UP_DEBUG )
        { 
          print "New Course ID is: " . $newcourseid . "<br />\n"; 
        }

 //       $section = NULL;
 //       $section->course = $newcourseid;   // Create a default section.
 //       $section->section = 0;
 //       $section->id = $DB->insert_record("course_sections", $section);


        $course = $DB->get_record('course', array('id'=>$newcourseid));

        // Setup the blocks
        blocks_add_default_course_blocks($course);

        $section = new stdClass();
        $section->course        = $newcourseid;   // Create a default section.
        $section->section       = 0;
        $section->summaryformat = FORMAT_HTML;
        $DB->insert_record('course_sections', $section);

        fix_course_sortorder();

        // Mark course as added in banner
        $totalrows = 0;
        $query = "INSERT INTO UP_MOODLE.TBL_UPM_COURSE_SYNC VALUES ('$courserequestnumber','$termcode','Y')";
        $sql   = oci_parse( $oc, $query );

        oci_execute( $sql );

        $rows = oci_fetch_all( $sql, $results, 0, $totalrows, OCI_FETCHSTATEMENT_BY_ROW );

        if ($rows = false) {
                print 'Insert to TBL_UPM_COURSE_SYNC failed. Sync may be broken.';
                exit;
        }
        
        return COURSE_CREATION_SUCCEEDED;

    } else {
            add_to_log( '1', 'course', 'error', '', "Creating: $shortname (BANID $courserequestnumber)", '','4');
            return COURSE_CREATION_FAILED;
    } // end of else on if (  $newcourseid = $DB->insert_record('course', $form)  )
}


/**
 * Create each course in the moodle database
 * @param type $courses
 */
function up_insert_courses(){
    global $SESSION;
    
    //counters for created and failed courses
    $courses_created = 0;
    $courses_failed = 0;
    
    
    // Start timer for script
    $ts_timer = new Timer();
    $ts_timer->start();
    
//    //display confirmation prompt in the case that the user has initiated the course creation
//    if ( $_GET['go'] != 'yes' ){
//
//	print '<div align="center">';
//	print '<font color="red">Do you really want to import courses?</font><br />';
//	print $rows . ' courses waiting to be imported<br />';
//	print 'CONFIG INFORMATION :<br />'; 
//	print "moodlepath : $moodlepath <br />";
//	print "database   : $CFG->dbname<br />";
//	print 'debug: ' . UP_DEBUG . '<br />';
//
//	print '<form action="' . $PHP_SELF . '"><br />';
//	print '<input type="text" name="go" value="" size="4"> (enter yes to continue)<br />';
//	print '<input type="submit" value="Go &gt;&gt;">';
//	print '</form>';
//	print '</div>';
//	exit;	
//    }
    
    $j=0; // Start Counter //
    
    //retrieve list of courses from session
    $courses = $SESSION->courses;
    
    //cycle through each of the courses and get their information
    foreach( $courses as $data ){
        
        if( UP_DEBUG )
        {
            print "<br /><br />ROW: $j<br />";
            print "COURSEID : " . $data->idnumber . '<br />';
            print "SHORTNAME : " . $data->shortname . '<br />';
            print "FULLNAME : " . $data->fullname . '<br />';
            print "STARTDATE : " . $data->startdate . '<br />';
            print "ENDDATE : " . $data->enddate . '<br />';
            print "TERMCODE : " . $data->termcode . '<br />';
        }
        $createstatus = up_insert_course($data);

        switch( $createstatus ){
                case COURSE_ALREADY_EXISTS:
                        if(UP_DEBUG){  print "COURSE ALREADY EXISTS<br />";  }
                break;
                case COURSE_CREATION_FAILED:
                        if (UP_DEBUG){  
                                print "COURSE CREATION FAILED<br />";
                                $courses_failed++;
                          }
                break;
                case COURSE_CREATION_SUCCEEDED:
                        if (UP_DEBUG){  
                                print "COURSE CREATION SUCCEDED<br />";  
                                $courses_created++;	
                        }
                break;
                case true:
                        print "Testing!";
                break;
        }

        $j++;

     }
     
     $ts_timer->finish();
     // end foreach( $results as $data ) //

    echo '<br />action took: ' . $ts_timer->getTime() . " seconds.<br />" . "<br /> Courses created: " . $courses_created . " | Courses failed: " . $courses_failed;
    $SESSION->courses = array();
}
?>