<?php

/**
 * createcourse_form.php defines a form containing textboxes for inputting data about course term information
 */
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
        $mform->addElement('header', 'importpage_header', get_string('importpage_header', 'tool_createcourse'));
        $mform->addElement('html', '<span>' . get_string('importpage_description', 'tool_createcourse') . '</span>');
        
        $this->addSpacer($mform);
        $this->addSpacer($mform);
        
        //adds textboxes for inputting data for course
        $mform->addElement('text', 'termcode', get_string('importpage_termcode', 'tool_createcourse'));
        $mform->setType('termcode', PARAM_NOTAGS);
        $mform->setDefault('termcode', '');
        $mform->addRule('termcode', null, 'required', null, 'client');
        
        $this->addSpacer($mform);
        
        $mform->addElement('text', 'suffix', get_string('importpage_suffix', 'tool_createcourse'));
        $mform->setType('suffix', PARAM_NOTAGS);
        $mform->setDefault('suffix', '');
        $mform->addRule('suffix', null, 'required', null, 'client');
        
        $this->addSpacer($mform);
        
        $mform->addElement('text', 'categoryid', get_string('importpage_categoryid', 'tool_createcourse'));
        $mform->setType('categoryid', PARAM_NOTAGS);
        $mform->setDefault('categoryid', '');
        $mform->addRule('categoryid', null, 'required', null, 'client');
        
        
        //adds the submit, reset, and cancel buttons to the form
        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('importpage_submit', 'tool_createcourse'));
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