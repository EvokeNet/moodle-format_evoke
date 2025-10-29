<?php

/**
 * Contains the default activity control menu for Evoke format.
 *
 * @package   format_evoke
 * @copyright 2022 World Bank Group <https://worldbank.org>
 * @author    Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace format_evoke\output\courseformat\content\cm;

use core_courseformat\output\local\content\cm\controlmenu as controlmenu_base;
use core_courseformat\base as course_format;
use core\context\module as context_module;
use moodle_url;
use section_info;
use cm_info;

/**
 * Base class to render a course module menu inside the Evoke course format.
 *
 * @package   format_evoke
 * @copyright 2022 World Bank Group <https://worldbank.org>
 * @author    Willian Mano <willianmanoaraujo@gmail.com>
 */
class controlmenu extends controlmenu_base {

    /**
     * Constructor.
     *
     * @param course_format $format the course format
     * @param section_info $section the section info
     * @param cm_info $mod the course module info
     * @param array $displayoptions optional extra display options
     */
    public function __construct(course_format $format, section_info $section, cm_info $mod, array $displayoptions = []) {
        try {
            // Call parent constructor first
            parent::__construct($format, $section, $mod, $displayoptions);
        } catch (\TypeError $e) {
            // If parent constructor fails due to null baseurl, set up manually
            // We need to call the grandparent constructor (basecontrolmenu) manually
            $this->format = $format;
            $this->section = $section;
            $this->mod = $mod;
            $this->menuid = $mod->id;
            $this->course = $format->get_course();
            $this->coursecontext = $format->get_context();

            // Now set up the cm-specific properties
            $this->displayoptions = $displayoptions;
            $this->modcontext = context_module::instance($mod->id);
            $this->canmanageactivities = has_capability('moodle/course:manageactivities', $this->modcontext);

            $this->basemodurl = new \core\url('/course/mod.php');
            $sectionnumreturn = $format->get_sectionnum();
            if ($sectionnumreturn !== null) {
                $this->basemodurl->param('sr', $sectionnumreturn);
            }
        }

        // Ensure baseurl is always set to a valid URL
        // Get the view URL and provide fallback if null
        $viewurl = $format->get_view_url($format->get_sectionnum(), ['navigation' => true]);

        if ($viewurl === null) {
            $course = $format->get_course();
            $sectionnum = $format->get_sectionnum();
            if ($sectionnum !== null) {
                $viewurl = new moodle_url('/course/view.php', [
                    'id' => $course->id,
                    'section' => $sectionnum
                ]);
            } else {
                $viewurl = new moodle_url('/course/view.php', ['id' => $course->id]);
            }
        }

        // Set the baseurl using the public method
        $this->set_baseurl($viewurl);
    }
}
