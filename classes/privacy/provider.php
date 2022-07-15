<?php

/**
 * Privacy Subsystem implementation for Evoke course format.
 *
 * @package   format_evoke
 * @copyright 2022 World Bank Group <https://worldbank.org>
 * @author    Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace format_evoke\privacy;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\null_provider;

/**
 * Privacy Subsystem for Evoke course format implementing null_provider.
 *
 * @copyright 2022 World Bank Group <https://worldbank.org>
 * @author    Willian Mano <willianmanoaraujo@gmail.com>
 */
class provider implements null_provider {

    /**
     * Get the language string identifier with the component's language
     * file to explain why this plugin stores no data.
     *
     * @return  string
     */
    public static function get_reason(): string {
        return 'privacy:metadata';
    }
}