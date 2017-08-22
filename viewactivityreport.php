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

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/local/teflacademyconnector/lib.php');
require_once($CFG->libdir . '/adminlib.php');

$context = context_system::instance();
require_capability('local/teflacademyconnector:viewactivityreport', $context);

$PAGE->set_context($context);
$PAGE->set_heading(format_string($SITE->fullname));
$PAGE->set_title(format_string($SITE->fullname) . ': ' . get_string('pluginname', 'local_teflacademyconnector'));
$PAGE->set_url('/local/teflacademyconnector/viewactivityreport.php');
$PAGE->set_pagetype('admin-teflacademyconnector');

admin_externalpage_setup('teflacademyconnectoractivityreport');

$renderer = $PAGE->get_renderer('local_teflacademyconnector');

echo $OUTPUT->header();

echo $renderer->heading(get_string('tawebsiteconnectoractivityreport', 'local_teflacademyconnector'));

$activityreport = local_teflacademyconnector_get_activityreport();
echo $renderer->list_activityreport($activityreport);

echo $OUTPUT->footer();
