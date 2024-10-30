<?php

namespace Jipink\Helper;

trait SettingsTrait
{
    /**
     * Gets a plugin option
     *
     * @param string $key
     * @param boolean $default
     * @return mixed
     */
    public static function get_option(string $key, $default = null)
    {
        return get_option('wc-jipink-' . $key, $default);
    }

    /**
     * Gets a plugin option for environment
     *
     * @return string
     */
    public static function get_env_option(string $key, $default = null)
    {
        $prefix = self::get_env() === 'test' ? 'dev_' : '';
        return self::get_option($prefix.$key, $default);
    }

    /**
     * Gets an array value by key
     *
     * @param array $array
     * @param string $key
     * @param boolean $default
     * @return mixed
     */
    public static function get_value(array $array, string $key)
    {
        return array_key_exists($key, $array) ? sanitize_text_field($array[$key]) : null;
    }

    /**
     * Gets a plugin environment
     *
     * @return string
     */
    public static function get_env()
    {
        return self::get_option('environment', 'prod');
    }
}
