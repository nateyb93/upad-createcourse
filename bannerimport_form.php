<?php
/**
 * bannerimport_form.php defines a form that is shown when a selection of courses has been imported from the banner database within UP's course system.
 * 
 */
class bannerimportform extends moodleform {
    
    protected $tableimport;
    
    public function __construct($tableimport)
    {
        //set the data for this form
        $this->tableimport = $tableimport;
        parent::__construt();
    }
    
    public function definition() {
        $mform =& $this->_form;
        
        //header
        $mform->addElement('header', 'importcourses', get_string('createcoursestable_legend', 'tool_createcourse'));
        
        //table content
        $mform->addElement('static', 'importcourseslist', '', html_writer::table($this->tableimport));
    }
}