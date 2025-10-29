<?php

/**
 * Contains the ajax update course module structure for Evoke format.
 *
 * @package   format_evoke
 * @copyright 2022 World Bank Group <https://worldbank.org>
 * @author    Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace format_evoke\output\courseformat\state;

use core_courseformat\output\local\state\cm as cm_base;
use core_courseformat\base as course_format;
use section_info;
use cm_info;
use renderer_base;
use stdClass;

/**
 * Contains the ajax update course module structure.
 *
 * @package   format_evoke
 * @copyright 2022 World Bank Group <https://worldbank.org>
 * @author    Willian Mano <willianmanoaraujo@gmail.com>
 */
class cm extends cm_base {

    /**
     * Export this data so it can be used as state object in the course editor.
     *
     * @param renderer_base $output typically, the renderer that's calling this function
     * @return stdClass data context for a mustache template
     */
    public function export_for_template(renderer_base $output): stdClass {
        global $CFG, $USER;

        $format = $this->format;
        $section = $this->section;
        $cm = $this->cm;
        $course = $format->get_course();
        $delegatedsectioninfo = $cm->get_delegated_section_info();

        $data = (object)[
            'id' => $cm->id,
            'anchor' => "module-{$cm->id}",
            'name' => \core_external\util::format_string($cm->name, $cm->context, true),
            'visible' => !empty($cm->visible),
            'stealth' => $cm->is_stealth(),
            'sectionid' => $section->id,
            'sectionnumber' => $section->section,
            'uservisible' => $cm->uservisible,
            'hascmrestrictions' => $this->get_has_restrictions(),
            'modname' => get_string('pluginname', 'mod_' . $cm->modname),
            'indent' => ($format->uses_indentation()) ? $cm->indent : 0,
            'groupmode' => $cm->groupmode,
            'module' => $cm->modname,
            'plugin' => 'mod_' . $cm->modname,
            // Activities with delegate section has some restriction to prevent structure loops.
            'hasdelegatedsection' => !empty($delegatedsectioninfo),
        ];

        if (!empty($delegatedsectioninfo)) {
            $data->delegatesectionid = $delegatedsectioninfo->id;
        }

        // Check the user access type to this cm.
        $info = new \core_availability\info_module($cm);
        $data->accessvisible = ($data->visible && $info->is_available_for_all());

        // Add url if the activity is compatible.
        $url = $cm->url;
        if ($url) {
            $data->url = $url->out();
        }

        if ($this->exportcontent) {
            $data->content = $output->course_section_updated_cm_item($format, $section, $cm);
        }

        // Completion status - only process if cm has a valid ID
        // During module save/update, cm->id might be null temporarily
        if (!empty($cm->id)) {
            $completioninfo = new \completion_info($course);
            $data->istrackeduser = $this->istrackeduser ?? $completioninfo->is_tracked_user($USER->id);
            if ($data->istrackeduser && $completioninfo->is_enabled($cm)) {
                $completiondata = new \core_completion\cm_completion_details($completioninfo, $cm, $USER->id, false);
                $data->completionstate = $completiondata->get_overall_completion();
                $data->isoverallcomplete = $completiondata->is_overall_complete();
            }
        } else {
            // If cm->id is null, set default values
            $data->istrackeduser = false;
        }

        $data->allowstealth = !empty($CFG->allowstealth) && $format->allow_stealth_module_visibility($cm, $section);

        return $data;
    }
}
