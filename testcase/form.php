<?php
require_once ($CFG->libdir.'/formslib.php');

class testcase_form extends moodleform {

    function definition() {
        global $CFG, $COURSE, $programming;
        $mform =& $this->_form;

//-------------------------------------------------------------------------------
        $this->set_upload_manager(new upload_manager('', true, false, $COURSE, false, 0, true, true, false));
        $mform->addElement('hidden', 'a', $programming->id);
        $mform->addElement('hidden', 'programmingid', $programming->id);
        $mform->addElement('hidden', 'id');

        $mform->addElement('textarea', 'input', get_string('input', 'programming').helpbutton('input', 'input', 'programming', true, false, '', true), 'rows="5" cols="50"');
        $mform->addElement('file', 'inputfile', get_string('usefile', 'programming'));
        $mform->addElement('textarea', 'output', get_string('output', 'programming').helpbutton('output', 'output', 'programming', true, false, '', true), 'rows="5" cols="50"');
        $mform->addElement('file', 'outputfile', get_string('usefile', 'programming'));

        $mform->addElement('select', 'timelimit', get_string('timelimit', 'programming').helpbutton('timelimit', 'timelimit', 'programming', true, false, '', true), programming_get_timelimit_options());
        $mform->setDefault('timelimit', $programming->timelimit);

        $mform->addElement('select', 'memlimit', get_string('memlimit', 'programming').helpbutton('memlimit', 'memlimit', 'programming', true, false, '', true), programming_get_memlimit_options());
        $mform->setDefault('memlimit', $programming->memlimit);

        $mform->addElement('select', 'nproc', get_string('extraproc', 'programming').helpbutton('nproc', 'nproc', 'programming', true, false, '', true), programming_get_nproc_options());
        $mform->setDefault('nproc', $programming->nproc);

        $mform->addElement('select', 'weight', get_string('weight', 'programming').helpbutton('weight', 'weight', 'programming', true, false, '', true), programming_get_weight_options());
        $mform->setDefault('weight', 1);

        $mform->addElement('select', 'pub', get_string('public', 'programming').helpbutton('testcasepub', 'helptestcasepub', 'programming', true, false, '', true), programming_testcase_pub_options());
        $mform->setDefault('pub', 1);

        $mform->addElement('textarea', 'memo', get_string('memo', 'programming'), 'rows="5" cols="50"');

// buttons
        $this->add_action_buttons();
    }

    function set_data($data) {
        if (strlen($data->input) > 1023) {
            $data->input = '';
        }
        if (strlen($data->output) > 1023) {
            $data->output = '';
        }
        parent::set_data($data);
    }

    function validation($data, $files) {
        $errors = array();

        if (empty($data['output']) or trim($data['output']) == '')
            if (empty($files['outputfile']))
                $errors['output'] = get_string('required');

        return $errors;
    }

}
