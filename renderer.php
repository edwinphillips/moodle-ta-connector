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

require_once($CFG->libdir.'/tablelib.php');
require_once($CFG->dirroot.'/user/profile/lib.php');

class local_teflacademyconnector_renderer extends core_renderer {

    public function list_activityreport($activityreport) {
        global $CFG;

        $table = new flexible_table('local-teflacademyconnector-activity-list');
        $table->define_columns(array('user', 'orderid', 'timestamp'));

        $table->define_headers(array(
            get_string('user'),
            get_string('orderid', 'local_teflacademyconnector'),
            get_string('timestamp', 'local_teflacademyconnector')
        ));
        $table->define_baseurl(new moodle_url('/local/teflacademyconnector/viewactivityreport.php'));

        $table->sortable(false);
        $table->collapsible(false);

        $table->column_class('user', 'user');
        $table->column_class('orderid', 'orderid');
        $table->column_class('timestamp', 'timestamp');

        $table->set_attribute('cellspacing', '0');
        $table->set_attribute('id', 'local-teflacademyconnector-activity-list');
        $table->set_attribute('class', 'local-teflacademyconnector-activity-list generaltable');
        $table->set_attribute('width', '100%');
        $table->setup();

        if ($activityreport) {

            $user = new stdClass();
            foreach ($activityreport as $activity) {

                $user->id                = $activity->userid;
                $user->firstname         = $activity->firstname;
                $user->lastname          = $activity->lastname;
                $user->firstnamephonetic = $activity->firstnamephonetic;
                $user->lastnamephonetic  = $activity->lastnamephonetic;
                $user->middlename        = $activity->middlename;
                $user->alternatename     = $activity->alternatename;

                $row = array();

                $userurl = new moodle_url($CFG->wwwroot . '/user/profile.php', array('id' => $user->id));
                $row[] = html_writer::link($userurl, fullname($user), array('title' => get_string('viewprofile')));
                $row[] = 'OID' . $activity->orderid;
                $row[] = userdate($activity->timestamp, get_string('strftimedatetime'));

                $table->add_data($row);
            }
        }

        $table->print_html();
    }
}
