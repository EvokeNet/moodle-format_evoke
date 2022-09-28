<?php

/**
 * Course support class.
 *
 * @copyright 2022 World Bank Group <https://worldbank.org>
 * @author    Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace format_evoke\support;

defined('MOODLE_INTERNAL') || die();

use moodle_url;
use core_course_category;

/**
 * Course class.
 *
 * @copyright  2022 Willian Mano <willianmano@conecti.me>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class course {
    protected $course;

    public function __construct($course) {
        $this->course = $course;
    }

    public function get_summary() {
        if ($this->course->has_summary()) {
            $chelper = new \coursecat_helper();

            return $chelper->get_course_formatted_summary($this->course,
                ['overflowdiv' => true, 'noclean' => true, 'para' => false]);
        }

        return false;
    }

    public function get_courseimage() {
        global $CFG, $OUTPUT;

        foreach ($this->course->get_course_overviewfiles() as $file) {
            if ($file->is_valid_image()) {
                $url = moodle_url::make_file_url("$CFG->wwwroot/pluginfile.php",
                    '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                    $file->get_filearea() . $file->get_filepath() . $file->get_filename(), !$file->is_valid_image());

                return $url->out();
            }
        }

        return $OUTPUT->get_generated_image_for_id($this->course->id);
    }

    public function get_custom_fields() {
        if ($this->course->has_custom_fields()) {
            $coursecustomfields = $this->course->get_custom_fields();

            foreach ($coursecustomfields as $data) {
                $fielddata = new \core_customfield\output\field_data($data);

                $value = $fielddata->get_value();
                $customfields[] = (object)[
                    'name' => $fielddata->get_name(),
                    'hasvalue' => ($value !== null),
                    'value' => $value
                ];
            }

            return $customfields;
        }

        return [];
    }

    public function get_category_name() {
        if ($category = core_course_category::get($this->course->category, IGNORE_MISSING)) {
            return $category->get_formatted_name();
        }

        return false;
    }

    /**
     * Get first teacher info.
     *
     * @return array
     *
     * @throws \coding_exception
     * @throws \dml_exception
     */
    public function get_teacher() {
        global $DB;

        if ($this->course->has_course_contacts()) {
            $instructors = $this->course->get_course_contacts();

            foreach ($instructors as $key => $instructor) {
                $user = $DB->get_record('user', ['id' => $key]);

                return [
                    'fullname' => $instructor['username'],
                    'image' => user::get_user_picture($user, 200),
                    'description' => format_text($user->description, $user->descriptionformat)
                ];
            }
        }

        return [];
    }
}
