<?php
class bannercoursetable extends html_table implements renderable
{
    protected $renderer;
    
    public function __construct($courses, $renderer)
    {
        parent::__construct();
        $this->renderer = $renderer;
        $this->buildtable($courses);
    }
    
    public function buildtable($courses)
    {
        global $OUTPUT;
        $this->data = array();
        
        $this->id = 'create_course_tool_banner_course_table';
        
        $columns = array(
            'col_termcode' => get_string('col_termcode', 'tool_createcourse'),
            'col_suffix' => get_string('col_suffix', 'tool_createcourse'),
            'col_categoryid' => get_string('col_categoryid', 'tool_createcourse'),
            'col_actions' => get_string('col_actions', 'tool_createcourse')
        );
        
        $this->head = array_values($columns);
        $this->colclasses = array_keys($columns);
        
        foreach ($courses as $course)
        {
            $row = array();
            $row[] = $course->termcode;
            $row[] = $course->suffix;
            $row[] = $course->categoryid;
            
            $edit_url = new moodle_url(__DIR__ . '/settings.php');
            $edit_icon = $OUTPUT->pix_icon('t/edit', new lang_string('edit'));
            $edit = html_writer::link($edit_url, $edit_icon);
            
            $delete_url = new moodle_url(__DIR__ . '/settings.php');
            $delete_icon = $OUTPUT->pix_icon('t/delete', new lang_string('delete'));
            
            $delete = html_writer::link($delete_url, $delete_icon);
            
            $actions = implode(' ', $edit, $delete);
            $row[] = $actions;
            
            //set data for table row
            $this->data[] = $row;
        }
    }
}