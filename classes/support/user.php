<?php

/**
 * User support class.
 *
 * @package    format_evoke
 * @copyright  2020 Willian Mano <willianmano@conecti.me>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace format_evoke\support;

defined('MOODLE_INTERNAL') || die();

/**
 * User class.
 *
 * @copyright  2022 Willian Mano <willianmano@conecti.me>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user {
    /**
     * Returns the user picture
     *
     * @param null $userobject
     * @param int $imgsize
     *
     * @return \moodle_url
     * @throws \coding_exception
     */
    public static function get_user_picture($userobject = null, $imgsize = 100) {
        global $USER, $PAGE;

        if (!$userobject) {
            $userobject = $USER;
        }

        $userimg = new \user_picture($userobject);

        $userimg->size = $imgsize;

        return $userimg->get_url($PAGE);
    }
}
