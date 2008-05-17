<?PHP

// THIS FILE IS DEPRECATED!  PLEASE DO NOT MAKE CHANGES TO IT!
//
// IT IS USED ONLY FOR UPGRADES FROM BEFORE MOODLE 1.7, ALL 
// LATER CHANGES SHOULD USE upgrade.php IN THIS DIRECTORY.

function programming_upgrade($oldversion) {
/// This function does anything necessary to upgrade 
/// older versions to match current functionality 

    global $CFG;

    if ($oldversion < 2005090101) {

        execute_sql("ALTER TABLE `{$CFG->prefix}programming_tests` ADD `weight` TINYINT(3) NOT NULL DEFAULT '3'");

    }

    if ($oldversion < 2005100103) {

        execute_sql("ALTER TABLE `{$CFG->prefix}programming` ADD `keeplatestonly` TINYINT(3) NOT NULL DEFAULT 0 AFTER attempts");

    }

    if ($oldversion < 2006030412) {

        execute_sql("ALTER TABLE `{$CFG->prefix}programming` ADD `timediscount` INT(10) NOT NULL DEFAULT '130000000' AFTER `memlimit`");
        execute_sql("ALTER TABLE `{$CFG->prefix}programming` ADD `discount` FLOAT NOT NULL DEFAULT '8' AFTER `timediscount`");

    }

    if ($oldversion < 2006040200) {

        // Add new columns to test results
        execute_sql("ALTER TABLE `{$CFG->prefix}programming_test_results` ADD `status` INT(10) NOT NULL DEFAULT '0' AFTER `passed`");
        execute_sql("ALTER TABLE `{$CFG->prefix}programming_test_results` ADD `stderr` TEXT NULL AFTER `output`");
        execute_sql("ALTER TABLE `{$CFG->prefix}programming_test_results` CHANGE `output` `output` TEXT NULL");

        // Change the type of column language of submits
        execute_sql("UPDATE `{$CFG->prefix}programming_submits` set language=1 where language='c89' or language='c99'");
        execute_sql("UPDATE `{$CFG->prefix}programming_submits` set language=2 where language='c++98'");
        execute_sql("ALTER TABLE `{$CFG->prefix}programming_submits` CHANGE `language` `language` INT(10) NULL");

        // Create a table for languages
        execute_sql("CREATE TABLE {$CFG->prefix}programming_languages ( id int(10) NOT NULL auto_increment, name varchar(20) NOT NULL, PRIMARY KEY (id)) COMMENT='programming language'");
        execute_sql("INSERT INTO {$CFG->prefix}programming_languages VALUES (1, 'gcc-3.3')");
        execute_sql("INSERT INTO {$CFG->prefix}programming_languages VALUES (2, 'g++-3.3')");

    }

    if ($oldversion < 2006040312) {
        // Add new columns to test results
        execute_sql("ALTER TABLE `{$CFG->prefix}programming_test_results` ADD exitcode TINYINT(3) NOT NULL DEFAULT '0' AFTER `status`");
        execute_sql("ALTER TABLE `{$CFG->prefix}programming_test_results` ADD signal TINYINT(3) NOT NULL DEFAULT '0' AFTER `exitcode`");
        execute_sql("ALTER TABLE `{$CFG->prefix}programming_test_results` DROP `status`");
    }

    if ($oldversion < 2006040512) {
        execute_sql("
        CREATE TABLE {$CFG->prefix}programming_langlimit (
          id int(10) NOT NULL AUTO_INCREMENT,
          programmingid int(10) NOT NULL,
          languageid int(10) NOT NULL,
          PRIMARY KEY (id),
          UNIQUE KEY programminglanguage(programmingid, languageid),
          UNIQUE KEY languageprogramming(languageid, programmingid)
        ) COMMENT='programming language limit';
        ");
    }

    if ($oldversion < 2006040617) {
        execute_sql("ALTER TABLE `{$CFG->prefix}programming_tests` CHANGE `input` `input` MEDIUMTEXT NOT NULL");
        execute_sql("ALTER TABLE `{$CFG->prefix}programming_tests` CHANGE `output` `output` MEDIUMTEXT NOT NULL");
    }

    if ($oldversion < 2006062300) {
        execute_sql("
          CREATE TABLE `{$CFG->prefix}programming_resemble` (
            id int(10) NOT NULL auto_increment,
            programmingid int(10) NOT NULL default '0',
            matchedcount int(4) NOT NULL default '0',
            matchedlines text,
            submitid1 int(10) NOT NULL default '0',
            percent1 int(2) NOT NULL default '0',
            submitid2 int(10) NOT NULL default '0',
            percent2 int(2) NOT NULL default '0',
            flag tinyint(2) NOT NULL default '0',
            PRIMARY KEY (id),
            KEY proglines (programmingid, flag, matchedcount)
          ) COMMENT='resemble info returned by moss';
        ");
    }

    if ($oldversion < 2006070301) {
        execute_sql("ALTER TABLE `{$CFG->prefix}programming` ADD `generatortype` tinyint(1) NOT NULL DEFAULT '0'");
        execute_sql("ALTER TABLE `{$CFG->prefix}programming` CHANGE `generator` `generator` TEXT");
        execute_sql("ALTER TABLE `{$CFG->prefix}programming` ADD `validatortype` tinyint(1) NOT NULL DEFAULT '0'");
        execute_sql("ALTER TABLE `{$CFG->prefix}programming` CHANGE `validator` `validator` TEXT");
    }

    if ($oldversion < 2006112801) {
        execute_sql("ALTER TABLE `{$CFG->prefix}programming_submits` ADD `codelines` int(10) NOT NULL default '0' AFTER `code`");
        execute_sql("ALTER TABLE `{$CFG->prefix}programming_submits` ADD `codesize` int(10) NOT NULL default '0' AFTER `codelines`");
        execute_sql("UPDATE `{$CFG->prefix}programming_submits` SET codesize = CHAR_LENGTH(code)");
        execute_sql("UPDATE `{$CFG->prefix}programming_submits` SET codelines = codesize - CHAR_LENGTH(REPLACE(code, '\n', ''))");
    }

    if ($oldversion < 2006112802) {
        execute_sql("ALTER TABLE `{$CFG->prefix}programming` ADD `showmode` TINYINT(1) NOT NULL DEFAULT '1'");
    }

    if ($oldversion < 2006121001) {
        execute_sql("ALTER TABLE `{$CFG->prefix}programming_test_results` CHANGE `output` `output` TEXT CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL");
        execute_sql("ALTER TABLE `{$CFG->prefix}programming_test_results` CHANGE `stderr` `stderr` TEXT CHARACTER SET utf8 COLLATE utf8_bin NULL DEFAULT NULL");
    }

    return true;
}

?>
