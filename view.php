<?php
require('../../../config.php');

global $CFG, $DB, $PAGE;

error_reporting(E_ALL);
ini_set('display_errors','On');

require_once($CFG->dirroot . '/lib/adminlib.php');
require_once(__DIR__ . '/viewqueries_form.php');

require_login();
require_capability('moodle/site:config', context_system::instance());
admin_externalpage_setup('tool_createcourse_view');
//$id = required_param('id', PARAM_INT);

$renderer = $PAGE->get_renderer('tool_createcourse');

$queries = new viewqueries_form();

echo $renderer->query_page($queries);