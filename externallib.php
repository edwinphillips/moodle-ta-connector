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
require_once($CFG->dirroot . '/local/teflacademyconnector/lib.php');

class local_teflacademyconnector_external extends external_api {

    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function process_teflacademy_request_parameters() {

        return new external_function_parameters(
            array(
                'order_number' => new external_value(PARAM_TEXT),
                'user'         => new external_single_structure(
                    array(
                        'firstname' => new external_value(PARAM_TEXT),
                        'lastname'  => new external_value(PARAM_TEXT),
                        'email'     => new external_value(PARAM_TEXT),
                        'city'      => new external_value(PARAM_TEXT),
                        'country'   => new external_value(PARAM_TEXT),
                        'username'  => new external_value(PARAM_TEXT),
                        'password'  => new external_value(PARAM_TEXT)
                    )
                ),
                'courses' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'idnumber' => new external_value(PARAM_TEXT)
                        )
                    )
                )
            )
        );
    }

    /**
     * Returns success or failure
     *
     * @return bool success or failure
     */
    public static function process_teflacademy_request($order_number, $userdata, $coursesdata) {
        global $USER, $DB;

        $params = self::validate_parameters(
            self::process_teflacademy_request_parameters(),
            array(
                'order_number' => $order_number,
                'user'         => $userdata,
                'courses'      => $coursesdata
            )
        );

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        if (!$user = $DB->get_record('user', array('email' => $userdata['email']))) {

            $user = new stdClass();
            $user->firstname    = $userdata['firstname'];
            $user->lastname     = $userdata['lastname'];
            $user->email        = $userdata['email'];
            $user->city         = $userdata['city'];
            $user->country      = $userdata['country'];
            // Todo: add a username check; autogenerate username vv
            $user->username     = $userdata['username'];
            $user->password     = hash_internal_user_password($userdata['password']);
            $user->timecreated  = time();
            $user->confirmed    = 1;
            $user->policyagreed = 1;
            $user->mnethostid   = 1;

            if ($userid =  user_create_user($user, true)) {
                // anthing required here?
            }

        } else {

            $userid = $user->id;
        }

        $roleid = $DB->get_field('role', 'id', array('shortname' => LOCAL_TEFLACADEMYCONNECTOR_STUDENT_SHORTNAME));

        $enrol = enrol_get_plugin('manual');

        foreach ($coursesdata as $coursedata) {
            if ($course = $DB->get_record('course', array('idnumber' => $coursedata['course_id']))) {

                $enrolinstance = $DB->get_record('enrol',
                        array('courseid' => $course->id, 'enrol' => 'theteflacademy'), '*', MUST_EXIST);
                $enrol->enrol_user($enrolinstance, $userid, $roleid);

                $record = new stdClass();
                $record->userid    = $userid;
                $record->orderid   = $order_number;
                $record->courseid  = $course->id;
                $record->timestamp = time();
                $DB->insert_record('local_teflacademyconnector', $record);
            } else {
                // no such course ... ?
            }
        }

        return true;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function process_teflacademy_request_returns() {

        return new external_value(PARAM_BOOL);
    }
}
