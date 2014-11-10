<?php
/**
 * upcustomlib.php defines custom functions to use for inserting imported courses into moodle's database
 *
 */

/* initialization stuff */
defined ( 'MOODLE_INTERNAL' ) || die ( 'moodle_internal not defined' );

define ( 'COURSE_ALREADY_EXISTS', - 1 );
define ( 'COURSE_CREATION_FAILED', 0 );
define ( 'COURSE_CREATION_SUCCEEDED', 1 );

require_once ("$CFG->libdir/externallib.php");
require_once ("$CFG->libdir/blocklib.php");
require_once ("$CFG->libdir/pagelib.php");
require_once ("$CFG->libdir/enrollib.php");

require_once (__DIR__ . '/upconf.inc.php');

error_reporting ( E_ALL );
ini_set ( 'display_errors', 'ON' );

// define('CLI_SCRIPT', true);

set_time_limit ( 3600 );
define ( 'UP_DEBUG', true );

// Load libs
// Call moodle path
// TODO: figure out if this is needed; doesn't seem to do anything at the moment.
// $moodlepath = '/var/www/html/moodle';
// require_once( $moodlepath . '/config.php' );

/* end initialization stuff */

/**
 * Retrives database information for the banner database containing course information
 *
 * @global type $DB
 * @return stdObject containing database information
 */
function get_db_info() {
	global $DB;

	$infoArr = array (
			'dbinfo_protocol',
			'dbinfo_hostname',
			'dbinfo_portno',
			'dbauth_login',
			'dbauth_password',
			'dbauth_sid',
			'importsettings_maxcourses'
	);
	// get database information from moodle's configuration/settings table
	$banner_query = "SELECT name,value FROM {config} WHERE name= ? OR name= ? OR name= ? OR name= ? OR name= ? OR name= ? OR name= ?";

	$records = $DB->get_records_sql ( $banner_query, $infoArr );

	if (count ( $records ) == 0) {
		//
		echo "Please provide valid database information by visiting the settings page for this plugin.\n";
	} else {
		// testing values
		// var_dump($records);

		$banner_protocol = $records ['dbinfo_protocol']->value;
		$banner_hostname = $records ['dbinfo_hostname']->value;
		$banner_portno = $records ['dbinfo_portno']->value;

		$banner_username = $records ['dbauth_login']->value;
		$banner_password = $records ['dbauth_password']->value;
		$banner_sid = $records ['dbauth_sid']->value;

		$banner_maxcourses = $records ['importsettings_maxcourses']->value;

		// create banner connection string from database rows
		$banner_db = "(DESCRIPTION =
                (ADDRESS =
                    (PROTOCOL = $banner_protocol)
                    (HOST = $banner_hostname)
                    (PORT = $banner_portno)
                )
            (CONNECT_DATA = (SID = $banner_sid))
        )";

		$db_connect_info = new stdClass();

		$db_connect_info->db = $banner_db;
		$db_connect_info->username = $banner_username;
		$db_connect_info->password = $banner_password;
		$db_connect_info->maxcourses = $banner_maxcourses;

		return $db_connect_info;
	}
}

/**
 * Gets all course information from the current semester's termcode from the banner database
 *
 * @return type array containing imported courses
 */
function up_import_courses() {

	// Connect to Banner and retrieve tables
	$query = "SELECT VW_UPM_COURSES.CRN,VW_UPM_COURSES.TERMCODE,VW_UPM_COURSES.COURSE_LONG_NAME,VW_UPM_COURSES.COURSE_SHORT_NAME,VW_UPM_COURSES.START_DATE,VW_UPM_COURSES.END_DATE,TBL_UPM_COURSE_SYNC.CREATED FROM UP_MOODLE.VW_UPM_COURSES LEFT OUTER JOIN UP_MOODLE.TBL_UPM_COURSE_SYNC ON VW_UPM_COURSES.CRN = TBL_UPM_COURSE_SYNC.CRN WHERE TBL_UPM_COURSE_SYNC.CRN is null";
	$dbinfo = get_db_info ();
	$oc = oci_connect ( $dbinfo->username, $dbinfo->password, $dbinfo->db ) or die ( "could not connect to banner" );
	$sql = oci_parse ( $oc, $query );

	oci_execute ( $sql );

	// Set total rows to be processed. Set to 0 to get all rows.
	// in plugin settings
	$totalrows = $dbinfo->maxcourses;

	// get rows from table
	$rows = oci_fetch_all ( $sql, $results, 0, $totalrows, OCI_FETCHSTATEMENT_BY_ROW );

	return $results;
}

