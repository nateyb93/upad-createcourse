<?php

require_once("$CFG->libdir/formslib.php");
require_once(__DIR__ . "/class_import.php");
require_once(__DIR__ . "/termtable.php");

class viewqueries_form extends moodleform {

	public function __construct()
	{
		//set the data for this form
		parent::__construct();
	}

	public function definition() {
		global $CFG, $DB;

		$mform = $this->_form;

		//adds header and description to page
		$mform->addElement('header', 'query_page_header',  get_string('viewqueries_header', 'tool_createcourse'));
		$mform->addElement('html', '<span>' . get_string('viewqueries_description', 'tool_createcourse') . '</span>');

		$this->addSpacer($mform);
		$this->addSpacer($mform);

		//add the table
		$sql = "SELECT * FROM {tool_createcourse}";
		$term_data = $DB->get_records_sql($sql);
		$mform->addElement('html', "<div style='width: 60%;margin: auto auto;'>" . $this->termdata_table($term_data) . "</div>");
	}


	/**
	 * Defines an html table containing term data
	 * @param unknown $term_data
	 * @return string
	 */
	private function termdata_table($term_data)
	{
		global $OUTPUT, $DB;
		$table = new html_table();

		$columns = array(
			'col_termcode' => get_string('col_termcode', 'tool_createcourse'),
			'col_suffix' => get_string('col_suffix', 'tool_createcourse'),
			'col_categoryid' => get_string('col_categoryid', 'tool_createcourse'),
			'col_hidden' => get_string('col_hidden', 'tool_createcourse'),
			'col_actions' => get_string('col_actions', 'tool_createcourse')
		);

		$table->head = array_values($columns);
		$table->colclasses = array_keys($columns);

		foreach ($term_data as $term)
		{
			$row = array();
			$row[] = $term->termcode;
			$row[] = $term->suffix;
			$row[] = $term->categoryid;
			$row[] = $term->hidden;

			$preview_url = new moodle_url('settings.php');
			$preview_icon = $OUTPUT->pix_icon('t/preview', new lang_string('preview'));
			$preview = html_writer::link($preview_url, $preview_icon);

			$delete_url = new moodle_url('settings.php');
			$delete_icon = $OUTPUT->pix_icon('t/delete', new lang_string('delete'));
			$delete = html_writer::link($delete_url, $delete_icon);

			$actions = implode(' ', array($preview, $delete));
			$row[] = $actions;

			//set data for table row
			$table->data[] = $row;
		}

		return html_writer::table($table);
	}

	/**
	 * Add a line break to the document
	 * @param unknown $mform
	 */
	private function addSpacer($mform)
	{
		$mform->addElement('html', '<br/>');
	}
}