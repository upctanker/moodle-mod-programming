<?php

require_once($CFG->dirroot.'/lib/formslib.php');

class best_search_form extends moodleform {

    function definition () {
        global $CFG, $course;

        $mform =& $this->_form;
        $mform->addElement('hidden', 'a');
        $mform->addElement('hidden', 'page');

        $groups = get_records('groups', 'courseid', $course->id);
        if (is_array($groups)) {
            $options = array('' => get_string('all'));
            foreach ($groups as $group) {
                $options[$group->id] = $group->name;
            }
            $mform->addElement('select', 'group', get_string('groups'), $options);
        }

        $languages = get_records('programming_languages');
        if (is_array($languages)) {
            $options = array('' => get_string('all'));
            foreach ($languages as $language) {
                $options[$language->id] = $language->name;
            }
            $mform->addElement('select', 'language', get_string('language', 'programming'), $options);
        }

        $options = array(10 => 10, 30 => 30, 100 => 100);
        $mform->addElement('select', 'perpage', get_string('showperpage', 'programming'), $options);
    }

}

