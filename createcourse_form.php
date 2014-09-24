<?php
require_once("$CFG->libdir/formslib.php");
require_once(__DIR__ . "/class_import.php");

class createcourse_form extends moodleform {
    /**
     * defines the form for creating a new course
     * @global type $CFG
     */
    public function definition() {
        global $CFG;
        
        $mform = $this->_form;
        
        //$mform->addElement('html', '<link rel="stylesheet" type="text/css" href="styles.css"');
        //adds header and description to page
        $mform->addElement('header', 'idx_header', get_string('idx_header', 'tool_createcourse'));
        $mform->addElement('static', 'description', get_string('idx_description', 'tool_createcourse'),
                           get_string('addcourse_description', 'tool_createcourse'));
        
        $this->addSpacer($mform);
        
        //adds textboxes for inputting data for course
        $mform->addElement('text', 'termcode', get_string('idx_termcode_txt', 'tool_createcourse'));
        $mform->setType('termcode', PARAM_NOTAGS);
        $mform->setDefault('termcode', '');
        
        $this->addSpacer($mform);
        
        $mform->addElement('text', 'categoryid', get_string('idx_categoryid_txt', 'tool_createcourse'));
        $mform->setType('categoryid', PARAM_NOTAGS);
        $mform->setDefault('categoryid', '');
        
        $this->addSpacer($mform);
        
        $mform->addElement('text', 'suffix', get_string('idx_suffix_txt', 'tool_createcourse'));
        $mform->setType('suffix', PARAM_NOTAGS);
        $mform->setDefault('suffix', '');
        
        //adds the submit, reset, and cancel buttons to the form
        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('idx_submit_btn', 'tool_createcourse'));
        $buttonarray[] = &$mform->createElement('reset', 'resetbutton', get_string('revert'));
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonarr', '', array(' '), false);
        $mform->closeHeaderBefore('buttonarr');
    }
    
    /**
     * Adds a line break to the document.
     * @param type $mform form to add spacer to
     */
    private function addSpacer($mform)
    {
        $mform->addElement('html', '<br/>');
    }
    
}