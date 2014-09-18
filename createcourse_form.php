<?php
require_once("$CFG->libdir/formslib.php");

class createcourse_form extends moodleform {
    public function definition() {
        global $CFG;
        
        $mform = $this->_form;
        
        //$mform->addElement('html', '<link rel="stylesheet" type="text/css" href="styles.css"');
        $mform->addElement('text', 'termcode', get_string('idx_termcode_txt', 'tool_createcourse'));
        $mform->setType('termcode', PARAM_NOTAGS);
        $mform->setDefault('termcode', '');
        
        $mform->addElement('text', 'courseid', get_string('idx_courseid_txt', 'tool_createcourse'));
        $mform->setType('courseid', PARAM_NOTAGS);
        $mform->setDefault('courseid', '');
        
        $mform->addElement('text', 'suffix', get_string('idx_suffix_txt', 'tool_createcourse'));
        $mform->setType('suffix', PARAM_NOTAGS);
        $mform->setDefault('suffix', '');
    }
    
}