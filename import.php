<?php
    echo 'Starting...';
    require_once(__DIR__ . '/upcustomlib.php');
    
    global $DB, $SESSION;
    
    $DB->insert_record('tool_createcourse', $SESSION->term_data);
    
    up_build_courses($SESSION->imports);
    
    up_insert_courses();
    
    echo 'Success!';