/**
 * Creates a course using the provided information
 *
 * @global type $CFG
 * @global type $DB
 * @global type $oc
 * @param type $courserequestnumber
 *        	CRN for course to build
 * @param type $shortname
 *        	course shortname
 * @param type $fullname
 *        	course full name
 * @param type $startdate
 *        	course start date
 * @param type $enddate
 *        	course end date
 * @param type $termcode
 *        	course term code
 */
function up_build_course($courserequestnumber, $shortname, $fullname, $startdate, $enddate, $termcode) {
	global $DB;

	$form = new stdClass ();

	if (UP_DEBUG) {
		print "calling :: build_course( $courserequestnumber, $shortname, $fullname, $startdate, $termcode )<br />\n";
	}

	// Retrieve the term information codes fetched from banner
	$term_data = $DB->get_records_list('tool_createcourse', 'termcode', array($termcode), '', 'category_id, suffix' );

	// Blow up if false
	if (! $term_data) {
		print 'FAIL<br/>';
		print 'No term code category has been set in the database.';
		exit ();
	}

	// Set the suffix and category
	$course_sn_postfix = $term_data->suffix;
	$form->category = $term_data->categoryid;

	if (UP_DEBUG) {
		print 'SUFFIX:' . $term_data->suffix . '<br/>';
		print 'CATEGORY:' . $term_data->categoryid . '<br/>';
	}

	// Set course ID number, Short and Fullname, and Course Summary
	$form->idnumber = $courserequestnumber;
	$form->shortname = $shortname . ' - ' . $course_sn_postfix;
	$form->fullname = $form->shortname . ' - ' . $fullname;
	$form->summary = "Welcome to $fullname.";
	$form->termcode = $termcode;

	// Reformat the date. It is returned in the format 29-AUG-05 from banner
	$form->startdate = strtotime ( $startdate );
	$enddate = strtotime ( $enddate );
	$form->enddate = $enddate;

	// Calculate the total number of weeks for the course
	$total_weeks = $enddate - $form->startdate;
	$total_weeks = ceil ( $total_weeks / 86400 / 7 );
	if ($total_weeks < 1) {
		$total_weeks = 1;
	}
	if ($total_weeks > 16) {
		$total_weeks = 16;
	}

	$form->format = "weeks";
	$form->numsections = $total_weeks; // The total number of weeks in the course
	$form->showgrades = 1;

	$form->password = '';
	$form->guest = 0;
	$form->cost = '';
	$form->newsitems = 5;
	$form->groupmode = 0;
	$form->groupmodeforce = 0;
	// $form->category = 0; // set default category //
	$form->id = '';
	$form->visible = 1;
	$form->teacher = get_string ( "defaultcourseteacher" );
	$form->teachers = get_string ( "defaultcourseteachers" );
	$form->student = get_string ( "defaultcoursestudent" );
	$form->students = get_string ( "defaultcoursestudents" );
	$form->enrollable = 0;
	$form->timecreated = time ();
	$form->timemodified = $form->timecreated;

	return $form;
} // end of function build_course //


/**
 * Cycles through list of imported courses and builds them for insertion into moodle's database.
 *
 * @param type $courses
 *        	array containing course information from banner database
 */
function up_build_courses($courses) {
	$built_courses = array();
	foreach ( $courses as $course ) {
		$built_courses[] = up_build_course ( $course ['CRN'], $course ['COURSE_SHORT_NAME'], $course ['COURSE_LONG_NAME'], $course ['START_DATE'], $course ['END_DATE'], $course ['TERMCODE'] );
	}

	return $built_courses;
}

/**
 * Checks to see if a category exists in the moodle database
 * @param unknown $categoryid
 */
function up_category_exists($categoryid) {
	global $DB;

	if($exists = $DB->get_records_select('course_categories', "id='$categoryid'"))
	{
		return true;
	}
	else
	{
		return false;
	}

}

/**
 * Checks to see if term data exists for the specified paramaters
 * @param unknown $termcode
 * @param unknown $suffix
 * @param unknown $categoryid
 */
function up_term_exists($termcode, $suffix, $categoryid)
{
	global $DB;

	$sql = "SELECT * FROM {tool_createcourse} WHERE termcode=$termcode OR suffix='$suffix' OR categoryid=$categoryid";

	if($rows = $DB->get_records_sql($sql, null, null, 1))
	{
		return true;
	}
	else
	{
		return false;
	}
}

