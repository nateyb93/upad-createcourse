<?php

print "starting...\n";

error_reporting(E_ALL);
ini_set('display_errors', 'ON');

require_once( __DIR__ . '/upconf.inc.php' );

require_once( __DIR__ . '/timer.php' );

//   define('CLI_SCRIPT', true);


// Create counters for successfully and unsuccessfully created courses
$courses_created = 0;
$courses_failed = 0;


set_time_limit( 3600 );
define( 'UP_DEBUG', true );


// Call moodle path
$moodlepath = '/var/www/html/moodle';


// Load libs
require_once( $moodlepath . '/config.php' );
require_once( __DIR__ . '/upcustomlib.php' );


function get_courses()
{
    // Connect to Banner and retrieve tables
    $query = "SELECT VW_UPM_COURSES.CRN,VW_UPM_COURSES.TERMCODE,VW_UPM_COURSES.COURSE_LONG_NAME,VW_UPM_COURSES.COURSE_SHORT_NAME,VW_UPM_COURSES.START_DATE,VW_UPM_COURSES.END_DATE,TBL_UPM_COURSE_SYNC.CREATED FROM UP_MOODLE.VW_UPM_COURSES LEFT OUTER JOIN UP_MOODLE.TBL_UPM_COURSE_SYNC ON VW_UPM_COURSES.CRN = TBL_UPM_COURSE_SYNC.CRN WHERE TBL_UPM_COURSE_SYNC.CRN is null";
    $dbinfo = get_db_info();
    $oc    = oci_connect( $dbinfo->username, $dbinfo->password, $dbinfo->db ) or die( "could not connect to oracle" );
    $sql   = oci_parse( $oc, $query );

    OCIExecute( $sql );


    // Set total rows to be processed. Set to 0 to get all rows.
    //in plugin settings
    $totalrows = $dbinfo->maxcourses; 


    $rows      = oci_fetch_all( $sql,$results,0,$totalrows,OCI_FETCHSTATEMENT_BY_ROW );
    if( UP_DEBUG )	{
	print "$rows classes queried<br />\n";
    }
    return $results;
}

//loop over each course being created
function create_courses($rows){
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
    
    foreach( $rows as $data ){

            if( UP_DEBUG )
            {
                    print "<br /><br />ROW: $j<br />";
                    print "COURSEID : " . $data['CRN'] . '<br />';
                    print "SHORTNAME : " . $data['COURSE_SHORT_NAME'] . '<br />';
                    print "FULLNAME : " . $data['COURSE_LONG_NAME'] . '<br />';
                    print "STARTDATE : " . $data['START_DATE'] . '<br />';
                    print "ENDDATE : " . $data['END_DATE'] . '<br />';
                    print "TERMCODE : " . $data['TERMCODE'] . '<br />';
             }


            $createstatus = up_create_course( $data['CRN'], $data['COURSE_SHORT_NAME'], $data['COURSE_LONG_NAME'], 
                                              $data['START_DATE'], $data['END_DATE'], $data['TERMCODE'] );
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
}

?>
