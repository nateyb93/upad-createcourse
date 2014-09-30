<?php
/**
 * bannerimport_form.php defines a form that is shown when a selection of courses has been imported from the banner database within UP's course system.
 * 
 */
class confirmationform extends moodleform {
    
    public function __construct()
    {
        //set the data for this form
        parent::__construct();
    }
    
    public function definition() {
        $mform =& $this->_form;
        
        //header
        $mform->addElement('header', 'importcourses', get_string('confirmationpage_header', 'tool_createcourse'));
        $mform->addElement('html', '<span>' . get_string('confirmationpage_text','tool_createcourse') . '</span>');
        
        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('confirmationpage_submit', 'tool_createcourse'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');

    }
}