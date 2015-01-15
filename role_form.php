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
 * Form for User Filter role setting 
 *
 * @package    tool
 * @subpackage user filter
 * @copyright  2015 dualcube {@link http://dualcube.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot . '/user/editlib.php');

global $table;
class tool_user_filter_config_form extends moodleform {
    public function definition () {
        global $DB;

        $mform = $this->_form;
        $this->nform = $this->_form;
        $mform->addElement('header', 'configheader', get_string('setting', 'tool_user_filter'));
        $id = array();
        $roleuser = array();
        $roleuser['0'] = 'No role';
        $role = $DB->get_records_sql('SELECT * FROM {role}');
        foreach ($role as $key => $rolename) {
            $roleuser[$rolename->id] = $rolename->shortname;
        }
        $mform->addElement('select' , 'role' , get_string('roleuser' , 'tool_user_filter') , $roleuser);
        $this->add_action_buttons();
    }
}

