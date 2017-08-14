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

defined('MOODLE_INTERNAL') || die;

$systemcontext = context_system::instance();

if (has_any_capability(array('local/teflacademyconnector:manage', 'local/teflacademyconnector:viewtransactions'), $systemcontext)) {
    $ADMIN->add('localplugins', new admin_category('teflacademyconnector', new lang_string('pluginname','local_teflacademyconnector')));
}

if (has_capability('local/teflacademyconnector:manage', $systemcontext)) {
    $settings = new admin_settingpage('teflacademyconnectorsettings', new lang_string('settings'));
    $options = array(1 => get_string('yes'), 0 => get_string('no'));
    $settings->add(new admin_setting_configselect('teflacademyconnector/teflacademyconnectorenabled',
            get_string('enabled', 'local_teflacademyconnector'),
            get_string('enableordisable', 'local_teflacademyconnector'), 1, $options));
    $ADMIN->add('teflacademyconnector', $settings);

}

if (has_capability('local/teflacademyconnector:viewtransactions', $systemcontext)) {
    $ADMIN->add('teflacademyconnector', new admin_externalpage('teflacademyconnectortransactions', get_string('transactions', 'local_teflacademyconnector'),
        $CFG->wwwroot . '/local/teflacademyconnector/viewtransactions.php'));
}
