<?php

	require('../../../config.php');
	global $CFG;
	global $PAGE;
	global $SESSION;
	global $DB;
	global $OUTPUT;

	if(empty($_GET['action']))
	{
		echo("You've navigated to this page by mistake.");
		redirect(new moodle_url('view.php'));
	}
	else if($_GET['action'] == 'import')
	{
		require_once(__DIR__ . '/lib.php');

		//conditionally initialize the session variable for this plugin.
		if(empty($SESSION->tool_createcourse))
		{
			$SESSION->tool_createcourse = new stdClass();
		}

		//retrieve term data from query string
		$term = new stdClass();
		$term->termcode = $_GET["termcode"];
		$term->suffix = $_GET["suffix"];
		$term->categoryid = $_GET["categoryid"];
		$term->hidden = $_GET["hidden"];

		//make sure the term data is correct for the insertion.
		$SESSION->tool_createcourse->term_data = $term;


		//builds and inserts courses into moodle database
		up_handle_course_enrolment();
	}
	else if($_GET['action'] == 'delete')//user is trying to delete a record
	{
		if($DB->delete_records('tool_createcourse', array('termcode' => $_GET['termcode'])))
		{
			redirect(new moodle_url('index.php?deletesuccess=1'));
		}
		else
		{
			redirect(new moodle_url('index.php?deletesuccess=0'));
		}
	}
