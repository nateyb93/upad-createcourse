<?php
require ('../../../config.php');

global $CFG, $DB, $PAGE;

error_reporting ( E_ALL );
ini_set ( 'display_errors', 'On' );

require_once ($CFG->dirroot . '/lib/adminlib.php');
require_once (__DIR__ . '/termcreation_form.php');
require_once (__DIR__ . '/lib.php');

require_login ();
require_capability ( 'moodle/site:config', context_system::instance () );
admin_externalpage_setup ( 'tool_createcourse_create' );

$termform = new termcreation_form ();
$renderer = $PAGE->get_renderer ( 'tool_createcourse' );

if ($postData = $termform->get_data ()) {
	if (! empty ( $postData->termcode )) {
		// set term data to form data
		$term_data = new stdClass ();
		$term_data->termcode = $postData->termcode;
		$term_data->suffix = $postData->suffix;
		$term_data->categoryid = $postData->categoryid;
		$term_data->hidden = $postData->hideterm;

		// make sure the category exists.
		up_category_exists ( $term_data->categoryid ) or die ( "Category $term_data->categoryid does not exist in Moodle database. Add it or choose a different category." );

		// perform course insertion
		// TODO: look to move this elsewhere
		if (! up_term_exists ( $term_data->termcode, $term_data->suffix, $term_data->categoryid )) {
			$DB->insert_record ( 'tool_createcourse', $term_data ) or die ( "Error" );
		}

		redirect ( new moodle_url ( 'index.php?added' ) );
	}
}

else {
	echo $renderer->index_page ( $termform );
}
