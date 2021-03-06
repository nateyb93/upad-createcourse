<?php
global $SESSION;
$string['pluginname'] = "View terms/import courses";
$string['header'] = 'Import courses from banner to be entered into the course database';

//database information
$string['dbinfo_heading_name'] = 'dbinfo_heading';
$string['dbinfo_heading_heading'] = 'Database information';
$string['dbinfo_heading_info'] = 'Enter connection information for the banner database';

$string['dbinfo_protocol_name'] = 'dbinfo_protocol';
$string['dbinfo_protocol_visiblename'] = 'Protocol';
$string['dbinfo_protocol_description'] = 'Enter external database protocol type here.';
$string['dbinfo_protocol_default'] = '';

$string['dbinfo_hostname_name'] = 'dbinfo_hostname';
$string['dbinfo_hostname_visiblename'] = 'Hostname';
$string['dbinfo_hostname_description'] = 'Enter external database hostname address here.';
$string['dbinfo_hostname_default'] = '';

$string['dbinfo_portno_name'] = 'dbinfo_portno';
$string['dbinfo_portno_visiblename'] = 'Port number';
$string['dbinfo_portno_description'] = 'Enter external database port number here.';
$string['dbinfo_portno_default'] = '';

//database login
$string['dbauth_heading_name'] = 'dbauth_heading';
$string['dbauth_heading_heading'] = 'Login information';
$string['dbauth_heading_info'] = 'Enter external database login information here';

$string['dbauth_login_name'] = 'dbauth_login';
$string['dbauth_login_visiblename'] = 'Username';
$string['dbauth_login_description'] = 'Enter external database username here.';
$string['dbauth_login_default'] = '';

$string['dbauth_password_name'] = 'dbauth_password';
$string['dbauth_password_visiblename'] = 'Password';
$string['dbauth_password_description'] = 'Enter external database password here.';
$string['dbauth_password_default'] = '';

$string['dbauth_sid_name'] = 'dbauth_sid';
$string['dbauth_sid_visiblename'] = 'Site Identifier';
$string['dbauth_sid_description'] = 'Enter valid SID here.';
$string['dbauth_sid_default'] = '';

//banner import settings
$string['importsettings_heading_name'] = 'importsettings_heading';
$string['importsettings_heading_heading'] = 'Import settings';
$string['importsettings_heading_info'] = 'Settings for external database imports';

$string['importsettings_maxcourses_name'] = 'importsettings_maxcourses';
$string['importsettings_maxcourses_visiblename'] = 'Max # of courses';
$string['importsettings_maxcourses_description'] = "Sets the maxiumum number of courses that can be imported at a time";
$string['importsettings_maxcourses_default'] = '';

//main page/new courses finding form elements
$string['importpage_termcode'] = 'Termcode';
$string['importpage_categoryid'] = 'Category Id';
$string['importpage_suffix'] = 'Suffix';


$string['importpage_submit'] = 'Submit';
$string['importpage_importbutton'] = 'Import Courses';
$string['importpage_header'] = "Add new category";
$string['importpage_description'] = "Enter information here for a new term. If it isn't already in the database, it will be added.";
$string['importpage_hideterm'] = 'Hide term';
$string['importpage_hideterm_info'] = 'Hide courses associated with the specified termcode in Moodle.';

$string['confirmationpage_header'] = 'Confirm Submission';
$string['confirmationpage_text'] = 'Be sure the information above is correct before clicking submit!';
$string['confirmationpage_submit'] = 'Submit';


$string['successpage_text'] = "Success! The courses with the specified termcode have been added to Moodle's course database.";
$string['successpage_submit'] = "Continue";


//for viewing queries
$string['viewqueries'] = "View terms or Import courses";
$string['viewqueries_header'] = "View terms/Import courses";
$string['viewqueries_description'] = "You can add/remove term data and import courses from this form.";

//term_data table column names
$string['col_termcode'] = 'Termcode';
$string['col_suffix'] = 'Suffix';
$string['col_categoryid'] = 'Category ID';
$string['col_hidden'] = "Hidden";
$string['col_actions'] = 'Actions';

