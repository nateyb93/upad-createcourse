<?php
/**
 * bannerimport_form.php defines a form that is shown when a selection of courses has been imported from the banner database within UP's course system.
 * 
 */
class confirmationform extends moodleform {
    
    public function __construct()
    {
        //set the data for this form
        parent::__construt();
    }
    
    public function definition() {
        $mform =& $this->_form;
        
        //header
        $mform->addElement('header', 'importcourses', get_string('createcoursestable_legend', 'tool_createcourse'));
        $mform->addElement('button', 'confirmimport', "Confirm");
        
        
       
    }
}