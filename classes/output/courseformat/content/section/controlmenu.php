<?php

/**
 * Contains the default section controls output class.
 *
 * @package   format_evoke
 * @copyright 2022 World Bank Group <https://worldbank.org>
 * @author    Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace format_evoke\output\courseformat\content\section;

use context_course;
use core_courseformat\output\local\content\section\controlmenu as controlmenu_base;
use moodle_url;
use section_info;
use cm_info;

/**
 * Base class to render a course section menu.
 *
 * @package   format_evoke
 * @copyright 2022 World Bank Group <https://worldbank.org>
 * @author    Willian Mano <willianmanoaraujo@gmail.com>
 */
class controlmenu extends controlmenu_base {

    /** @var course_format the course format class */
    protected $format;

    /** @var section_info the course section class */
    protected $section;

    /**
     * Constructor.
     *
     * @param course_format $format the course format
     * @param section_info $section the section info
     * @param cm_info|null $mod the module info
     * @param string $menuid the ID value for the menu
     */
    public function __construct($format, section_info $section, ?cm_info $mod = null, string $menuid = '') {
        try {
            // Call parent constructor first
            parent::__construct($format, $section, $mod, $menuid);
        } catch (\TypeError $e) {
            // If parent constructor fails due to null baseurl, set up manually
            $this->format = $format;
            $this->section = $section;
            $this->mod = $mod;
            $this->menuid = $menuid;
            $this->course = $format->get_course();
            $this->coursecontext = $format->get_context();
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

    /**
     * Generate the edit control items of a section.
     *
     * This method must remain public until the final deprecation of section_edit_control_items.
     *
     * @return array of edit control items
     */
    public function section_control_items() {

        $format = $this->format;
        $section = $this->section;
        $course = $format->get_course();
        $sectionreturn = $format->get_sectionnum();

        $coursecontext = context_course::instance($course->id);

        if ($sectionreturn) {
            $url = course_get_url($course, $section->section);
        } else {
            $url = course_get_url($course);
        }
        $url->param('sesskey', sesskey());

        $controls = [];
        if ($section->section && has_capability('moodle/course:setcurrentsection', $coursecontext)) {
            if ($course->marker == $section->section) {  // Show the "light globe" on/off.
                $url->param('marker', 0);
                $highlightoff = get_string('highlightoff');
                $controls['highlight'] = [
                    'url' => $url,
                    'icon' => 'i/marked',
                    'name' => $highlightoff,
                    'pixattr' => ['class' => ''],
                    'attr' => [
                        'class' => 'editing_highlight',
                        'data-action' => 'removemarker'
                    ],
                ];
            } else {
                $url->param('marker', $section->section);
                $highlight = get_string('highlight');
                $controls['highlight'] = [
                    'url' => $url,
                    'icon' => 'i/marker',
                    'name' => $highlight,
                    'pixattr' => ['class' => ''],
                    'attr' => [
                        'class' => 'editing_highlight',
                        'data-action' => 'setmarker'
                    ],
                ];
            }
        }

        $parentcontrols = parent::section_control_items();

        // If the edit key exists, we are going to insert our controls after it.
        if (array_key_exists("edit", $parentcontrols)) {
            $merged = [];
            // We can't use splice because we are using associative arrays.
            // Step through the array and merge the arrays.
            foreach ($parentcontrols as $key => $action) {
                $merged[$key] = $action;
                if ($key == "edit") {
                    // If we have come to the edit key, merge these controls here.
                    $merged = array_merge($merged, $controls);
                }
            }

            return $merged;
        } else {
            return array_merge($controls, $parentcontrols);
        }
    }
}
