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

/**
 * Returns details from the The TEFL Academy Website Connector report table
 *
 * @return array
 */
function local_teflacademyconnector_get_activityreport() {
    global $DB;

    $sql = "SELECT t.id, u.id AS userid, u.firstnamephonetic, u.lastnamephonetic, u.middlename, u.alternatename,
                   u.firstname, u.lastname, c.id AS courseid, c.fullname AS course, t.orderid, t.timestamp
              FROM {local_teflacademyconnector} t
              JOIN {user} u ON u.id = t.userid
              JOIN {course} c ON c.id = t.courseid
          ORDER BY id DESC";

    return $DB->get_records_sql($sql);
}
