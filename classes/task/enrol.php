<?php
namespace tool_createcourse\task;

require_once('../../lib.php');

class banner_enrol extends \core\task\scheduled_task {
	public function get_name() {
		// Shown in admin screens
		return get_string('tool_createcourse', 'pluginname');
	}

	public function execute() {
		//builds and inserts courses into moodle database
		up_handle_course_enrolment();
	}
}