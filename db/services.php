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

$functions = array(
    'local_teflacademyconnector_process_request' => array(
        'classname'   => 'local_teflacademyconnector_external',
        'methodname'  => 'process_teflacademy_request',
        'classpath'   => 'local/teflacademyconnector/externallib.php',
        'description' => 'Receives data from The TEFL Academy website and initiates an enrolment with The TEFL Academy Enrolment plugin',
        'type'        => 'write',
    )
);

$services = array(
    'The TEFL Academy website connector' => array(
        'functions'       => array('local_teflacademyconnector_process_request'),
        'restrictedusers' => 0,
        'enabled'         => 1,
        'shortname'       => 'teflacademyconnector',
        'downloadfiles'   => 1
    )
);
