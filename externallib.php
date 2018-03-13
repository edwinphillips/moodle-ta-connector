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
 * @package    local
 * @subpackage teflacademyconnector
 * @author     Ed Phillips <ed@theteflacademy.com>
 * @copyright  The TEFL Academy 2017 <https://www.theteflacademy.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/user/lib.php');

define('LOCAL_TEFLACADEMYCONNECTOR_STUDENT_SHORTNAME', 'student');

class local_teflacademyconnector_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function process_teflacademy_request_parameters() {

        return new external_function_parameters(
            array(
                'userdata'          => new external_single_structure(
                    array(
                        'username'  => new external_value(PARAM_TEXT),
                        'password'  => new external_value(PARAM_TEXT),
                        'firstname' => new external_value(PARAM_TEXT),
                        'lastname'  => new external_value(PARAM_TEXT),
                        'email'     => new external_value(PARAM_TEXT),
                        'city'      => new external_value(PARAM_TEXT),
                        'country'   => new external_value(PARAM_TEXT),
                        'idnumber'  => new external_value(PARAM_TEXT),
                        'phone'     => new external_value(PARAM_TEXT),
                    )
                ),
                'courseidnumber' => new external_value(PARAM_TEXT),
                'taorderid'      => new external_value(PARAM_TEXT),
                'tacourseid'     => new external_value(PARAM_TEXT),
                'tacourseinfo'   => new external_value(PARAM_TEXT),
            )
        );
    }

    /**
     * Processes website enrolment request.
     *
     * @param array $userdata
     * @param text $courseidnumber
     * @param text $taorderid
     * @param text $tacourseid
     * @param text $tacourseinfo
     *
     * @return bool success or failure
     */
    public static function process_teflacademy_request($userdata, $courseidnumber, $taorderid, $tacourseid, $tacourseinfo) {
        global $USER, $DB, $CFG;

        $params = self::validate_parameters(
            self::process_teflacademy_request_parameters(),
            array(
                'userdata'       => $userdata,
                'courseidnumber' => $courseidnumber,
                'taorderid'      => $taorderid,
                'tacourseid'     => $tacourseid,
                'tacourseinfo'   => $tacourseinfo
            )
        );

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $now = time();

        // Get course Id for requested enrolment.
        if ($course = $DB->get_record('course', array('idnumber' => $courseidnumber))) {

            // Get/Create user.
            if (!$user = $DB->get_record('user', array('email' => $userdata['email']))) {

                $user = new stdClass();
                $user->username     = $userdata['username'];
                $user->password     = $userdata['password'];
                $user->firstname    = $userdata['firstname'];
                $user->lastname     = $userdata['lastname'];
                $user->email        = $userdata['email'];
                $user->city         = $userdata['city'];
                $user->country      = $userdata['country'];
                $user->idnumber     = $userdata['idnumber'];
                $user->phone1       = $userdata['phone'];
                $user->mnethostid   = $CFG->mnet_localhost_id;
                $user->confirmed    = 1;

                $userid = user_create_user($user, true);
            } else {

                $userid = $user->id;
            }

            // Get course context.
            $coursecontext = context_course::instance($course->id);

            // Check that the user does not already have an enrolment in the course.
            if (!is_enrolled($coursecontext, $userid, '', true)) {

                // Identify manual enrolment instance.
                $enrolinstance = $DB->get_record('enrol', array('courseid' => $course->id, 'enrol' => 'manual'), '*', MUST_EXIST);

                // Enrol user as a student using the enrolment instance.
                $roleid = $DB->get_field('role', 'id', array('shortname' => LOCAL_TEFLACADEMYCONNECTOR_STUDENT_SHORTNAME));

                $timestart = mktime(0, 0, 0, date('n', $now), date('j', $now), date('Y', $now));
                $timeend = strtotime('+6 month', $timestart);

                $enrol = enrol_get_plugin('manual');
                $enrol->enrol_user($enrolinstance, $userid, $roleid, $timestart, $timeend);

                // Record activity.
                $record = new stdClass();
                $record->userid       = $userid;
                $record->orderid      = $taorderid;
                $record->courseid     = $course->id;
                $record->tacourseid   = $tacourseid;
                $record->tacourseinfo = $tacourseinfo;
                $record->timestamp    = $now;

                $DB->insert_record('local_teflacademyconnector', $record);

                return true;
            }
        }

        return false;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function process_teflacademy_request_returns() {

        return new external_value(PARAM_BOOL);
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function return_current_enrolment_enddate_parameters() {
        return new external_function_parameters(
            array(
                'email' => new external_value(PARAM_TEXT)
            )
        );
    }

    /**
     * Returns course and enrolments for user by passed email address.
     *
     * @param text $email
     * @return array
     */
    public static function return_current_enrolment_enddate($email) {
        global $USER, $DB;

        $params = self::validate_parameters(
            self::return_current_enrolment_enddate_parameters(),
            array(
                'email' => $email
            )
        );

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $sql = "SELECT ue.id, c.id AS courseid, c.fullname AS coursename,
                       ue.status, ue.timestart AS enrolmentstart, ue.timeend AS enrolmentend
                  FROM {user_enrolments} ue
                  JOIN {user} u ON u.id = ue.userid
                  JOIN {enrol} e ON e.id = ue.enrolid
                  JOIN {course} c ON c.id = e.courseid
                 WHERE u.email = ?";

        return $DB->get_records_sql($sql, array($email));
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     */
    public static function return_current_enrolment_enddate_returns() {

        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT),
                    'courseid' => new external_value(PARAM_INT),
                    'coursename' => new external_value(PARAM_TEXT),
                    'status' => new external_value(PARAM_INT),
                    'enrolmentstart' => new external_value(PARAM_INT),
                    'enrolmentend' => new external_value(PARAM_INT)
                )
            )
        );
    }

    /**
     * Returns description of method parameters.
     *
     * @return external_function_parameters
     */
    public static function extend_enrolment_parameters() {

        return new external_function_parameters(
            array(
                'enrolmentdata' => new external_single_structure(
                    array(
                        'courseid'  => new external_value(PARAM_INT),
                        'ueid'      => new external_value(PARAM_INT),
                        'status'    => new external_value(PARAM_INT),
                        'timestart' => new external_value(PARAM_INT),
                        'timeend'   => new external_value(PARAM_INT),
                    )
                ),
            )
        );
    }

    /**
     *
     *
     * @return array
     */
    public static function extend_enrolment($enrolmentdata) {
        global $USER, $DB;

        $params = self::validate_parameters(
            self::extend_enrolment_parameters(),
            array(
                'enrolmentdata' => $enrolmentdata,
            )
        );

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $courseid  = $enrolmentdata['courseid'];
        $ueid      = $enrolmentdata['ueid'];
        $status    = $enrolmentdata['status'];
        $timestart = $enrolmentdata['timestart'];
        $timeend   = $enrolmentdata['timeend'];

        $enrolinstance = $DB->get_record('enrol', array('courseid' => $courseid, 'enrol' => 'manual'), '*', MUST_EXIST);
        $userid = $DB->get_field('user_enrolments', 'userid', array('id' => $ueid));

        $enrol = enrol_get_plugin('manual');
        $enrol->update_user_enrol($enrolinstance, $userid, $status, $timestart, $timeend);

        return true;
    }

    /**
     * Returns description of method result value.
     *
     * @return external_description
     */
    public static function extend_enrolment_returns() {

        return new external_value(PARAM_BOOL);
    }
}
