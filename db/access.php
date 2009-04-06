<?php

$mod_programming_capabilities = array(

   'mod/programming:viewcontent' => array(
       'captype' => 'read',
       'contextlevel' => CONTEXT_MODULE,
       'legacy' => array(
           'guest' => CAP_ALLOW,
           'student' => CAP_ALLOW,
           'teacher' => CAP_ALLOW,
           'editingteacher' => CAP_ALLOW,
           'coursecreator' => CAP_ALLOW,
           'admin' => CAP_ALLOW
       )
   ),

   'mod/programming:viewcontentatanytime' => array(
       'captype' => 'read',
       'contextlevel' => CONTEXT_MODULE,
       'legacy' => array(
           'guest' => CAP_PREVENT,
           'student' => CAP_PREVENT,
           'teacher' => CAP_ALLOW,
           'editingteacher' => CAP_ALLOW,
           'coursecreator' => CAP_PREVENT,
           'admin' => CAP_ALLOW
       )
   ),

   'mod/programming:submitprogram' => array(
       'captype' => 'write',
       'contextlevel' => CONTEXT_MODULE,
       'legacy' => array(
           'guest' => CAP_PREVENT,
           'student' => CAP_ALLOW,
           'teacher' => CAP_ALLOW,
           'editingteacher' => CAP_ALLOW,
           'coursecreator' => CAP_ALLOW,
           'admin' => CAP_ALLOW
       )
   ),

   'mod/programming:submitatanytime' => array(
       'captype' => 'write',
       'contextlevel' => CONTEXT_MODULE,
       'legacy' => array(
           'guest' => CAP_PREVENT,
           'student' => CAP_PREVENT,
           'teacher' => CAP_ALLOW,
           'editingteacher' => CAP_ALLOW,
           'coursecreator' => CAP_PREVENT,
           'admin' => CAP_ALLOW
       )
   ),

   'mod/programming:submitforothers' => array(
       'captype' => 'write',
       'contextlevel' => CONTEXT_MODULE,
       'legacy' => array(
           'guest' => CAP_PREVENT,
           'student' => CAP_PREVENT,
           'teacher' => CAP_PREVENT,
           'editingteacher' => CAP_ALLOW,
           'coursecreator' => CAP_PREVENT,
           'admin' => CAP_ALLOW
       )
   ),

   'mod/programming:deleteothersubmit' => array(
       'captype' => 'write',
       'contextlevel' => CONTEXT_MODULE,
       'legacy' => array(
           'guest' => CAP_PREVENT,
           'student' => CAP_PREVENT,
           'teacher' => CAP_PREVENT,
           'editingteacher' => CAP_ALLOW,
           'coursecreator' => CAP_PREVENT,
           'admin' => CAP_ALLOW
       )
   ),

   'mod/programming:viewdetailresult' => array(
       'captype' => 'read',
       'contextlevel' => CONTEXT_MODULE,
       'legacy' => array(
           'guest' => CAP_PREVENT,
           'student' => CAP_ALLOW,
           'teacher' => CAP_ALLOW,
           'editingteacher' => CAP_ALLOW,
           'coursecreator' => CAP_ALLOW,
           'admin' => CAP_ALLOW
       )
   ),

   'mod/programming:viewdetailresultincontest' => array(
       'captype' => 'read',
       'contextlevel' => CONTEXT_MODULE,
       'legacy' => array(
           'guest' => CAP_PREVENT,
           'student' => CAP_PREVENT,
           'teacher' => CAP_ALLOW,
           'editingteacher' => CAP_ALLOW,
           'coursecreator' => CAP_ALLOW,
           'admin' => CAP_ALLOW
       )
   ),

   'mod/programming:viewsummaryresult' => array(
       'captype' => 'read',
       'contextlevel' => CONTEXT_MODULE,
       'legacy' => array(
           'guest' => CAP_PREVENT,
           'student' => CAP_ALLOW,
           'teacher' => CAP_ALLOW,
           'editingteacher' => CAP_ALLOW,
           'coursecreator' => CAP_ALLOW,
           'admin' => CAP_ALLOW
       )
   ),

   'mod/programming:viewhistory' => array(
       'captype' => 'read',
       'contextlevel' => CONTEXT_MODULE,
       'legacy' => array(
           'guest' => CAP_PREVENT,
           'student' => CAP_ALLOW,
           'teacher' => CAP_ALLOW,
           'editingteacher' => CAP_ALLOW,
           'coursecreator' => CAP_ALLOW,
           'admin' => CAP_ALLOW
       )
   ),

   'mod/programming:edittestcase' => array(
       'captype' => 'write',
       'contextlevel' => CONTEXT_MODULE,
       'legacy' => array(
           'guest' => CAP_PREVENT,
           'student' => CAP_PREVENT,
           'teacher' => CAP_PREVENT,
           'editingteacher' => CAP_ALLOW,
           'coursecreator' => CAP_PREVENT,
           'admin' => CAP_ALLOW
       )
   ),

   'mod/programming:viewpubtestcase' => array(
       'captype' => 'read',
       'contextlevel' => CONTEXT_MODULE,
       'legacy' => array(
           'guest' => CAP_PREVENT,
           'student' => CAP_ALLOW,
           'teacher' => CAP_ALLOW,
           'editingteacher' => CAP_ALLOW,
           'coursecreator' => CAP_ALLOW,
           'admin' => CAP_ALLOW
       )
   ),

   'mod/programming:viewhiddentestcase' => array(
       'captype' => 'read',
       'contextlevel' => CONTEXT_MODULE,
       'legacy' => array(
           'guest' => CAP_PREVENT,
           'student' => CAP_PREVENT,
           'teacher' => CAP_PREVENT,
           'editingteacher' => CAP_ALLOW,
           'coursecreator' => CAP_PREVENT,
           'admin' => CAP_ALLOW
       )
   ),

   'mod/programming:viewotherprogram' => array(
       'captype' => 'read',
       'contextlevel' => CONTEXT_MODULE,
       'legacy' => array(
           'guest' => CAP_PREVENT,
           'student' => CAP_PREVENT,
           'teacher' => CAP_ALLOW,
           'editingteacher' => CAP_ALLOW,
           'coursecreator' => CAP_PREVENT,
           'admin' => CAP_ALLOW
       )
   ),

   'mod/programming:viewotherresult' => array(
       'captype' => 'read',
       'contextlevel' => CONTEXT_MODULE,
       'legacy' => array(
           'guest' => CAP_PREVENT,
           'student' => CAP_PREVENT,
           'teacher' => CAP_ALLOW,
           'editingteacher' => CAP_ALLOW,
           'coursecreator' => CAP_PREVENT,
           'admin' => CAP_ALLOW
       )
   ),

   'mod/programming:viewreport' => array(
       'captype' => 'read',
       'contextlevel' => CONTEXT_MODULE,
       'legacy' => array(
           'guest' => CAP_PREVENT,
           'student' => CAP_ALLOW,
           'teacher' => CAP_ALLOW,
           'editingteacher' => CAP_ALLOW,
           'coursecreator' => CAP_ALLOW,
           'admin' => CAP_ALLOW
       )
   ),

   'mod/programming:viewresemble' => array(
       'captype' => 'read',
       'contextlevel' => CONTEXT_MODULE,
       'legacy' => array(
           'guest' => CAP_PREVENT,
           'student' => CAP_ALLOW,
           'teacher' => CAP_ALLOW,
           'editingteacher' => CAP_ALLOW,
           'coursecreator' => CAP_ALLOW,
           'admin' => CAP_ALLOW
       )
   ),
   'mod/programming:editresemble' => array(
       'captype' => 'write',
       'contextlevel' => CONTEXT_MODULE,
       'legacy' => array(
           'guest' => CAP_PREVENT,
           'student' => CAP_PREVENT,
           'teacher' => CAP_PREVENT,
           'editingteacher' => CAP_ALLOW,
           'coursecreator' => CAP_PREVENT,
           'admin' => CAP_ALLOW
       )
   ),

   'mod/programming:updateresemble' => array(
       'captype' => 'write',
       'contextlevel' => CONTEXT_MODULE,
       'legacy' => array(
           'guest' => CAP_PREVENT,
           'student' => CAP_PREVENT,
           'teacher' => CAP_PREVENT,
           'editingteacher' => CAP_ALLOW,
           'coursecreator' => CAP_PREVENT,
           'admin' => CAP_ALLOW
       )
   ),

   'mod/programming:rejudge' => array(
       'captype' => 'write',
       'contextlevel' => CONTEXT_MODULE,
       'legacy' => array(
           'guest' => CAP_PREVENT,
           'student' => CAP_PREVENT,
           'teacher' => CAP_PREVENT,
           'editingteacher' => CAP_ALLOW,
           'coursecreator' => CAP_PREVENT,
           'admin' => CAP_ALLOW
       )
   ),

);

?>
