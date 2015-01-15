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
 * course unenrolled User Filter tool.
 *
 * @package    tool
 * @subpackage user filter
 * @copyright  2015 dualcube {@link http://dualcube.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once('../../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/'.$CFG->admin.'/tool/user_filter/role_form.php');
require_once($CFG->libdir.'/weblib.php');

require_login();
admin_externalpage_setup( 'tooluser_filter' );

global $DB;

echo $OUTPUT->header();

$roleform = new tool_user_filter_config_form();

$roleform->display();

if (isset($roleform->nform->_submitValues['role'])) {
    if ($roleform->nform->_submitValues['role'] > 0) {
        $roleid = $roleform->nform->_submitValues['role'];
        $nameusername = $DB->get_records_sql('SELECT u.id, u.firstname, u.lastname
                                              FROM {user} u
        																			JOIN  {role_assignments} ra ON u.id = ra.userid 
        																			WHERE ra.roleid = ? GROUP BY u.id' , array($roleid));
        $tabledata = array();
        foreach ($nameusername as $name) {
            $tabledata[] = array(html_writer::empty_tag('input' ,  array('type' => 'checkbox' , 'name' => $name->id ,
              'id' => 'delete_id' , 'value' => $name->id )) , $name->id , $name->firstname.' '.$name->lastname);
        }

        $table = new html_table();
        $table->head = array('Select', 'id', 'Username');
        $table->data = $tabledata;
        echo html_writer::start_tag('form', array('action' => 'index.php', 'method' => 'post'));
        echo html_writer::table($table);
        echo html_writer::tag('input' , '' , array('id' => 'delete_user' ,
          'name' => 'delete_name' , 'value' => 'Delete user' , 'type' => 'submit'));
        echo html_writer::end_tag('form');
    } else if ($roleform->nform->_submitValues['role'] == 0) {
        $nameusersname = $DB->get_records_sql('SELECT id, firstname, lastname
        																			FROM {user}
        																			WHERE deleted = 0 AND id NOT IN (SELECT userid FROM {role_assignments})'
                                              );
        $datatable = array();
        foreach ($nameusersname as $usersname) {
                $datatable[] = array(html_writer::empty_tag('input' ,
                  array('type' => 'checkbox' , 'name' => $usersname->id ,
                'id' => 'delete_id' , 'value' => $usersname->id )) , $usersname->id ,
                $usersname->firstname .' '. $usersname->lastname);
        }
        $table = new html_table();
        $table->head = array('Select', 'id', 'Username');
        $table->data = $datatable;
        echo html_writer::start_tag('form', array('action' => 'index.php', 'method' => 'post'));
        echo html_writer::table($table);
        echo html_writer::tag('input' , '' , array('id' => 'delete_user' , 'name' => 'delete_name' ,
          'value' => 'Delete user' , 'type' => 'submit'));
        echo html_writer::end_tag('form');
    }
}

$data = (array)data_submitted();

if (isset($data['delete_name'])) {
    $flag = 0;
    $delvalue = array();
    echo html_writer::start_tag('form', array('action' => 'index.php', 'method' => 'post'));
    foreach ($data as $key => $userdata) {
        if ($key == 'delete_name') {
            continue;
        }
        $flag = 1;
        echo html_writer::tag('input' , '' , array('name' => $key , 'value' => $userdata , 'type' => 'hidden'));
        $delvalue[] = $key;
    }
    foreach ($delvalue as $deluser) {
        $role[] = $DB->get_record('user' , array('id' => $deluser));
    }
    $tabledelete = array();
    foreach ($role as $roleusers) {
        $tabledelete[] = array(html_writer::tag('div', $roleusers->firstname .' '. $roleusers->lastname));
    }
    $table = new html_table();
    $table->head = array('Username');
    $table->data = $tabledelete;
    echo html_writer::table($table);
    if ($flag == 1) {
        echo get_string('delete' , 'tool_user_filter');
        echo html_writer::start_tag('div');
        echo html_writer::end_tag('div');
        echo html_writer::tag('input' , '' , array('id' => 'del_yes' , 'name' => 'delete_yes' ,
        'value' => 'Yes' , 'type' => 'submit'));
        echo html_writer::tag('input' , '' , array('id' => 'del_no' , 'name' => 'delete_no' ,
        'value' => 'No' , 'type' => 'submit'));
    }
    echo html_writer::end_tag('form');
} else if (isset($data['delete_yes'])) {
    $deleteflag = 0;
    foreach ($data as $key => $userdata) {
        if ($key == 'delete_yes') {
            continue;
        }
        $userdetails = $DB->get_record('user', array('id' => $userdata, 'deleted' => '0'));
        if ($userdetails) {
            delete_user($userdetails);
            $deleteflag = 1;
        } else {
            $deleteflag = 0;
        }
    }
    if ($deleteflag == 1) {
        echo get_string('deleteuser', 'tool_user_filter');
    } else if ($deleteflag == 0) {
        echo get_string('doesnotexist', 'tool_user_filter');
    }
}

echo $OUTPUT->footer();


