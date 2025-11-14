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



/**
 *
 * Method to display site menu links
 *
 */
function theme_mb2nl_is_site_menu() {

    if (isloggedin() && !isguestuser()) {
        return true;
    }

    return false;

}



/**
 *
 * Method to display site menu links
 *
 */
function theme_mb2nl_site_menu($mobile = false, $tgsdb = false, $admin = false) {

    global $PAGE, $COURSE, $USER;

    $output = '';
    $context = context_course::instance($COURSE->id);
    $enrolled = is_enrolled($context, $USER->id, '', true);
    $excludedlinks = explode(',', theme_mb2nl_theme_setting($PAGE, 'excludedlinks'));
    $menuitems = theme_mb2nl_site_menu_items();
    $svg = theme_mb2nl_svg();
    $itemcls = theme_mb2nl_bsfcls(1, 'row', '', 'center');
    $listcls = 'quicklinks-list';

    if ($tgsdb) {
        $menuitems = theme_mb2nl_site_menu_items(false);
        $itemcls = theme_mb2nl_bsfcls(1, 'column', 'center');
        $listcls = 'tgsdb-links tgsdb-quicklinks position-relative' . theme_mb2nl_bsfcls(1, 'column');
    }

    if ($tgsdb && $admin) {
        $menuitems = theme_mb2nl_site_menu_admin_items();

        if (!has_capability('moodle/site:configview', context_system::instance()) && !has_capability('moodle/site:config',
        context_system::instance())) {
            $helplink = theme_mb2nl_user_helplink(true);

            if (is_array($helplink) && !empty($helplink)) {
                $menuitems = array_merge($menuitems, $helplink);
            }
        }

        $listcls = 'tgsdb-links tgsdb-admin-links position-relative' . theme_mb2nl_bsfcls(1, 'column');
    }

    if (!count($menuitems)) {
        return;
    }

    if (!$mobile) {
        $output .= '<div id="quicklinks" class="quicklinks">';
        $output .= '<button id="quicklinks-toggle" type="button" class="themereset" aria-label="' .
        get_string('quicklinks', 'theme_mb2nl') . '" aria-expanded="false" aria-controls="' . $listcls . '">';
        $output .= $svg['dots'];
        $output .= '</button>';
    }

    $output .= !$mobile ? '<ul id="quicklinks-list" class="' . $listcls . '">' : '<ul class="' . $listcls . '">';

    foreach ($menuitems as $k => $el) {
        $output .= '<li class="item-' . $k . '">';
        $output .= '<a href="' . $el['link'] . '" class="item-link position-relative' . $itemcls . '">';
        $output .= '<span class="static-icon' . theme_mb2nl_bsfcls(2, '', 'center', 'center') . '" aria-hidden="true"><i class="' .
        $el['icon'] . '"></i></span>';
        $output .= '<span class="text"><span class="position-relative">' . $el['text'] . '</span></span>';
        $output .= '</a>';
        $output .= '</li>';
    }

    if (theme_mb2nl_theme_setting($PAGE, 'customsitemnuitems')) {
        $output .= theme_mb2nl_static_content(theme_mb2nl_theme_setting($PAGE, 'customsitemnuitems'), false, true);
    }

    $output .= '</ul>';

    if (!$mobile) {
        $output .= '</div>';
        $PAGE->requires->js_call_amd('theme_mb2nl/quicklinks', 'quickLinksInit');
    }

    return $output;

}



/**
 *
 * Method to display site menu item
 *
 *
 */
function theme_mb2nl_site_menu_admin_items() {

    $syscontext = context_system::instance();
    if (!has_capability('moodle/site:configview', $syscontext) && !has_capability('moodle/site:config', $syscontext)) {
        return [];
    }

    return [
        'settings' => [
            'access' => ['admin'],
            'course' => true,
            'icon' => 'ri-list-settings-line',
            'text' => get_string('tsettings', 'theme_mb2nl'),
            'link' => new moodle_url('/admin/settings.php', ['section' => 'themesetting' . theme_mb2nl_themename()]),
        ],
        'admin' => [
            'access' => ['admin'],
            'course' => true,
            'icon' => 'ri-settings-5-line',
            'text' => get_string('administrationsite'),
            'link' => new moodle_url('/admin/search.php'),
        ],
    ];

}



