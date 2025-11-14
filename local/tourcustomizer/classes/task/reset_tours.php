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
 * Scheduled task to reset enabled tours for all users
 *
 * @package    local_tourcustomizer
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tourcustomizer\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Scheduled task to reset enabled tours for all users.
 *
 * @package    local_tourcustomizer
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class reset_tours extends \core\task\scheduled_task {

    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('resettourstask', 'local_tourcustomizer');
    }

    /**
     * Execute the task.
     */
    public function execute() {
        global $DB;

        // Get all enabled tours from our custom table.
        $enabledtours = $DB->get_records('local_tourcustomizer_tours', ['enabled' => 1]);

        if (empty($enabledtours)) {
            mtrace('No tours enabled for auto-reset.');
            return;
        }

        mtrace('Starting tour reset process...');

        foreach ($enabledtours as $record) {
            try {
                // Load the tour instance.
                $tour = \tool_usertours\tour::instance($record->tourid);
                
                // Mark major change - this will clear user preferences and reset the tour for all users.
                $tour->mark_major_change();
                
                mtrace('Successfully reset tour ID: ' . $record->tourid . ' (' . $tour->get_name() . ')');
            } catch (\Exception $e) {
                mtrace('Error resetting tour ID ' . $record->tourid . ': ' . $e->getMessage());
            }
        }

        mtrace('Tour reset process completed.');
    }
}
