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
                        'idnumber' => new external_value(PARAM_TEXT),
                        'phone'     => new external_value(PARAM_TEXT),
                    )
                ),
                'courseidnumber' => new external_value(PARAM_TEXT),
                'taorderid'      => new external_value(PARAM_TEXT),
                'tacourseid'     => new external_value(PARAM_TEXT),
            )
        );
    }

    /**
     * Returns success or failure
     *
     * @return bool success or failure
     */
    public static function process_teflacademy_request($userdata, $courseidnumber, $taorderid, $tacourseid) {
        global $USER, $DB, $CFG;

        $params = self::validate_parameters(
            self::process_teflacademy_request_parameters(),
            array(
                'userdata'       => $userdata,
                'courseidnumber' => $courseidnumber,
                'taorderid'      => $taorderid,
                'tacourseid'     => $tacourseid,
            )
        );

        $context = context_user::instance($USER->id);
        self::validate_context($context);

        $now = time();

        if (!$user = $DB->get_record('user', array('email' => $userdata['email']))) {

            $user = new stdClass();

            // Todo: add a username check; autogenerate username vv -- where does username come from?
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

            if ($userid = user_create_user($user, true)) {
                // anthing required here?
            }

        } else {

            $userid = $user->id;
        }

        // Get course Id for requested enrolment.
        if ($course = $DB->get_record('course', array('idnumber' => $courseidnumber))) {

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
            $record->userid     = $userid;
            $record->orderid    = $taorderid;
            $record->courseid   = $course->id;
            $record->tacourseid = $tacourseid;
            $record->timestamp  = $now;

            $DB->insert_record('local_teflacademyconnector', $record);

        } else {
            // no such course ... ?
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
