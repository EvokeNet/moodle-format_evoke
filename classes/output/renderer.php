<?php

namespace format_evoke\output;

use core_courseformat\base as course_format;
use core_courseformat\output\section_renderer;
use moodle_page;

/**
 * Basic renderer for evoke format.
 *
 * @copyright 2022 World Bank Group <https://worldbank.org>
 * @author    Willian Mano <willianmanoaraujo@gmail.com>
 */
class renderer extends section_renderer {

    /**
     * Constructor method, calls the parent constructor.
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);

        // Since format_evoke_renderer::section_edit_control_items() only displays the 'Highlight' control
        // when editing mode is on we need to be sure that the link 'Turn editing mode on' is available for a user
        // who does not have any other managing capability.
        $page->set_other_editing_capability('moodle/course:setcurrentsection');
    }

    /**
     * Generate the section title, wraps it in a link to the section page if page is to be displayed on a separate page.
     *
     * @param section_info|stdClass $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section));
    }

    /**
     * Generate the section title to be displayed on the section page, without a link.
     *
     * @param section_info|stdClass $section The course_section entry from DB
     * @param int|stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title_without_link($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section, false));
    }

    /**
     * Get the course index drawer with placeholder.
     *
     * The default course index is loaded after the page is ready. Format plugins can override
     * this method to provide an alternative course index.
     *
     * If the format is not compatible with the course index, this method will return an empty string.
     *
     * @param course_format $format the course format
     * @return String the course index HTML.
     */
    public function course_index_drawer(course_format $format): ?String {
        if (isguestuser() || !is_enrolled(\context_course::instance($format->get_courseid()))) {
            return '';
        }

        if ($format->uses_course_index()) {
            $course = $format->get_course();

            if ($this->page->pagelayout != 'incourse' &&
                $course->coursedisplay == \format_evoke::COURSE_DISPLAY_HIDESECTIONS &&
                !has_capability('moodle/course:update', $this->page->context))
            {
                return '';
            }

            if ($this->page->pagelayout == 'incourse' && !has_capability('moodle/course:update', $this->page->context)) {
                return $this->render_from_template('format_evoke/incourse_courseindex', [
                    'section' => $this->page->cm->section
                ]);
            }

            include_course_editor($format);

            return $this->render_from_template('core_courseformat/local/courseindex/drawer', []);
        }
        return '';
    }
}
