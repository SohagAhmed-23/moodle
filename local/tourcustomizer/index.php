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
 * Tour customizer main page - Manage tour auto-reset settings.
 *
 * @package    local_tourcustomizer
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

// Check login and capabilities.
require_login();
require_capability('local/tourcustomizer:manage', context_system::instance());

// Get parameters.
$action = optional_param('action', '', PARAM_ALPHA);
$tourid = optional_param('tourid', 0, PARAM_INT);

// Set up the page.
$PAGE->set_url(new moodle_url('/local/tourcustomizer/index.php'));
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('pluginname', 'local_tourcustomizer'));
$PAGE->set_heading(get_string('pluginname', 'local_tourcustomizer'));

// Handle actions.
if ($action && confirm_sesskey()) {
    switch ($action) {
        case 'enable':
            if ($tourid) {
                \local_tourcustomizer\manager::enable_tour_reset($tourid);
                redirect($PAGE->url, get_string('tourresetenabled', 'local_tourcustomizer'), null, 
                    \core\output\notification::NOTIFY_SUCCESS);
            }
            break;
        case 'disable':
            if ($tourid) {
                \local_tourcustomizer\manager::disable_tour_reset($tourid);
                redirect($PAGE->url, get_string('tourresetdisabled', 'local_tourcustomizer'), null, 
                    \core\output\notification::NOTIFY_SUCCESS);
            }
            break;
    }
}

// Output starts here.
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('managetourresets', 'local_tourcustomizer'));

echo html_writer::tag('p', get_string('managetourresets_desc', 'local_tourcustomizer'));

// Get all tours.
$tours = \tool_usertours\helper::get_tours();

if (empty($tours)) {
    echo $OUTPUT->notification(get_string('notoursfound', 'local_tourcustomizer'), 
        \core\output\notification::NOTIFY_INFO);
} else {
    // Get enabled tours from our table.
    $enabledtours = \local_tourcustomizer\manager::get_enabled_tour_ids();

    // Create the table.
    $table = new html_table();
    $table->head = [
        get_string('tourname', 'local_tourcustomizer'),
        get_string('tourdescription', 'local_tourcustomizer'),
        get_string('tourstatus', 'local_tourcustomizer'),
        get_string('autoresetstatus', 'local_tourcustomizer'),
        get_string('actions'),
    ];
    $table->attributes['class'] = 'generaltable';
    $table->data = [];

    foreach ($tours as $tour) {
        $tourname = $tour->get_name();
        $splitname = explode(',', $tourname, 2);
        if (count($splitname) == 2) {
            $tourname = get_string(trim($splitname[0]), trim($splitname[1]));
        }
        $tourdescription = $tour->get_description();
        $splitdesc = explode(',', $tourdescription, 2);
        if (count($splitdesc) == 2) {
            $tourdescription = get_string(trim($splitdesc[0]), trim($splitdesc[1]));
        }
        
        $tourenabled = $tour->get_enabled() ? get_string('enabled', 'local_tourcustomizer') : 
            get_string('disabled', 'local_tourcustomizer');

        $isenabled = in_array($tour->get_id(), $enabledtours);
        
        if ($isenabled) {
            $statusbadge = html_writer::tag('span', get_string('enabled', 'local_tourcustomizer'), 
                ['class' => 'badge badge-success']);
            $actionurl = new moodle_url('/local/tourcustomizer/index.php', [
                'action' => 'disable',
                'tourid' => $tour->get_id(),
                'sesskey' => sesskey()
            ]);
            $actiontext = get_string('disable');
            $actionclass = 'btn btn-sm btn-warning';
        } else {
            $statusbadge = html_writer::tag('span', get_string('disabled', 'local_tourcustomizer'), 
                ['class' => 'badge badge-secondary']);
            $actionurl = new moodle_url('/local/tourcustomizer/index.php', [
                'action' => 'enable',
                'tourid' => $tour->get_id(),
                'sesskey' => sesskey()
            ]);
            $actiontext = get_string('enable');
            $actionclass = 'btn btn-sm btn-success';
        }

        $actionbutton = html_writer::link($actionurl, $actiontext, ['class' => $actionclass]);

        $table->data[] = [
            $tourname,
            $tourdescription,
            $tourenabled,
            $statusbadge,
            $actionbutton,
        ];
    }

    echo html_writer::table($table);
}

// Add link to scheduled tasks.
$taskurl = new moodle_url('/admin/tool/task/scheduledtasks.php');
echo html_writer::tag('p', html_writer::link($taskurl, get_string('viewscheduledtasks', 'local_tourcustomizer')));

echo $OUTPUT->footer();
