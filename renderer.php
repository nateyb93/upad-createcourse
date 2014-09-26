<?php
defined('MOODLE_INTERNAL') || die();

class tool_createcourse_renderer extends plugin_renderer_base
{
    const INDEX_PAGE_IMPORT_STEP = 1;
    
    const INDEX_PAGE_CONFIRMATION_STEP = 2;
    
    const INDEX_PAGE_SUCCESS_STEP = 3;
    /**
     * Shows main form for creating a course
     * @param moodleform $mform
     * @return type
     */
    public function index_page(moodleform $mform, $step){
        $output = $this->header();
        
        switch($step)
        {
            case self::INDEX_PAGE_IMPORT_STEP;
                $output .= $this->moodleform($mform);
                break;
            case self::INDEX_PAGE_CONFIRMATION_STEP;
                $output .= $this->moodleform($mform);
                break;
        }
        
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