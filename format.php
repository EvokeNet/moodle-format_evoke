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

if (($marker >= 0) && has_capability('moodle/course:setcurrentsection', $context) && confirm_sesskey()) {
    $course->marker = $marker;
    course_set_marker($course->id, $marker);
}

// Make sure section 0 is created.
course_create_sections_if_missing($course, 0);

$renderer = $PAGE->get_renderer('format_evoke');

if (!empty($displaysection)) {
    $format->set_section_number($displaysection);
}

if ($course->coursedisplay != format_evoke::COURSE_DISPLAY_HIDESECTIONS || has_capability('moodle/course:update', $context)) {
    $outputclass = $format->get_output_classname('content');
    $widget = new $outputclass($format);
    echo $renderer->render($widget);
}
