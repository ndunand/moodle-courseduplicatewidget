<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version information
 *
 * @package    block
 * @subpackage teacherchoice
 * @copyright  Univertisé de Lausanne, RISET ( http://www.unil.ch/riset )
 * @author     Nicolas Dunand <Nicolas.Dunand@unil.ch>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('TEACHERROLES', '3,4,9'); // including Assistants
define('STUDENTROLE', 5);

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');
require_once($CFG->dirroot . '/course/externallib.php');

// The id of the course we are importing FROM
if (!isset($_GET['courseid'])) {
    die ('no courseid specified!');
}
$importcourseid = (int) $_GET['courseid'];
$importcourse = $DB->get_record('course', array('id' => $importcourseid)) or die('no such course ID!');

// The id of category where we want to place the CLONED course into.
if (!isset($_GET['categoryid'])) {
    die ('no categoryid specified!');
}
$categoryid = (int) $_GET['categoryid'];
$category = $DB->get_record('course_categories', array('id' => $categoryid)) or die('no such course category ID!');

// go to new couse upon success?
$gotonewcourse = isset($_GET['gotonewcourse']) && $_GET['gotonewcourse'] == 1;

// archive old couse upon success?
$archiveoldcourse = isset($_GET['archiveoldcourse']) && $_GET['archiveoldcourse'] == 1;

// open new course?
$opennewcourse = isset($_GET['opennewcourse']) && $_GET['opennewcourse'] == 1;

// keep teachers enrolled?
$keepteachers = isset($_GET['keepteachers']) && $_GET['keepteachers'] == 1;

// new course name
$fullname = (isset($_GET['fullname'])) ? (urldecode($_GET['fullname'])) : ($importcourse->fullname . ' (copy)');
$shortname = (isset($_GET['shortname'])) ? (urldecode($_GET['shortname'])) : ($importcourse->shortname . '-copy');

// new enrolment key
$enrolpassword = (isset($_GET['enrolpassword'])) ? (urldecode($_GET['enrolpassword'])) : ('');

// keep users?
$users = isset($_GET['users']) && $_GET['users'] == 1;

// If the CLONED course should be visible or not.
$visible = (int)$opennewcourse;

// The CLONEing options (these are the defaults).
$options = array(
    array ('name' => 'activities', 'value' => 1),
    array ('name' => 'blocks', 'value'  => 1),
    array ('name' => 'filters', 'value' => 1),
    array ('name' => 'users', 'value' => $users),
//     array ('name' => 'role_assignments', 'value' => 1),
    array ('name' => 'comments', 'value' => 0),
//     array ('name' => 'userscompletion', 'value' => 1),
    array ('name' => 'logs', 'value' => 0),
//     array ('name' => 'grade_histories', 'value' => 1),
);

$errors = array();

$context1 = context_course::instance($importcourse->id);
$context2 = context_coursecat::instance($category->id);

if (!has_capability('moodle/backup:backupcourse', $context1) || !has_capability('moodle/restore:restorecourse', $context2)) {
    $errors[] = get_string('no_rights', 'block_teacherchoice');
}

if (!count($errors)) {
    try {
        $newcourse = core_course_external::duplicate_course(
            $importcourseid,
            $fullname,
            $shortname,
            $categoryid,
            $visible,
            $options
        );
    } catch (exception $e) {
        // Some debugging information to see what went wrong
    //     echo '<pre>';
    //     print_r($e);
    //     exit;
        if ($e->errorcode === 'shortnametaken') {
            $errors[] = get_string('duplicate_errorcode_shortnametaken', 'block_teacherchoice', $shortname);
        }
        else {
            $errors[] = get_string('duplicate_errorcode', 'block_teacherchoice', $e->errorcode);
        }
    }
}

if (count($errors)) {
    include('./course-duplicate-error.php');
    exit;
}

if ($archiveoldcourse) {
    // try to figure which "Archive" category
    $thiscat = $DB->get_record('course_categories', array('id' => $categoryid));
    if ($thiscat) {
        $archcats = $DB->get_records_sql("SELECT id FROM {course_categories} WHERE path LIKE '{$thiscat->path}%' AND parent = {$thiscat->id} AND name LIKE 'archiv%';");
        if (count($archcats) === 1) {
            // all good, let's archive $importcourse
            $archcat = array_pop($archcats);
            $importcourse->visible = 0;
            $importcourse->category = $archcat->id;
            $DB->update_record('course', $importcourse);
        }
        else {
            $errors[] = get_string('no_archive_category', 'block_teacherchoice');
        }
    }
    else {
        $errors[] = get_string('no_archive_category', 'block_teacherchoice');
    }
}

if ($keepteachers && !$users) {
    // Users were not kept along, so we have to re-enroll teachers
    $enrol = $DB->get_record('enrol', array('enrol' => 'manual', 'status' => 0, 'courseid' => $importcourse->id));
    if ($enrol) {
        // let's make an identical "enrol_manual" plugin into target course
        $newenrol = clone($enrol);
        unset($newenrol->id);
        $newenrol->courseid = $newcourse['id'];
        $newcourseenrol = $DB->get_record('enrol', array('enrol' => 'manual', 'courseid' => $newcourse['id']));
        if ($newcourseenrol) {
            $newenrol->id = $newcourseenrol->id;
            $DB->update_record('enrol', $newenrol);
        }
        else {
            $newenrol->id = $DB->insert_record($newenrol);
        }
    }
    else {
        $errors[] = get_string('no_enrolmanual', 'block_teacherchoice');
    }
    // now, let's figure out who was enrolled...
    $enrolled_users = $DB->get_records('user_enrolments', array('enrolid' => $enrol->id));
    $course_context = context_course::instance($importcourse->id);
    // ...and who among these are teachers
    $teachers = array();
    foreach ($enrolled_users as $enrolled_user) {
        $teachers[] = $DB->get_records_sql("SELECT * FROM {role_assignments} WHERE userid = {$enrolled_user->userid} AND roleid IN (".TEACHERROLES.") AND contextid = {$course_context->id};");
    }
    if (count($teachers)) {
//         $errors[] = 'ct='.count($teachers);
//         $errors[] = '<pre>'.print_r($teachers, true).'</pre>';
        require_once($CFG->dirroot . '/enrol/manual/lib.php');
        $enrol_manual_instance = new enrol_manual_plugin();
        foreach ($teachers as $teacher) {
            $role_assignment = array_pop($teacher);
                if (!is_object($role_assignment)) {
                continue;
            }
            $enrol_manual_instance->enrol_user($newenrol, $role_assignment->userid, $role_assignment->roleid);
        }
    }
    else {
        $errors[] = get_string('no_teacher', 'block_teacherchoice');
    }
}

// update enrol_self password
if ($enrolpassword) {
    $enrolself = $DB->get_record('enrol', array('courseid' => $newcourse['id'], 'enrol' => 'self', 'status' => 0, 'roleid' => STUDENTROLE), 'id');
    $enrolself->password = $enrolpassword;
    $DB->update_record('enrol', $enrolself);
}

if (count($errors)) {
    include('./course-duplicate-error.php');
    exit;
}

$returnurl = optional_param('returnurl', '', PARAM_URL);
if (!$returnurl) {
    $gotocourse = ($gotonewcourse) ? ($newcourse['id']) : ($importcourse->id);
    $returnurl = $CFG->wwwroot.'/course/view.php?id='.$gotocourse;
}
else {
    // back to block_teacherchoice report, so log success
    include_once($CFG->dirroot . '/blocks/teacherchoice/locallib.php');
    $logtxt = get_string('courseduplicated', 'block_teacherchoice') . ' ' . '(' . html_writer::link(new moodle_url('/course/view.php', ['id' => $newcourse['id']]), get_string('view')) . ')';
    block_teacherchoice_addtolog((int)$importcourse->id, time(), $USER->id, $logtxt);
}

header('Location: ' . $returnurl);