/**
 * Checks to see if a course already exists in the moodle database
 *
 * @global type $CFG
 * @global type $DB
 * @param type $courserequestnumber
 * @param type $category
 * @return boolean
 */
function up_course_exists($courserequestnumber, $category) {
	global $CFG, $DB;
	if (strlen ( $courserequestnumber ) == 0) {
		return false;
	}

	if (UP_DEBUG) {
		"calling: course_exists( $courserequestnumber )<br />\n";
	}

	if ($exists = $DB->get_records_select ( 'course', "idnumber='$courserequestnumber' AND category='$category'" )) {
		return true;
	} else {
		return false;
	}
} // end of function up_course_exists //

/**
 * Inserts a category into the moodle database
 * @param unknown $categoryid category to insert
 * @param string $description description of the category to insert
 */
function up_insert_category($categoryid, $description = null)
{
	global $DB;

	//create record and insert it into the database
	//categoryid is the only field we need, the rest should be generated automatically.
	$record = new stdClass();
	$record->idnumber = $categoryid;
	$record->description = $description;

	try{
		$DB->insert_record('course_categories', $record, false);
	}
	catch(coding_exception $e)
	{
		print $e->getMessage();
	}

}

/**
 * Attempts to insert a course into the moodle database
 *
 * @param type $course
 * @return string
 */
function up_insert_course($course) {
	global $DB;
	$courserequestnumber = $course->idnumber;
	$category = $course->category;


	$dbinfo = get_db_info();
	$oc = new stdClass();

	try{
		$oc = oci_connect($dbinfo->username, $dbinfo->password, $dbinfo->db);
	}
	catch(exception $e){
		print $e->getMessage();
	}


	// Check if the course exists
	if (up_course_exists ( $courserequestnumber, $category )) {
		add_to_log ( '1', 'course', 'error', '', "Course already exists: BannerID: $courserequestnumber", '', '4' );

		// Mark course as added in banner
		$totalrows = 0;
		$query = "INSERT INTO UP_MOODLE.TBL_UPM_COURSE_SYNC VALUES ('$courserequestnumber','$course->termcode','Y')";
		$sql = oci_parse ( $oc, $query );

		oci_execute ( $sql );

		$results = array();
		$rows = oci_fetch_all ( $sql, $results, 0, $totalrows, OCI_FETCHSTATEMENT_BY_ROW );

		if ($rows = false) {
			print 'Insert to TBL_UPM_COURSE_SYNC failed. Sync may be broken.';
			exit ();
		}

		return COURSE_ALREADY_EXISTS;
	}

	// Set up new course
	// insert course
	else if ($newcourseid = $DB->insert_record ( 'course', $course )) {

		if (UP_DEBUG) {
			print "New Course ID is: " . $newcourseid . "<br />\n";
		}

		//get the newly inserted course so we can add the default blocks
		$newMoodleCourse = $DB->get_record ( 'course', array (
				'id' => $newcourseid
		) );


		$data = $course;
		$course = course_get_format($newcourseid)->get_course();
		// Setup the blocks
		blocks_add_default_course_blocks ( $newMoodleCourse );

		//set section information and insert into the section database
		$section = new stdClass ();
		$section->course = $newcourseid; // Create a default section.
		$section->section = 0;
		$section->summaryformat = FORMAT_HTML;

		try{
			$DB->insert_record ( 'course_sections', $section );
		}
		catch(coding_exception $e)
		{
			print $e->getMessage();
		}


		fix_course_sortorder ();

		up_add_enrol_plugin('manual', true, $course, $data);

		// Mark course as added in banner
		$totalrows = 0;
		$query = "INSERT INTO UP_MOODLE.TBL_UPM_COURSE_SYNC VALUES ('$courserequestnumber','$data->termcode','Y')";
		$sql = oci_parse ( $oc, $query );

		oci_execute ( $sql );

		$results = array();
		$rows = oci_fetch_all ( $sql, $results, 0, $totalrows, OCI_FETCHSTATEMENT_BY_ROW );

		if ($rows = false) {
			print 'Insert to TBL_UPM_COURSE_SYNC failed. Sync may be broken.';
			exit ();
		}


		$event = \core\event\course_created::create(array(
				'objectid' => $course->id,
				'context' => context_course::instance($course->id),
				'other' => array('shortname' => $course->shortname,
						'fullname' => $course->fullname)
		));
		$event->trigger();

		return COURSE_CREATION_SUCCEEDED;
	} else {
		add_to_log ( '1', 'course', 'error', '', "Creating: $shortname (BANID $courserequestnumber)", '', '4' );
		return COURSE_CREATION_FAILED;
	} // end of else on if ( $newcourseid = $DB->insert_record('course', $form) )
}

