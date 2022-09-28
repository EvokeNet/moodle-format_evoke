<?php

/**
 * Preview renderer class.
 *
 * @copyright 2022 World Bank Group <https://worldbank.org>
 * @author    Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace format_evoke\output;

use renderable;
use templatable;
use renderer_base;

/**
 * Preview renderer class.
 *
 * @copyright 2022 World Bank Group <https://worldbank.org>
 * @author    Willian Mano <willianmanoaraujo@gmail.com>
 */
class preview implements renderable, templatable {
    public $context;
    public $course;

    public function __construct($context, $course) {
        $this->context = $context;
        $this->course = $course;
    }

    /**
     * Export the data
     *
     * @param renderer_base $output
     *
     * @return array|\stdClass
     *
     * @throws \dml_exception
     * @throws \moodle_exception
     */
    public function export_for_template(renderer_base $output) {
        $courselistelement = new \core_course_list_element($this->course);

        $coursesupport = new \format_evoke\support\course($courselistelement);

        $customfields = $coursesupport->get_custom_fields();
        $teacher = $coursesupport->get_teacher();

        $courseformatoptions = course_get_format($this->course->id)->get_format_options();
        $syllabus = format_text($courseformatoptions['syllabus_editor']['text'], $courseformatoptions['syllabus_editor']['format']);

        $enrolement = new \format_evoke\support\enrolment($courselistelement);
        $enrolementinstances = $enrolement->get_enrolment_instances();

        $context = [
            'courseid' => $this->course->id,
            'coursename' => $courselistelement->get_formatted_fullname(),
            'courseimage' => $coursesupport->get_courseimage(),
            'coursesummary' => $coursesupport->get_summary(),
            'categoryname' => $coursesupport->get_category_name(),
            'hascustomfields' => (bool)count($customfields),
            'customfields' => $customfields,
            'teacher' => $teacher,
            'syllabus' => $syllabus,
            'enrolbuttons' => $output->render_from_template('format_evoke/enrol_buttons', $enrolementinstances)
        ];

        return $context;
    }
}