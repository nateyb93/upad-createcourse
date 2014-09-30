<?php
defined('MOODLE_INTERNAL') || die;

if($hassiteconfig) {
    $ADMIN->add('accounts',
                new admin_category('tool_createcourse', get_string('pluginname', 'tool_createcourse')));
    
    $ADMIN->add('tool_createcourse',
                new admin_externalpage('tool_createcourse_create', get_string('pluginname', 'tool_createcourse'),
                $CFG->wwwroot.'/'.$CFG->admin.'/tool/createcourse/index.php',
                'moodle/site:config'));
    
    //add configuration settings for external database information
    $settings = new admin_settingpage('createcourse_settings',
            get_string('pluginname', 'tool_createcourse'));
    
    $settings->add(new admin_setting_heading(get_string('dbinfo_heading_name', 'tool_createcourse'),
                                             get_string('dbinfo_heading_heading', 'tool_createcourse'),
                                             get_string('dbinfo_heading_info', 'tool_createcourse')));
    
    $settings->add(new admin_setting_configtext(get_string('dbinfo_protocol_name', 'tool_createcourse'),
                                                get_string('dbinfo_protocol_visiblename', 'tool_createcourse'),
                                                get_string('dbinfo_protocol_description', 'tool_createcourse'),
                                                get_string('dbinfo_protocol_default', 'tool_createcourse')));
    
    $settings->add(new admin_setting_configtext(get_string('dbinfo_hostname_name', 'tool_createcourse'),
                                                get_string('dbinfo_hostname_visiblename', 'tool_createcourse'),
                                                get_string('dbinfo_hostname_description', 'tool_createcourse'),
                                                get_string('dbinfo_hostname_default', 'tool_createcourse')));
    
    $settings->add(new admin_setting_configtext(get_string('dbinfo_portno_name', 'tool_createcourse'),
                                                get_string('dbinfo_portno_visiblename', 'tool_createcourse'),
                                                get_string('dbinfo_portno_description', 'tool_createcourse'),
                                                get_string('dbinfo_portno_default', 'tool_createcourse')));
    
    
    $settings->add(new admin_setting_heading(get_string('dbauth_heading_name', 'tool_createcourse'),
                                             get_string('dbauth_heading_heading', 'tool_createcourse'),
                                             get_string('dbauth_heading_info', 'tool_createcourse')));
    
    $settings->add(new admin_setting_configtext(get_string('dbauth_login_name', 'tool_createcourse'),
                                                get_string('dbauth_login_visiblename', 'tool_createcourse'),
                                                get_string('dbauth_login_description', 'tool_createcourse'),
                                                get_string('dbauth_login_default', 'tool_createcourse')));
    
    $settings->add(new admin_setting_configpasswordunmask(get_string('dbauth_password_name', 'tool_createcourse'),
                                                          get_string('dbauth_password_visiblename', 'tool_createcourse'),
                                                          get_string('dbauth_password_description', 'tool_createcourse'),
                                                          get_string('dbauth_password_default', 'tool_createcourse')));
    
    $settings->add(new admin_setting_configtext(get_string('dbauth_sid_name', 'tool_createcourse'),
                                                get_string('dbauth_sid_visiblename', 'tool_createcourse'),
                                                get_string('dbauth_sid_description', 'tool_createcourse'),
                                                get_string('dbauth_sid_default', 'tool_createcourse')));
    
    
    $settings->add(new admin_setting_heading(get_string('importsettings_heading_name', 'tool_createcourse'),
                                             get_string('importsettings_heading_heading', 'tool_createcourse'),
                                             get_string('importsettings_heading_info', 'tool_createcourse')));
    
    $settings->add(new admin_setting_configtext(get_string('importsettings_maxcourses_name', 'tool_createcourse'),
                                                get_string('importsettings_maxcourses_visiblename', 'tool_createcourse'),
                                                get_string('importsettings_maxcourses_description', 'tool_createcourse'),
                                                get_string('importsettings_maxcourses_default', 'tool_createcourse')));
    
    $ADMIN->add('tools', $settings);
}