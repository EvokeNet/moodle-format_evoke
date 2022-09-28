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
 * Enrolment support class.
 *
 * @package    format_evoke
 * @copyright  2020 Willian Mano <willianmano@conecti.me>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_evoke\support;

defined('MOODLE_INTERNAL') || die();

/**
 * Enrolment class.
 *
 * @copyright  2020 Willian Mano <willianmano@conecti.me>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class enrolment {
    protected $course;

    public function __construct($course) {
        $this->course = $course;
    }

    public function get_enrolment_instances() {
        $data['hasenroldata'] = false;

        if (isguestuser()) {
            $data['name'] = get_string('dologin', 'format_evoke');

            return $data;
        }

        $context = \context_course::instance($this->course->id);

        // Enrolled users doesn't need see this link anymore.
        if ($this->is_course_enrolled($context)) {
            return [
                'userenrolled' => true,
                'courseid' => $this->course->id
            ];
        }

        // For logged in users, we must check course configs.
        $instances = enrol_get_instances($this->course->id, true);

        $enrolinstances = [];
        foreach ($instances as $instance) {
            if ($this->is_enrolinstance_enabled($instance)) {
                $enrolinstances[] = [
                    'id' => $instance->id,
                    'enrol' => $instance->enrol,
                    'courseid' => $instance->courseid,
                    'name' => $instance->name ?: get_string('takethiscourse', 'format_evoke'),
                    'cost' => $instance->cost ? number_format($instance->cost, 2, ',', '.') : false,
                    'currency' => $instance->currency ?: false,
                    'class' => $instance->enrol == 'self' ? 'btn-success' : 'btn-primary'
                ];
            }
        }

        if (!$enrolinstances) {
            $data['name'] = get_string('enrolnotavailable', 'format_evoke');

            return $data;
        }

        $data['hasenroldata'] = true;
        $data['enrolinstances'] = $enrolinstances;

        return $data;
    }

    protected function is_enrolinstance_enabled($instance) {
        if (!in_array($instance->enrol, ['self', 'pagseguro'])) {
            return false;
        }

        if ($instance->enrolstartdate > 0 && $instance->enrolstartdate > time()) {
            return false;
        }

        if ($instance->enrolenddate > 0 && $instance->enrolenddate < time()) {
            return false;
        }

        return true;
    }

    /**
     * Returns if the whether the user is enrolled in the course or not.
     *
     * @return bool
     */
    public function is_course_enrolled($context) {
        global $USER;

        // Enrolled users doesn't need see this link anymore.
        if (is_enrolled($context, $USER->id)) {
            return true;
        }

        return false;
    }
}
