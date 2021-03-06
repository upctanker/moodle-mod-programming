<?php

require_once($CFG->dirroot.'/lib/formslib.php');

class judgeresultchart_search_form extends moodleform {

    function definition () {
        global $CFG, $course;

        $mform =& $this->_form;
        $mform->addElement('hidden', 'a');
        $mform->addElement(
            'select', 'range', get_string('range', 'programming'),
             array('0' => get_string('showall', 'programming'),
                   '1' => get_string('showlatestonly', 'programming')));

        $groups = get_records('groups', 'courseid', $course->id);
        if (is_array($groups)) {
            $options = array('' => get_string('all'));
            foreach ($groups as $group) {
                $options[$group->id] = $group->name;
            }
            $mform->addElement('select', 'group', get_string('groups'), $options);
        }
    }

}

