# PROGRAMMING ACTIVITY

## Database Dictionary

### programming

This is the main table for the programming activity.

* id: ID of programming activity
* course: ID of the course which contains programming
* name: name of the programming task
* description: the description of the task
* descformat: the format of the description
* grade: the max grade of the programming task
* timeopen: The time(stamp) after which students can see the description of
    programming task
* timeclose: The time(stamp) before which students should submit programs.
* timelimit: In how many seconds should the program finish.
* memlimit: The maximum memory (KB) the program can use.
* allowlate: If true, students are allowed to submit programs.
* attempts: If not zero, the maximum times can a student post his/her program.
* generator: A python program which can generate testcase. (NOT USED)
* validator: A python program which check if the result of the program
    submitted by students correct.

### programming_submits

In this table, the program written by students are saved.

* id: ID of the submit.
* programmingid: ID of the programming task the submit belong to.
* userid: The owner of the submit.
* timemodified: The time(stamp) when the program is submitted.
* language: The langugae the program is written in.
* code: The program code.
* status: Processing status of the judging program.
  0. new, not processed.
  1. compiling, the program is in the compile queue of one of the judge program
  2. compile ok, the program is compiled with our error.
  3. running, the program is in the test queue of one of the judge program
  10. finish, the program is tested, maybe wrong or right.
  11. compile fail, the program is failed in compile.
* compilemessage: The compile message generate by the compiler.

### programming_tests

The testcase of each of the programming task.

* id: ID of the submit
* programmingid: ID of the programming task the teskcase for
* input: The input of the testcase
* output: The output of the testcase
* timelimit: The maximum time in seconds the program can use. This value always
    overwrite the timelimit in programming table. If this value is zero, the
    program won't be interrupted until the maximum setting in the judge program
    exceeded.
* memlimit: The maximum memory in KB the program can use. This value always
    overwrite the memlimit in programming table. If this value is zero, the
    program won't be interrupted until the maximum setting in the judge program
    execced. (NOT USED)
* pub: Is this testcase public(should be show to students)

### programming_test_results

This table stores test result of each of the submit in every testcase.

* id: ID of the test result
* submitid: the submit this test result belong to
* testid: ID of the correspond testcase of the test result
* passed: Is the program passed the test
* output: The output of the program
* timeused: How many seconds did the program used in the test.

### programming_testers

This table is used by judge program. Several judge program can be runned in
parallel mode, and the ID of the judge program and ID of the processing submit
is stored in this table. When the first judge program runs, it create this
table and insert -1 and 1 to the table. When the second judge program runs, it
will insert -1 and 2 to the table.

* submitid: ID of the submit
* testerid: ID of the tester program
