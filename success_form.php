<?php
/**
 * success_form.php defines a form that is displayed when courses have successfully been inserted into moodle's course database
 */
class successform extends moodleform {
    
    public function __construct()
    {
        //set the data for this form
        parent::__construct();
    }
    
    public function definition() {
        $mform =& $this->_form;
        
        //header
        $mform->addElement('html', '<span>' . get_string('successpage_text','tool_createcourse') . '</span>');
        
        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('successpage_submit', 'tool_createcourse'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);

    }
}
