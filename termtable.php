<?php
class termdata_table extends html_table implements renderable
{
    protected $renderer;

    public function __construct($renderer)
    {
        parent::__construct();
        $this->renderer = $renderer;
        $this->buildtable($courses);
    }

    public function buildtable($term_data)
    {

    }
}