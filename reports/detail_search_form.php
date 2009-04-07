<?php

require_once($CFG->dirroot.'/lib/formslib.php');

class detail_search_form extends moodleform {

    function definition () {
        global $CFG, $course;

        $mform =& $this->_form;
        $mform->addElement('hidden', 'a');
        $mform->addElement('hidden', 'firstinitial');
        $mform->addElement('hidden', 'lastinitial');
        $mform->addElement('hidden', 'page');
        $mform->addElement(
            'select', 'latestonly', get_string('range', 'programming'),
             array('0' => get_string('showall', 'programming'),
                   '1' => get_string('showlatestonly', 'programming')));

        $options = programming_judgeresult_options(true);
        $options['NULL'] = get_string('statusshortnew', 'programming');
        $mform->addElement(
            'select', 'judgeresult', get_string('judgeresult', 'programming'), $options);

        $groups = get_records('groups', 'courseid', $course->id);
        if (is_array($groups)) {
            $options = array('' => get_string('all'));
            foreach ($groups as $group) {
                $options[$group->id] = $group->name;
            }
            $mform->addElement('select', 'group', get_string('groups'), $options);
        }

        $options = array(10 => 10, 20 => 20, 30 => 30, 50 => 50, 100 => 100);
        $mform->addElement('select', 'perpage', get_string('showperpage', 'programming'), $options);
    }

}

