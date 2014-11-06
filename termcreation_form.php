<?php

require_once("$CFG->libdir/formslib.php");

class termcreation_form extends moodleform {

	public function __construct()
	{
		//set the data for this form
		parent::__construct();
	}

	public function definition() {
		global $CFG, $DB;

		$mform = $this->_form;

		$mform->addElement('html','<link href="styles.css" rel="stylesheet" type="text/css"/>');
		//adds header and description to page
		$mform->addElement('header', 'query_page_header',  get_string('viewqueries_header', 'tool_createcourse'));
		$mform->addElement('html', '<span>' . get_string('viewqueries_description', 'tool_createcourse') . '</span>');

		$this->addSpacer($mform);
		$this->addSpacer($mform);

		//add the table
		$sql = "SELECT * FROM {tool_createcourse}";
		$term_data = $DB->get_records_sql($sql);

		$mform->addElement('html',
						   "<div style='width: 60%;margin: auto auto;'>".
								$this->termdata_table($term_data).
						   "</div>");

		$mform->addElement('header', 'importpage_header', get_string('importpage_header', 'tool_createcourse'));
		$mform->addElement('html', '<span>' . get_string('importpage_description', 'tool_createcourse') . '</span>');

		$this->addSpacer($mform);
		$this->addSpacer($mform);

		//add textboxes for input
		$mform->addElement('text', 'termcode', get_string('importpage_termcode', 'tool_createcourse'));
		$mform->setType('termcode', PARAM_NOTAGS);
		$mform->setDefault('termcode', '');
		$mform->addRule('termcode', null, 'required', null, 'client');

		$this->addSpacer($mform);

		$mform->addElement('text', 'suffix', get_string('importpage_suffix', 'tool_createcourse'));
		$mform->setType('suffix', PARAM_NOTAGS);
		$mform->setDefault('suffix', '');
		$mform->addRule('suffix', null, 'required', null, 'client');

		$this->addSpacer($mform);

		//create multiselect for category ids; can only use a category id that already exists
		$query = "SELECT id FROM {course_categories}";
		$records = $DB->get_records_sql($query);
		$options = array();
		$options[] = '';
		foreach($records as $record)
		{
			$options[] = $record->id;
		}
		$mform->addElement('select', 'categoryid', get_string('importpage_categoryid', 'tool_createcourse'), $options);
		$mform->addRule('categoryid', null, 'required', null, 'client');

		$this->addSpacer($mform);

		//adds a checkbox for the 'hidden' field
		$mform->addElement('advcheckbox',
				'hideterm',
				get_string('importpage_hideterm', 'tool_createcourse'),
				get_string('importpage_hideterm_info', 'tool_createcourse'),
				/*attributes*/null,
				/*un/checked values*/array(0,1));

		//adds the submit, reset, and cancel buttons to the form
		$submitarray = array();
		$submitarray[] = &$mform->createElement('submit', 'submitbutton', get_string('importpage_submit', 'tool_createcourse'));
		$submitarray[] = &$mform->createElement('reset', 'resetbutton', get_string('revert'));
		$submitarray[] = &$mform->createElement('cancel');
		$mform->addGroup($submitarray, 'buttonarr', '', array(' '), false);


		$mform->closeHeaderBefore('buttonarr');



	}


	/**
	 * Defines an html table containing term data
	 * @param unknown $term_data
	 * @return string
	 */
	private function termdata_table($term_data)
	{
		global $OUTPUT, $DB;

		$mform = $this->_form;

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

			//import courses action
			$params = array(
				'action' => 'import',
				'termcode' => $term->termcode,
				'suffix' => $term->suffix,
				'categoryid' => $term->categoryid,
				'hidden' => $term->hidden
			);
			$action_url = new moodle_url("action.php", $params);
			$action_icon = $OUTPUT->pix_icon('t/backup', new lang_string('import'));
			$action = html_writer::link($action_url, $action_icon);

			//delete record action
			$params = array(
				'action' => 'delete',
				'termcode' => $term->termcode
			);
			$delete_url = new moodle_url("action.php", $params);
			$delete_icon = $OUTPUT->pix_icon('t/delete', new lang_string('delete'));
			$delete = html_writer::link($delete_url, $delete_icon);

			$actions = implode(' ', array($action, $delete));
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