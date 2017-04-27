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

require_once(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');

$courseid = required_param('courseid', PARAM_INT);

// The id of the course we are importing FROM
$course = $DB->get_record('course', array('id' => $courseid), 'id', MUST_EXIST);

$student_archetype = get_archetype_roles('student');
$student_role = array_shift($student_archetype);
require_login();
require_capability('moodle/course:manageactivities', context_course::instance($courseid));

$enrolself = $DB->get_record('enrol', array('courseid' => $course->id, 'enrol' => 'self', 'status' => 0, 'roleid' => $student_role->id));

$course->enrolpassword = $enrolself->password;

header("Content-type: application/json");
echo json_encode($course);

