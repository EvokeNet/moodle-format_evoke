<?php

/**
 * Evoke course format. Display the whole course as "missions" made of modules.
 *
 * @package   format_evoke
 * @copyright 2022 World Bank Group <https://worldbank.org>
 * @author    Willian Mano <willianmanoaraujo@gmail.com>
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/filelib.php');
require_once($CFG->libdir.'/completionlib.php');

// Retrieve course format option fields and add them to the $course object.
$format = course_get_format($course);
$course = $format->get_course();
$context = context_course::instance($course->id);

$page = optional_param('page', 'course', PARAM_ALPHAEXT);

// If is guest user or non-enrolled, can only access introduction page.
// Don't allow access for anyone to anywhere in course.
if (isguestuser($USER) || (!is_enrolled($context, $USER) && !has_capability('moodle/course:update', $context))) {
    $page = 'preview';
}

if (($marker >= 0) && has_capability('moodle/course:setcurrentsection', $context) && confirm_sesskey()) {
    $course->marker = $marker;
    course_set_marker($course->id, $marker);
}

// Make sure section 0 is created.
course_create_sections_if_missing($course, 0);

$renderer = $PAGE->get_renderer('format_evoke');

if ($page == 'preview') {
    $widget = new \format_evoke\output\preview($context, $course);

    echo $renderer->render($widget);
} else {
    if (!empty($displaysection)) {
        $format->set_sectionnum($displaysection);
    }

    if ($course->coursedisplay != format_evoke::COURSE_DISPLAY_HIDESECTIONS || has_capability('moodle/course:update', $context)) {
        $outputclass = $format->get_output_classname('content');
        $widget = new $outputclass($format);
        echo $renderer->render($widget);
    }
}
