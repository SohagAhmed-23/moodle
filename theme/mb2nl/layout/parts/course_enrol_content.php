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
 *
 * @package   theme_mb2nl
 * @copyright 2017 - 2025 Mariusz Boloz (lmsstyle.com)
 * @license   PHP and HTML: http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later. Other parts: http://themeforest.net/licenses
 *
 */

defined('MOODLE_INTERNAL') || die();

// Start HTML.
$html = '';

foreach (theme_mb2nl_is_coursetab_items(true) as $tabcontent) {
    $id = $tabcontent['id'] === '' ? 'desc' : $tabcontent['id'];

    if (!theme_mb2nl_is_eopn()) {
        $activecls = $id === 'desc' ? ' active' : ' d-none';
    } else {
        $activecls = '';
    }

    $html .= '<div id="course-csection-' . $id . '" class="course-csection enrol-tab-content' . $activecls .
    '" aria-labelledby="enrol-nav-btn-' . $id . '">';

    if ($id === 'sections') {
        $html .= theme_mb2nl_enrol_content_html();
    } else if (preg_match('@mb2section@', $id)) {
        $html .= theme_mb2nl_course_csection_html($id, false);
    } else if ($id === 'instructor') {
        $html .= theme_mb2nl_enrol_instructor_html();
    } else if ($id === 'reviews') {
        $html .= theme_mb2nl_enrol_reviews_html();
    } else {
        $html .= theme_mb2nl_enrol_section_overview_html();
    }

    $html .= '</div>';
}

echo $html;
