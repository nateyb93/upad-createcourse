<?php

/**
 * createcourse_form.php defines a form containing textboxes for inputting data about course term information
 */
require_once("$CFG->libdir/formslib.php");
require_once(__DIR__ . "/class_import.php");

class createcourse_form extends moodleform {

	public function __construct()
	{
		//set the data for this form.
		parent::__construct();
	}


    /**
     * defines the form for creating a new course
     * @global type $CFG
     */
    public function definition() {
        global $CFG, $DB;

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

        //create multiselect for category ids; can only use a category id that already exists
        $query = "SELECT id FROM {course_categories}";
        $records = $DB->get_records_sql($query);
        $options = array();
        $options[] = '';
        foreach($records as $record)
        {
        	$options[] = $record->id;
        }
        $mform->addElement('select', 'categoryid', get_string('importpage_categoryid', 'tool_createcourse'), $options);
        $mform->addRule('categoryid', null, 'required', null, 'client');

        $this->addSpacer($mform);

        //adds a checkbox for the 'hidden' field
        $mform->addElement('advcheckbox',
        				   'hideterm',
        				   get_string('importpage_hideterm', 'tool_createcourse'),
        				   get_string('importpage_hideterm_info', 'tool_createcourse'));
        $mform->addRule('hideterm', null, 'required', null, 'client');

        $this->addSpacer($mform);

        $mform->addElement('html', '<span>' . get_string('confirmationpage_text', 'tool_createcourse') . '</span>');


        //adds the submit, reset, and cancel buttons to the form
        $submitarray = array();
        $submitarray[] = &$mform->createElement('submit', 'submitbutton', get_string('importpage_submit', 'tool_createcourse'));
        $submitarray[] = &$mform->createElement('reset', 'resetbutton', get_string('revert'));
        $submitarray[] = &$mform->createElement('cancel');
        $mform->addGroup($submitarray, 'buttonarr', '', array(' '), false);


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