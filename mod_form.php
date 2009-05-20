<?php
require_once ($CFG->dirroot.'/course/moodleform_mod.php');

class mod_programming_mod_form extends moodleform_mod {

    function definition() {

        global $CFG, $COURSE;
        $mform    =& $this->_form;

//-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'globalid', get_string('globalid', 'programming'));

        $mform->addElement('text', 'name', get_string('name'), array('size'=>'30'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $mform->addElement('htmleditor', 'description', get_string('description', 'programming'));
        $mform->setType('description', PARAM_RAW);
        $mform->addRule('description', get_string('required'), 'required', null, 'client');
        $mform->setHelpButton('description', array('writing', 'questions', 'richtext'), false, 'editorhelpbutton');
        $mform->addElement('format', 'descformat', get_string('format'));

//-------------------------------------------------------------------------------
        $mform->addElement('header', '', get_string('grade'));

        $options = array();
        $options[0] = get_string('nograde', 'programming');
        for ($i = 5; $i <= 100; $i += 5) {
            $options[$i] = $i;
        }
        $mform->addElement('select', 'grade', get_string('grade'), $options);

        $options = array();
        for ($i = 10; $i > 0; $i -= 1) {
            $options[$i] = $i / 10.0;
        }
        $mform->addElement('select', 'discount', get_string('discount', 'programming'), $options);

        $mform->addElement('date_time_selector', 'timeopen', get_string('timeopen', 'programming'));
        $mform->addElement('date_time_selector', 'timediscount', get_string('timediscount', 'programming'));
        $mform->addElement('date_time_selector', 'timeclose', get_string('timeclose', 'programming'));

        $mform->addElement('selectyesno', 'allowlate', get_string('allowlate', 'programming'));
    
//-------------------------------------------------------------------------------
        $mform->addElement('header', '', get_string('program', 'programming'));

        $languages = get_records('programming_languages', '', '', 'id');
        $langs = array();
        foreach ($languages as $key => $lang) {
            $langs[$key] = $lang->name;
        }
        $select = $mform->addElement('select', 'langlimit', get_string('langlimit', 'programming'), $langs);
        $select->setMultiple(true);

        $inputs = array();
        $inputs[] = &MoodleQuickForm::createElement('radio', 'inputs', null, get_string('stdin', 'programming'), 0);
        $inputs[] = &MoodleQuickForm::createElement('radio', 'inputs', null, get_string('inputfromfile', 'programming'), 1);
        $inputs[] = &MoodleQuickForm::createElement('text', 'inputfile');
        $mform->addGroup($inputs, 'inputs', get_string('inputfile', 'programming'), ' ', false);
        $mform->disabledIf('inputfile', 'inputs', 'eq', 0);

        $outputs = array();
        $outputs[] = &MoodleQuickForm::createElement('radio', 'outputs', null, get_string('stdout', 'programming'), 0);
        $outputs[] = &MoodleQuickForm::createElement('radio', 'outputs', null, get_string('outputtofile', 'programming'), 1);
        $outputs[] = &MoodleQuickForm::createElement('text', 'outputfile');
        $mform->addGroup($outputs, 'outputs', get_string('outputfile', 'programming'), ' ', false);
        $mform->disabledIf('outputfile', 'outputs', 'eq', 0);

        $options = programming_get_timelimit_options();
        $mform->addElement('select', 'timelimit', get_string('timelimit', 'programming'), $options);

        $options = programming_get_memlimit_options();
        $mform->addElement('select', 'memlimit', get_string('memlimit', 'programming'), $options);

        $options = array();
        $options[0] = get_string('attemptsunlimited', 'programming');
        $options[1] = get_string('1attempt', 'programming');
        for ($i = 2; $i <= PROGRAMMING_MAX_ATTEMPTS; $i++) {
            $options[$i] = get_string('nattempts', 'programming', $i);
        }
        $mform->addElement('select', 'attempts', get_string('attempts', 'programming'), $options);

        $mform->addElement('selectyesno', 'keeplatestonly', get_string('keeplatestonly', 'programming'));

        $options = programming_get_showmode_options();
        $mform->addElement('select', 'showmode', get_string('showmode', 'programming'), $options);
        
//-------------------------------------------------------------------------------
        $features = new stdClass;
        $features->groups = true;
        $features->groupings = true;
        $features->groupmembersonly = true;
        $this->standard_coursemodule_elements($features);
//-------------------------------------------------------------------------------
// buttons
        $this->add_action_buttons();
    }

    function data_preprocessing(&$default_values) {
        if (empty($default_values['discount'])) {
            $default_values['discount'] = 8;
        }

        if (empty($default_values['inputs'])) {
            $default_values['inputs'] = $default_values['inputfile'] ? 1 : 0;
        }

        if (empty($default_values['outputs'])) {
            $default_values['outputs'] = $default_values['outputfile'] ? 1 : 0;
        }

        if (empty($default_values['langlimit']) && !empty($default_values['id'])) {
            $default_values['langlimit'] = array();
            $rows = get_records('programming_langlimit', 'programmingid', $default_values['id']);
            if (is_array($rows)) {
                foreach ($rows as $row) {
                    $default_values['langlimit'][] = $row->languageid;
                }
            }
        }
    }
}
?>
