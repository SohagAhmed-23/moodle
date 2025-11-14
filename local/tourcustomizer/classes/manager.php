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
 * Manager class for tour customizer.
 *
 * @package    local_tourcustomizer
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_tourcustomizer;

defined('MOODLE_INTERNAL') || die();

/**
 * Manager class for tour customizer plugin.
 *
 * @package    local_tourcustomizer
 * @copyright  2025
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class manager {

    /**
     * Enable auto-reset for a specific tour.
     *
     * @param int $tourid The ID of the tour
     * @return bool True if successful
     */
    public static function enable_tour_reset($tourid) {
        global $DB;

        // Check if tour exists.
        if (!\tool_usertours\tour::instance($tourid)) {
            return false;
        }

        // Check if already exists in our table.
        $existing = $DB->get_record('local_tourcustomizer_tours', ['tourid' => $tourid]);

        if ($existing) {
            // Update existing record.
            $existing->enabled = 1;
            $existing->timemodified = time();
            $DB->update_record('local_tourcustomizer_tours', $existing);
        } else {
            // Create new record.
            $record = new \stdClass();
            $record->tourid = $tourid;
            $record->enabled = 1;
            $record->timecreated = time();
            $record->timemodified = time();
            $DB->insert_record('local_tourcustomizer_tours', $record);
        }

        return true;
    }

    /**
     * Disable auto-reset for a specific tour.
     *
     * @param int $tourid The ID of the tour
     * @return bool True if successful
     */
    public static function disable_tour_reset($tourid) {
        global $DB;

        $existing = $DB->get_record('local_tourcustomizer_tours', ['tourid' => $tourid]);

        if ($existing) {
            $existing->enabled = 0;
            $existing->timemodified = time();
            $DB->update_record('local_tourcustomizer_tours', $existing);
            return true;
        }

        return false;
    }

    /**
     * Get the list of tour IDs that have auto-reset enabled.
     *
     * @return array Array of tour IDs
     */
    public static function get_enabled_tour_ids() {
        global $DB;

        $records = $DB->get_records('local_tourcustomizer_tours', ['enabled' => 1], '', 'tourid');
        return array_keys($records);
    }

    /**
     * Check if a tour has auto-reset enabled.
     *
     * @param int $tourid The ID of the tour
     * @return bool True if enabled
     */
    public static function is_tour_reset_enabled($tourid) {
        global $DB;

        $record = $DB->get_record('local_tourcustomizer_tours', ['tourid' => $tourid, 'enabled' => 1]);
        return !empty($record);
    }
}