/**
 *
 * Method to display site menu item
 *
 *
 */
function theme_mb2nl_site_menu_items($admin = true) {

    global $COURSE, $CFG, $PAGE, $DB, $USER, $SITE;

    $curentcat = optional_param('categoryid', 0, PARAM_INT);
    $iscourse = theme_mb2nl_is_course();

    if ($curentcat) {
        // Check if the category exists.
        // This is require to display error in the main content in case when the category doesn't exists.
        if ($DB->record_exists_sql('SELECT id FROM {course_categories} WHERE id=?', [$curentcat])) {
            $context = context_coursecat::instance($curentcat);
        } else {
            $context = context_system::instance();
        }
    } else if ($iscourse) {
        $context = context_course::instance($COURSE->id);
    } else {
        $context = context_system::instance();
    }

    // Check if is frontpage.
    $isfp = $PAGE->pagetype === 'site-index';
    $enabledashboard = isset($CFG->enabledashboard) ? $CFG->enabledashboard : true;
    $isds = $PAGE->pagelayout === 'mycourses' ? true : $PAGE->pagetype !== 'my-index';

    // Check if is page added to the front page.
    $isfpage = ($PAGE->pagetype === 'mod-page-view' && $COURSE->id == $SITE->id);

    // Check if is course page or admin pages.
    $showmanage = (
        $PAGE->pagetype === 'site-index' ||
        $PAGE->pagetype === 'course-index' ||
        $PAGE->pagetype === 'course-index-category' ||
        $PAGE->pagetype === 'my-index');

    $items = [
        'buildpage' => [],
        'turneditingcourse' => [
            'access' => [],
            'cap' => $PAGE->user_allowed_editing(),
            'course' => true,
            'icon' => theme_mb2nl_turnediting_button_atts(true),
            'text' => theme_mb2nl_turnediting_button_atts(),
            'link' => theme_mb2nl_editmode_link(),
        ],
        'editcourse' => [
            'access' => ['admin', 'manager', 'editingteacher'],
            'course' => $iscourse,
            'icon' => 'ri-edit-box-line',
            'text' => get_string('editcoursesettings'),
            'link' => new moodle_url('/course/edit.php', ['id' => $COURSE->id]),
        ],
        'enrolpage' => [
            'access' => ['admin', 'manager', 'editingteacher'],
            'course' => $iscourse && !is_enrolled($context, $USER->id) && theme_mb2nl_theme_setting($PAGE, 'enrollayout')
            && !theme_mb2nl_is_enrol_page(),
            'icon' => 'ri-pages-line',
            'text' => get_string('enrollmentpage', 'theme_mb2nl'),
            'link' => new moodle_url('/enrol/index.php', ['id' => $COURSE->id]),
        ],
        'editpage' => [
            'access' => ['admin', 'manager', 'editingteacher'],
            'course' => $isfpage,
            'icon' => 'ri-file-edit-line',
            'text' => get_string('editsettings'),
            'link' => isset($PAGE->cm->id) ? new moodle_url('/course/modedit.php', ['update' => $PAGE->cm->id, 'return' => 1]) :
            '',
        ],
        'mycourses' => [
            'access' => ['admin', 'manager', 'editingteacher', 'teacher', 'coursecreator', 'student', 'user'],
            'course' => true,
            'shown' => $PAGE->pagelayout !== 'mycourses',
            'icon' => 'ri-graduation-cap-line',
            'text' => get_string('mycourses'),
            'link' => new moodle_url('/my/courses.php'),
        ],
        'notes' => [],
        'dashboard' => [
            'access' => ['admin', 'manager', 'editingteacher', 'teacher', 'coursecreator', 'student', 'user'],
            'course' => true,
            'shown' => $isds && $enabledashboard,
            'icon' => 'ri-dashboard-line',
            'text' => get_string('myhome'),
            'link' => new moodle_url('/my/'),
        ],
        'frontpage' => [
            'access' => ['admin', 'manager', 'editingteacher', 'teacher', 'coursecreator', 'student', 'user'],
            'course' => true,
            'shown' => !$isfp,
            'icon' => 'ri-home-line',
            'text' => get_string('sitehome'),
            'link' => new moodle_url('/', ['redirect' => 0]),
        ],
        'courses' => [
            'access' => ['admin', 'manager', 'editingteacher', 'teacher', 'coursecreator', 'student', 'user'],
            'course' => true,
            'icon' => 'ri-book-2-line',
            'text' => get_string('fulllistofcourses'),
            'link' => new moodle_url('/course/'),
        ],
        'blog' => [
            'access' => ['admin', 'manager', 'editingteacher', 'teacher', 'coursecreator', 'student', 'user'],
            'course' => true,
            'shown' => $CFG->enableblogs,
            'icon' => 'ri-newspaper-line',
            'text' => get_string('blog', 'blog'),
            'link' => new moodle_url('/blog/'),
        ],
        'calendar' => [
            'access' => ['admin', 'manager', 'editingteacher', 'teacher', 'coursecreator', 'student', 'user'],
            'course' => true,
            'icon' => 'ri-calendar-2-line',
            'text' => get_string('calendar', 'calendar'),
            'link' => new moodle_url('/calendar/view.php', ['view' => 'month']),
        ],
        'badges' => [
            'access' => ['admin', 'manager', 'editingteacher', 'teacher', 'coursecreator', 'student', 'user'],
            'course' => true,
            'shown' => $CFG->enablebadges,
            'icon' => 'ri-medal-line',
            'text' => get_string('badges'),
            'link' => new moodle_url('/badges/mybadges.php'),
        ],
        'contentbank' => [
            'access' => [],
            'cap' => has_capability('moodle/contentbank:access', $context),
            'course' => true,
            'shown' => $PAGE->pagetype !== 'contentbank',
            'icon' => 'ri-bank-line',
            'text' => get_string('contentbank'),
            'link' => new moodle_url('/contentbank/index.php', ['contextid' => $context->id]),
        ],
        'addcourse' => [
            'access' => ['admin', 'manager', 'coursecreator'],
            'course' => true,
            'icon' => 'ri-add-line',
            'text' => get_string('createnewcourse'),
            'link' => theme_mb2nl_addcourse_url(),
        ],
        'addcategory' => [
            'access' => ['admin', 'manager'],
            'course' => true,
            'icon' => 'ri-folder-add-line',
            'text' => get_string('createnewcategory'),
            'link' => new moodle_url('/course/editcategory.php', ['parent' => 1]),
        ],
        'editcategory' => [
            'access' => ['admin', 'manager'],
            'course' => $iscourse,
            'icon' => 'ri-file-settings-line',
            'text' => get_string('editcategorysettings'),
            'link' => new moodle_url('/course/editcategory.php', ['id' => $COURSE->category]),
        ],
        'managecoursesandcats' => [
            'access' => ['admin', 'manager'],
            'course' => true,
            'shown' => $showmanage,
            'icon' => 'ri-folder-settings-line',
            'text' => get_string('managecourses'),
            'link' => new moodle_url('/course/management.php'),
        ],
        'addpage' => [
            'access' => ['admin', 'manager', 'editingteacher'],
            'course' => $isfpage || $isfp,
            'cap' => has_capability('moodle/course:manageactivities', context_course::instance($SITE->id)),
            'icon' => 'ri-file-add-line',
            'text' => get_string('addpage', 'my'),
            'link' => new moodle_url('/course/modedit.php', ['add' => 'page', 'type' => '', 'course' => $SITE->id, 'section' =>
            0, 'return' => 0, 'sr' => 0]),
        ],
        'parts' => [],
    ];

    // Course notes.
    if ($notes = theme_mb2nl_note_on(true)) {
        $items['notes'] = [
            'access' => ['admin', 'manager', 'editingteacher', 'teacher', 'coursecreator', 'student', 'user'],
            'course' => true,
            'shown' => $notes,
            'icon' => 'ri-sticky-note-line',
            'text' => $notes ? get_string('notes', 'local_mb2coursenotes') : '',
            'link' => $notes ? new moodle_url('/local/mb2coursenotes/manage.php', ['user' => theme_mb2nl_notes_userid(), 'course' =>
            theme_mb2nl_notes_courseid()]) : '',
        ];
    }

    // Page builder link.
    if (theme_mb2nl_check_builder() && $builderlink = theme_mb2nl_builder_pagelink()) {
        $items['buildpage'] = [
            'access' => ['admin'],
            'course' => !empty($builderlink),
            'cap' => has_capability('local/mb2builder:managepages', context_system::instance()),
            'icon' => 'ri-magic-line',
            'text' => theme_mb2nl_builder_has_page() ? get_string('editpage', 'local_mb2builder') :
            get_string('buildepage', 'local_mb2builder'),
            'link' => new moodle_url('/local/mb2builder/edit-page.php', $builderlink),
        ];
    }

    // Content parts.
    if (theme_mb2nl_check_builder()) {
        $items['parts'] = [
            'access' => [],
            'cap' => has_capability('local/mb2builder:manageparts', $context),
            'course' => true,
            'icon' => 'ri-function-add-line',
            'text' => get_string('parts', 'local_mb2builder'),
            'link' => new moodle_url('/local/mb2builder/parts.php', ['contextid' => $context->id]),
        ];
    }

    if ($admin) {
        $items = array_merge($items, theme_mb2nl_site_menu_admin_items());
    }

    $excludedlinks = explode(',', theme_mb2nl_theme_setting($PAGE, 'excludedlinks'));

    foreach ($items as $k => $item) {
        if (empty($item) ) {
            unset($items[$k]);
            continue;
        }

        $shown = isset($item['shown']) ? $item['shown'] : true;
        $access = isset($item['cap']) ? $item['cap'] : in_array(theme_mb2nl_site_access(), $item['access']);

        if (in_array($k, $excludedlinks) || !$access || !$item['course'] || !$shown) {
            unset($items[$k]);
        }
    }

    return $items;

}





