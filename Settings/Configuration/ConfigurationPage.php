<?php

namespace Jipink\Settings\Configuration;

use Jipink\Helper\Helper;
use Jipink\Settings\Configuration\Auth2Section;
use Jipink\Settings\Configuration\FreeShippingSection;
use Jipink\Settings\Configuration\DevelopSection;

defined('ABSPATH') || exit;

/**
 * A main class that holds all our settings logic
 */
class ConfigurationPage
{
    /**
     * Gets all settings fields from all the settings sections
     *
     * @return array
     */
    public static function get_settings_fields()
    {
        return array_merge(
            Auth2Section::get_fields(),
            FreeShippingSection::get_fields(),
            DevelopSection::get_fields()
        );
    }

    /**
     * Registers the sections and render them
     *
     * @return void
     */
    public static function init_settings()
    {
        register_setting('wc-jipink', 'wc-jipink_options');

        $section = new Auth2Section();
        $section->add('wc-jipink-settings');
        $section = new FreeShippingSection();
        $section->add('wc-jipink-settings');
        $section = new DevelopSection();
        $section->add('wc-jipink-settings');
    }

    public static function initPage()
    {
        $fields =  self::get_settings_fields();
        Helper::render_page('wc-jipink-settings', $fields);
    }
}
