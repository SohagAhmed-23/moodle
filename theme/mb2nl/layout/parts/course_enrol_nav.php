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

global $PAGE, $COURSE;

$urlid = optional_param('id', 0, PARAM_INT);
$ctab = optional_param('ctab', '', PARAM_ALPHANUMEXT);
$layout = theme_mb2nl_enrol_layout();
$onepagenav = theme_mb2nl_is_eopn();
$tabalt = theme_mb2nl_mb2fields_filed('mb2tabalt') ? theme_mb2nl_mb2fields_filed('mb2tabalt') :
theme_mb2nl_theme_setting($PAGE, 'tabalt');
$tabcls = $tabalt ? ' tabalt' : '';
$navcol = '9 enrol-contentcol';
$tablist = !$onepagenav ? ' role="tablist"' : '';

if ($layout == 1 || $layout == 2) {
    $navcol = 12;
}

if ($onepagenav) {
    $PAGE->requires->js_call_amd('theme_mb2nl/enrol', 'onePageNav');
} else {
    $PAGE->requires->js_call_amd('theme_mb2nl/enrol', 'navTabs');
}

// Start HTML.
$html = '';

if ($onepagenav) {
    $html .= '<div class="enrol-course-nav-replace"></div>';
}

$html .= '<div class="enrol-course-nav position-relative' . $tabcls . '">';
$html .= '<div class="enrol-course-nav-inner">';
$html .= '<div class="container-fluid">';
$html .= '<div class="row">';
$html .= '<div class="col-lg-' . $navcol . '">';
$html .= '<ul class="enrol-course-nav-list' . theme_mb2nl_bsfcls(2, 'row', '', 'center') . '"' . $tablist . '>';

foreach (theme_mb2nl_is_coursetab_items(true) as $item) {

    if (!$onepagenav && $item['id'] === $ctab) {
        $isactive = ' active';
        $isselected = 'true';
        $istabindex = 0;
    } else {
        $isactive = '';
        $isselected = 'false';
        $istabindex = -1;
    }

    $hrefid = !$item['id'] ? 'desc' : $item['id'];
    $stars = isset($item['stars']) && $item['stars'] ? ' ' . $item['stars'] : '';

    $url = '#course-csection-' . $hrefid;

    $html .= '<li class="enrol-course-navitem tab-item item-' . $item['id'] . $isactive . '">';

    if ($onepagenav) {
        $html .= '<a class="enrol-nav-btn' . theme_mb2nl_bsfcls(2, '', 'center', 'center') . '" href="#course-csection-' . $hrefid .
        '">' . $item['str'] . $stars . '</a>';
    } else {
        $html .= '<button type="button" role="tab" id="enrol-nav-btn-' . $hrefid . '" class="themereset enrol-nav-btn' .
        theme_mb2nl_bsfcls(2, '', 'center', 'center') . '" aria-selected="' . $isselected . '" aria-controls="course-csection-' .
        $hrefid . '" data-tabid="' . $hrefid . '" tabindex="' . $istabindex . '">' . $item['str'] . $stars . '</button>';
    }

    $html .= '</li>';

}

$html .= '</ul>';
$html .= '</div>';
$html .= '</div>';
$html .= '</div>';
$html .= '</div>';
$html .= '</div>';

echo $html;