/**
 *
 * Method to set course editing link
 *
 */
function theme_mb2nl_editmode_link() {

    global $COURSE, $USER, $PAGE;

    $editing = $PAGE->user_is_editing();
    $pageurl = $PAGE->url;

    $localurl = '/course/view.php';
    $params = ['id' => $COURSE->id, 'sesskey' => sesskey(), 'edit' => $editing ? 'off' : 'on'];

    if (theme_mb2nl_is_editmode()) {
        $localurl = '/editmode.php';
        $params = ['context' => $PAGE->context->id, 'pageurl' => $pageurl, 'setmode' => $editing ? 0 : 1, 'sesskey' => sesskey()];
    } else if (isset($USER->gradeediting[$COURSE->id]) && $PAGE->pagetype === 'grade-report-grader-index') {
        $localurl = '/index.php';
        $params = ['id' => $COURSE->id, 'sesskey' => sesskey(), 'plugin' => 'grader', 'edit' => $USER->gradeediting[$COURSE->id] ?
        0 : 1];
    }

    return new moodle_url($localurl, $params);

}





/**
 *
 * Method to check if there is new edit mode
 *
 */
function theme_mb2nl_is_editmode() {
    global $CFG;

    if (is_file($CFG->dirroot . '/editmode.php')) {
        return true;
    }

    return false;

}




/**
 *
 * Method to set course editing text
 *
 */
function theme_mb2nl_turnediting_button_atts($icon = false) {

    global $USER, $PAGE, $COURSE;

    $texton = get_string('turneditingon');
    $textoff = get_string('turneditingoff');
    $iconon = 'ri-toggle-line';
    $iconoff = 'ri-toggle-fill';
    $ifvar = isset($USER->gradeediting[$COURSE->id]) && $PAGE->pagetype === 'grade-report-grader-index' ?
    $USER->gradeediting[$COURSE->id] : $PAGE->user_is_editing();

    if ($icon) {
        return $ifvar ? $iconoff : $iconon;
    } else {
        return $ifvar ? $textoff : $texton;
    }

}
