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

define('AJAX_SCRIPT', true);

// No login check is expected here bacause the course tabs element is visible for all site visitors.
// @codingStandardsIgnoreLine
require_once(__DIR__ . '/../../../config.php');
require_once($CFG->libdir . '/adminlib.php');

if ($CFG->forcelogin) {
    require_login();
}

require_sesskey();

if (!confirm_sesskey()) {
    die;
}

$themedir = '/theme';

if (isset($CFG->themedir)) {
    $themedir = $CFG->themedir;
    $themedir = str_replace($CFG->dirroot, '', $CFG->themedir);
}

// Require theme lib files.
require_once($CFG->dirroot . $themedir . '/mb2nl/lib.php');

$options = [
    'limit' => 12,
    'catids' => '',
    'excats' => '',
    'filtertype' => 'category',
    'tagids' => '',
    'extags' => '',
    'taball' => 0,
    'columns' => 4,
    'gutter' => 'normal',
    'custom_class' => '',
    'mt' => 0,
    'mb' => 30,

    'cistyle' => 'n',
    'crounded' => 1,

    'catdesc' => 0,
    'coursecount' => 0,

    'carousel' => 0,
    'sloop' => 0,
    'snav' => 1,
    'sdots' => 0,
    'autoplay' => 0,
    'pausetime' => 5000,
    'animtime' => 450,

    'tabstyle' => 1,
    'acccolor' => '',
    'tcolor' => '',
    'tcenter' => 0,
];

// Get options from the URL.
$categories = optional_param('categories', '', PARAM_RAW);
$options = theme_mb2nl_optional_params($options);
$options['categories'] = $categories;

$context = context_system::instance();
$PAGE->set_url($themedir . '/mb2nl/lib/lib_ajax_coursetabs.php', $options);
$PAGE->set_context($context);

$options['tags'] = $options['filtertype'] === 'category' ? [] : [$options['categories']];
$options['categories'] = $options['filtertype'] === 'tag' ? [] : [$options['categories']];
$options['lazy'] = 0;

// This is required for the 'All" course tab.
if (in_array(0, $options['categories'])) {
    $options['categories'] = [];
}

if (in_array(0, $options['tags'])) {
    $options['tags'] = theme_mb2nl_course_tag_ids($options);
}

echo theme_mb2nl_coursetabs_tabcontent($options);
die;
