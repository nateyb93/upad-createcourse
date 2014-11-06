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
