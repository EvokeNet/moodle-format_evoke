<?php

/**
 * Contains the ajax update section structure for Evoke format.
 *
 * @package   format_evoke
 * @copyright 2022 World Bank Group <https://worldbank.org>
 * @author    Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace format_evoke\output\courseformat\state;

use core_courseformat\output\local\state\section as section_base;
use core_courseformat\base as course_format;
use section_info;
use moodle_url;
use renderer_base;
use stdClass;

/**
 * Contains the ajax update section structure.
 *
 * @package   format_evoke
 * @copyright 2022 World Bank Group <https://worldbank.org>
 * @author    Willian Mano <willianmanoaraujo@gmail.com>
 */
class section extends section_base {

    /**
     * Export this data so it can be used as state object in the course editor.
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(renderer_base $output): stdClass {
        $format = $this->format;
        $course = $format->get_course();
        $section = $this->section;
        $modinfo = $format->get_modinfo();

        $indexcollapsed = false;
        $contentcollapsed = false;
        $preferences = $format->get_sections_preferences();
        if (isset($preferences[$section->id])) {
            $sectionpreferences = $preferences[$section->id];
            if (!empty($sectionpreferences->contentcollapsed)) {
                $contentcollapsed = true;
            }
            if (!empty($sectionpreferences->indexcollapsed)) {
                $indexcollapsed = true;
            }
        }

        // Get section URL with fallback for null case
        // course_get_url() can return null when get_view_url() returns null
        $sectionurl = course_get_url($course, $section->section, ['navigation' => true]);
        if ($sectionurl === null) {
            // Fallback to course view URL if get_view_url returns null
            $sectionurl = new moodle_url('/course/view.php', [
                'id' => $course->id,
                'section' => $section->section
            ]);
        }

        $data = (object)[
            'id' => $section->id,
            'section' => $section->section,
            'number' => $section->section,
            'title' => $format->get_section_name($section),
            'hassummary' => !empty($section->summary),
            'rawtitle' => $section->name,
            'cmlist' => [],
            'visible' => !empty($section->visible),
            'sectionurl' => $sectionurl->out(false),
            'current' => $format->is_section_current($section),
            'indexcollapsed' => $indexcollapsed,
            'contentcollapsed' => $contentcollapsed,
            'hasrestrictions' => $this->get_has_restrictions(),
            'bulkeditable' => $this->is_bulk_editable(),
            'component' => $section->component,
            'itemid' => $section->itemid,
            'parentsectionid' => $section->get_component_instance()?->get_parent_section()?->id,
        ];

        if (empty($modinfo->sections[$section->section])) {
            return $data;
        }

        foreach ($modinfo->sections[$section->section] as $modnumber) {
            $mod = $modinfo->cms[$modnumber];
            if ($section->uservisible && $mod->is_visible_on_course_page() && $mod->is_of_type_that_can_display()) {
                $data->cmlist[] = $mod->id;
            }
        }

        return $data;
    }
}
