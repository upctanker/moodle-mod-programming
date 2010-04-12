<?php
require_once ($CFG->libdir.'/formslib.php');
require_once ('../lib.php');

class validator_form extends moodleform {

    function definition() {
        global $CFG, $COURSE, $programming;
        $mform =& $this->_form;

//-------------------------------------------------------------------------------
        $mform->addElement('hidden', 'a', $programming->id);
        $mform->addElement('hidden', 'id', $programming->id);

        $options = array(
            '0' => get_string('comparetext', 'programming'),
            '1' => get_string('comparetextwithpe', 'programming'),
            '2' => get_string('comparefilesizeandmd5', 'programming'),
            '9' => get_string('customizedjudgescript', 'programming')
        );
        $mform->addElement('select', 'validatortype', get_string('validatortype', 'programming'), $options);

        $options = programming_get_language_options();
        $mform->addElement('select', 'validatorlang', get_string('validatorlang', 'programming'), $options);
        $mform->disabledIf('validatorlang', 'validatortype', 'neq', 9);

        $mform->addElement('textarea', 'validator', get_string('validatorcode', 'programming'), 'rows="10" cols="50"');
        $mform->disabledIf('validator', 'validatortype', 'neq', 9);

// buttons
        $this->add_action_buttons();
    }

    function validation($data, $files) {
        $errors = array();

        return $errors;
    }

}
