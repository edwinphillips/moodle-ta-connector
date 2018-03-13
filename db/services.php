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

$services = array(
    'The TEFL Academy website connector' => array(
        'functions'          => array(
            'local_teflacademyconnector_process_request',
            'local_teflacademyconnector_return_current_enrolment_enddate',
            'local_teflacademyconnector_extend_enrolment'
        ),
        'requiredcapability' => 'local/teflacademyconnector:processrequest',
        'restrictedusers'    => 0,
        'enabled'            => 1,
        'shortname'          => 'teflacademyconnector',
        'downloadfiles'      => 1
    )
);

$functions = array(
    'local_teflacademyconnector_process_request' => array(
        'classname'   => 'local_teflacademyconnector_external',
        'methodname'  => 'process_teflacademy_request',
        'classpath'   => 'local/teflacademyconnector/externallib.php',
        'description' => 'Receives data from The TEFL Academy website, creates a Moodle user if neccessary, and initiates a course enrolment of type manual into the requested course.',
        'type'        => 'write',
    ),
    'local_teflacademyconnector_return_current_enrolment_enddate' => array(
        'classname'   => 'local_teflacademyconnector_external',
        'methodname'  => 'return_current_enrolment_enddate',
        'classpath'   => 'local/teflacademyconnector/externallib.php',
        'description' => 'Returns a UNIX datastamp value of the enddate of any enrolments the passed user has.',
        'type'        => 'read',
    ),
    'local_teflacademyconnector_extend_enrolment' => array(
        'classname'   => 'local_teflacademyconnector_external',
        'methodname'  => 'extend_enrolment',
        'classpath'   => 'local/teflacademyconnector/externallib.php',
        'description' => 'Extends or re-opens a specific user enrolment by 90 days.',
        'type'        => 'write',
    ),
);
