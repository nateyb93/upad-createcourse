<?php
defined('MOODLE_INTERNAL') || die();

class tool_createcourse_renderer extends plugin_renderer_base
{
    
    /**
     * Shows main form for creating a course
     * @param moodleform $mform
     * @return type
     */
    public function index_page(moodleform $mform){
        $output = $this->header();
        $output .= $this->moodleform($mform);
        $output .= $this->footer();
        return $output;
    }
    
    /**
     * Use this since we can't fetch the output of a moodle form
     * @param moodleform $mform
     * @return type string HTML
     */
    protected function moodleform(moodleform $mform)
    {
        ob_start();
        $mform->display();
        $o = ob_get_contents();
        ob_end_clean();
        
        return $o;
    }
}