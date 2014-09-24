<?php 

defined('MOODLE_INTERNAL') || die ;
require_once("$CFG->libdir/externallib.php");

define( 'COURSE_ALREADY_EXISTS', -1 );
define( 'COURSE_CREATION_FAILED', 0 );
define( 'COURSE_CREATION_SUCCEEDED', 1  );

require_once( "$CFG->libdir/blocklib.php" );
require_once( "$CFG->libdir/pagelib.php" );


/**
 * Retrives database information for the banner database containing course information
 * MAKE SURE YOU CHECK FOR NULL WHEN USING
 * @global type $DB
 * @return type
 */
function get_db_info()
{
    global $DB;
    
    $banner_query = "SELECT name, value FROM {mdl_config}"
        . "WHERE name='dbinfo_protocol'OR name='dbinfo_hostname' OR name='dbinfo_portno'"
        . "OR name='dbauth_login' OR name='dbauth_password'"; 

    $records = $DB->get_records_sql($banner_query, null, $limitfrom=0, $limitnum=0);
    
    $banner_protocol = $records(0)->value;
    $banner_hostname = $records(1)->value;
    $banner_portno = $records(2)->value;
    
    if($banner_protocol == null || $banner_hostname == null || $banner_portno == null) {
        die("Please provide valid database information by visiting the settings page for this plugin.\n");

    }
    
    $banner_username = $records(3)->value;
    $banner_password = $records(4)->value;
    $banner_maxcourses = $records(5)->value;
    $banner_db = "(DESCRIPTION =
            (ADDRESS =
                (PROTOCOL = $banner_protocol)
                (HOST = $banner_hostname)
                (PORT = $banner_portno)
            )
        (CONNECT_DATA = (SID = PRD))
    )";
    
    $db_connect_info = array(
        'db' => $banner_db,
        'username' => $banner_username,
        'password' => $banner_password,
        'maxcourses' => $banner_maxcourses
    );
    
    return $db_connect_info;
}

/*
*	Check if a course exists in the moodle database.
*	@param int $idnumber
*	@return boolean
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



/*
*	Create a course
*	@param string $courserequestnumber
*	@param string $shortname
*	@param string $fullname
*	@param date	  $startdate	
*/

function up_create_course( $courserequestnumber, $shortname, $fullname, $startdate, $enddate, $termcode ){

	global $CFG, $DB, $oc;
	
	if( UP_DEBUG ){ 
		print "calling :: create_course( $courserequestnumber, $shortname, $fullname, $startdate, $termcode )<br />\n"; 
	}

        $course_sn_postfix = new stdClass;

	// Get the category and suffix for the courses term code from mdl_up_termcode in the moodle database
        $term_data = $DB->get_records_list( 'up_termcode', 'termcode', array($termcode), '', 'category_id, suffix' );

	
	// Blow up if false
	if (!$term_data) { 
		print 'FAIL<br/>';
		print 'No term code category has been set in the database.';
		exit;
	}

	// Set the suffix and category
	foreach ($term_data as $course_data) { 
		$course_sn_postfix = $course_data->suffix;
		$form->category = $course_data->category_id;
		if( UP_DEBUG ){	
			print 'SUFFIX:' . $course_data->suffix . '<br/>';
			print 'CATEGORY:' . $course_data->category_id . '<br/>';
		}
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
	

	// Check if the course exists
	if( up_course_exists( $courserequestnumber, $form->category ) )
	{
		add_to_log( '1', 'course', 'error', '', "Course already exists: BannerID: $courserequestnumber", '','4');

	// Mark course as added in banner
		
		$totalrows = 0;
		$query = "INSERT INTO UP_MOODLE.TBL_UPM_COURSE_SYNC VALUES ('$courserequestnumber','$termcode','Y')";
   		$sql   = OCIParse( $oc, $query );

  		OCIExecute( $sql );

		$rows = OCIFetchstatement( $sql, $results, 0, $totalrows, OCI_FETCHSTATEMENT_BY_ROW );

		if ($rows = false) {
			print 'Insert to TBL_UPM_COURSE_SYNC failed. Sync may be broken.';
			exit;
		}

	  return COURSE_ALREADY_EXISTS;
	}

	
	// Set up new course
	if( $newcourseid = $DB->insert_record('course', $form)  ) 
	{ 

		if ( UP_DEBUG )
                { 
		  print "New Course ID is: " . $newcourseid . "<br />\n"; 
		}

//		$section = NULL;
//		$section->course = $newcourseid;   // Create a default section.
//		$section->section = 0;
//		$section->id = $DB->insert_record("course_sections", $section);

		
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


 } // end of function create_course //


?>