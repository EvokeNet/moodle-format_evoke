<?php

/**
 * Contains the default content output class.
 *
 * @package   format_evoke
 * @copyright 2022 World Bank Group <https://worldbank.org>
 * @author    Willian Mano <willianmanoaraujo@gmail.com>
 */

namespace format_evoke\output\courseformat;

use core_courseformat\output\local\content as content_base;

/**
 * Base class to render a course content.
 *
 * @package   format_evoke
 * @copyright 2022 World Bank Group <https://worldbank.org>
 * @author    Willian Mano <willianmanoaraujo@gmail.com>
 */
class content extends content_base {

    /**
     * @var bool Evoke format has add section after each mission.
     *
     * The responsible for the buttons is core_courseformat\output\local\content\section.
     */
    protected $hasaddsection = false;

}
