<?php
require_once ($CFG->libdir.'/formslib.php');

class datafile_form extends moodleform {

    function definition() {
        global $CFG, $COURSE, $programming;
        $mform =& $this->_form;

//-------------------------------------------------------------------------------
        $this->set_upload_manager(new upload_manager('', true, false, $COURSE, false, 0, true, true, false));
        $mform->addElement('hidden', 'a', $programming->id);
        $mform->addElement('hidden', 'programmingid', $programming->id);
        $mform->addElement('hidden', 'id');

        $places = array();
        $mform->addElement('text', 'filename', get_string('filename', 'programming'), 'maxlength="50"');
        $filetype = array();
        $filetype[] = &MoodleQuickForm::createElement('radio', 'isbinary', null, get_string('textfile', 'programming'), 0);
        $filetype[] = &MoodleQuickForm::createElement('radio', 'isbinary', null, get_string('binaryfile', 'programming'), 1);
        $mform->addGroup($filetype, 'filetype', get_string('filetype', 'programming'), ' ', false);
        $mform->addElement('file', 'data', get_string('datafile', 'programming'));
        $mform->addElement('checkbox', 'usecheckdata', get_string('usecheckdata', 'programming'));
        $mform->addElement('file', 'checkdata', get_string('datafileforcheck', 'programming'));
        $mform->disabledIf('checkdata', 'usecheckdata');

        $mform->addElement('textarea', 'memo', get_string('memo', 'programming'), 'rows="5" cols="50"');

// buttons
        $this->add_action_buttons();
    }

    function validation($data, $files) {
        $errors = array();
        /// filename should not be empty
        if (empty($data['filename'])) {
            $errors['filename'] = get_string('required');
        } else {
            /// filename should only contain alpha, digit and underlins
            if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $data['filename'])) {
                $errors['filename'] = get_string('filenamechars', 'programming');
            }

            /// file name should not duplicate
            if (empty($data['id']) && count_records_select('programming_datafile', "programmingid={$data['programmingid']} AND filename='{$data['filename']}'")) {
                $errors['filename'] = get_string('datafilenamedupliate', 'programming');
            }
        }

        if (empty($data['id']) && empty($files['data'])) {
            $errors['data'] = get_string('required');
        }

        if (empty($data['id']) && !empty($data['usecheckdata']) && empty($files['checkdata'])) {
            $errors['checkdata'] = get_string('required');
        }

        return $errors;
    }

}