/**
 * Create each course in the moodle database
 *
 * @param type $courses
 */
function up_insert_courses($courses) {

	!empty($courses) or die('No courses to import!');

	// counters for created and failed courses
	$courses_created = 0;
	$courses_failed = 0;

	// Start timer for script
	$ts_timer = new Timer ();
	$ts_timer->start ();

	$j = 0; // Start Counter //


	// cycle through each of the courses and get their information
	foreach ( $courses as $data ) {

		if (UP_DEBUG) {
			print "<br /><br />ROW: $j<br />";
			print "COURSEID : " . $data->idnumber . '<br />';
			print "SHORTNAME : " . $data->shortname . '<br />';
			print "FULLNAME : " . $data->fullname . '<br />';
			print "STARTDATE : " . $data->startdate . '<br />';
			print "ENDDATE : " . $data->enddate . '<br />';
			print "TERMCODE : " . $data->termcode . '<br />';
		}
		$createstatus = up_insert_course ( $data );

		switch ($createstatus) {
			case COURSE_ALREADY_EXISTS :
				if (UP_DEBUG) {
					print "COURSE ALREADY EXISTS<br />";
				}
				break;
			case COURSE_CREATION_FAILED :
				if (UP_DEBUG) {
					print "COURSE CREATION FAILED<br />";
					$courses_failed ++;
				}
				break;
			case COURSE_CREATION_SUCCEEDED :
				if (UP_DEBUG) {
					print "COURSE CREATION SUCCEDED<br />";
					$courses_created ++;
				}
				break;
			case true :
				print "Testing!";
				break;
		}

		$j ++;
	}

	$ts_timer->finish ();
	// end foreach( $results as $data ) //

	echo '<br />action took: ' . $ts_timer->getTime () . " seconds.<br />" . "<br /> Courses created: " . $courses_created . " | Courses failed: " . $courses_failed;
}

/**
 * For writability/readability: handles all course enrolment tasks (importing, building, and inserting)
 */
function up_handle_course_enrolment()
{
	up_insert_courses(up_build_courses(up_import_courses()));
}


/**
 * Adds an enrolment plugin capability to a course
 * @param unknown $name name of the plugin
 * @param unknown $inserted whether the course was just inserted or already exists
 * @param unknown $course moodle course data
 * @param unknown $data raw course information
 * @return boolean whether the function successfully added a course or not
 */
function up_add_enrol_plugin($name, $inserted, $course, $data)
{
	global $CFG;

	$name = clean_param($name, PARAM_PLUGIN);

	if(empty($name)) {
		return false;
	}


	$location = "$CFG->dirroot/enrol/$name";
	if(!file_exists("$location/lib.php")) {
		//debug
		//echo "<h1 style='font-weight:bold;font-size=32;color:#FF0000'>PLUGIN DOES NOT EXIST</h1>";
		return false;
	}

	include_once("$location/lib.php");
	$class = "enrol_{$name}_plugin";
	if(!class_exists($class)) {
		//debug
		//echo "<h1 style='font-weight:bold;font-size=32;color:#FF0000'>CLASS DOES NOT EXIST</h1>";
		return false;
	}

	$plugin = new $class();

	if($inserted) {
		if($plugin->get_config('defaultenrol')) {
			$plugin->add_default_instance($course);
		}
	}

	//debug
	//echo "<h1 style='font-weight:bold;font-size=32'>Enrolment plugin added!</h1>";
	return true;
}


// Timer class // License: None, do whatever you want with it.
// Allows for benchmarking page performance.
//-------------------------------------------------------------------------
class Timer {
	var $startTime, $endTime, $timeDifference;

	function start()  { $this->startTime = $this->currentTime(); }
	function finish() { $this->endTime = $this->currentTime();   }

	function getTime() {
		$this->timeDifference = $this->endTime - $this->startTime;
		return round($this->timeDifference, 5);
	}


	function currentTime() { list($usec, $sec) = explode(' ',microtime()); return ((float)$usec + (float)$sec); }


}// End Timer class



