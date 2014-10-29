<?php
/**
 * confirmation_form.php defines a form that is shown when a selection of courses has been imported from the banner database within UP's course system.
 * adds an extra layer of security to adding courses to moodle's database
 */
class confirmationform extends moodleform {

    public function __construct()
    {
        //set the data for this form
        parent::__construct();
    }

    public function definition() {
        global $SESSION;
        $mform =& $this->_form;

        $num_courses = $SESSION->tool_createcourse->num_courses;
        //header
        $mform->addElement('header', 'importcourses', get_string('confirmationpage_header', 'tool_createcourse'));

        //text
        $mform->addElement('html', '<span>' . "You are about to add $num_courses courses to Moodle. ".get_string('confirmationpage_text','tool_createcourse') . '</span>');

        $mform->registerNoSubmitButton('confirmsubmit');
        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'confirmsubmit', get_string('confirmationpage_submit', 'tool_createcourse'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }
}