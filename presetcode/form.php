<?php
require_once ($CFG->libdir.'/formslib.php');

class presetcode_form extends moodleform {

    function __construct() {
        parent::__construct();
    }

    function definition() {
        global $CFG, $COURSE, $programming;
        $mform =& $this->_form;

//-------------------------------------------------------------------------------
        $mform->addElement('hidden', 'a', $programming->id);
        $mform->addElement('hidden', 'programmingid', $programming->id);
        $mform->addElement('hidden', 'id');

        $places = array();
        $places[] = &MoodleQuickForm::createElement('radio', 'choosename', null, get_string('prepend', 'programming'), 1);
        $places[] = &MoodleQuickForm::createElement('radio', 'choosename', null, get_string('postpend', 'programming'), 2);
        $places[] = &MoodleQuickForm::createElement('radio', 'choosename', null, get_string('customfile', 'programming'), 0);
        $mform->addGroup($places, 'places', get_string('place', 'programming'), ' ', false);

        $mform->addElement('text', 'name', get_string('filename', 'programming'));
        $mform->disabledIf('name', 'choosename', 'not eq', 0);

        $mform->addElement('select', 'languageid', get_string('language', 'programming'), programming_get_language_options($programming));

        $mform->addElement('textarea', 'presetcode', get_string('presetcode', 'programming'), 'rows="5" cols="50"');

        $mform->addElement('checkbox', 'usepresetcodeforcheck', get_string('usepresetcodeforcheck', 'programming'));
        $mform->addElement('textarea', 'presetcodeforcheck', get_string('presetcodeforcheck', 'programming'), 'rows="5" cols="50"');
        $mform->disabledIf('presetcodeforcheck', 'usepresetcodeforcheck');

// buttons
        $this->add_action_buttons();
    }

    function set_data($data) {
        if (empty($data->name) || $data->name == '<prepend>') {
            $data->choosename = 1;
        } else if ($data->name == '<postpend>') {
            $data->choosename = 2;
        } else {
            $data->choosename = 0;
        }

        if (!empty($data->presetcodeforcheck)) {
            $data->usepresetcodeforcheck = true;
        }

        if (empty($data->presetcode)) {
            $data->usepresetcodeforcheck = false;
        }
        parent::set_data($data);
    }

    function validation($data) {
        $errors = array();
        if ($data['choosename'] == 0) {
            /// filename should not be empty
            if (empty($data['name'])) {
                $errors['name'] = get_string('required');
            }

            /// filename should only contain alpha, digit and underlins
            if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $data['name'])) {
                $errors['name'] = get_string('filenamechars', 'programming');
            }

            /// file extension must be correct
            $lang = get_record('programming_languages', 'id', $data['languageid']);
            $allowedext = array_merge(explode(' ', $lang->headerext), explode(' ', $lang->sourceext));
            $ext = substr($data['name'], strrpos($data['name'], '.'));
            if (!in_array($ext, $allowedext)) {
                $errors['name'] = get_string('extmustbe', 'programming', implode(', ', $allowedext));
            }

            /// file name should not duplicate
            if (empty($data['id']) && count_records_select('programming_presetcode', "programmingid={$data['programmingid']} AND name='{$data['name']}'")) {
                $errors['name'] = get_string('presetcodenamedupliate', 'programming');
            }

        } else if (empty($data['id']) && $data['choosename'] == 1 && count_records_select('programming_presetcode', "programmingid={$data['programmingid']} AND name='<prepend>'")) {
            $errors['places'] = get_string('prependcodeexists', 'programming');

        } else if (empty($data['id']) && $data['choosename'] == 2 && count_records_select('programming_presetcode', "programmingid={$data['programmingid']} AND name='<postpend>'")) {
            $errors['places'] = get_string('postpendcodeexists', 'programming');
        }

        if (empty($data['presetcode'])) {
            $errors['presetcode'] = get_string('required');
        }

        if (!empty($data['usepresetcodeforcheck']) && empty($data['presetcodeforcheck'])) {
            $errors['presetcodeforcheck'] = get_string('required');
        }

        return $errors;
    }

}
