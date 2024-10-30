<?php

use Jipink\Helper\Helper;

/**
 * Plugin Name: Jipink for WooCommerce
 * Description: Integrá tu tienda WooCommerce con Jipink para ofrecer envíos en el día.
 * Version: 2.1
 * Requires PHP: 7.0
 * Author: Jipink
 * Author URI: https://jipink.com
 * Text Domain: jipink-for-woocommerce
 * WC requires at least: 3.3
 * WC tested up to: 6.5
 */

defined('ABSPATH') || exit;

/**
 * Plugin's base Class
 */
class WCJipink
{
    public const PLUGIN_NAME = 'Jipink';
    public const MAIN_FILE = __FILE__;
    public const MAIN_DIR = __DIR__;

    public function __construct()
    {
        add_action('plugins_loaded', [$this, 'setScripts']);
        add_action('admin_menu', [$this, 'set_menu_pages'], 11);
        add_action('admin_enqueue_scripts', [$this, 'register_scripts']);
    }
    /**
     * Checks system requirements
     *
     * @return bool
     */
    public static function check_system()
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        $system = self::check_components();

        if ($system['flag']) {
            deactivate_plugins(plugin_basename(__FILE__));
            echo esc_html('<div class="notice notice-error is-dismissible">'
                . '<p>' . sprintf(__('<strong>%s/strong> Requires at least %s version %s or greater.', 'jipink-for-woocommerce'), self::PLUGIN_NAME, $system['flag'], $system['version']) . '</p>'
                . '</div>');
            return false;
        }

        if (!class_exists('WooCommerce')) {
            deactivate_plugins(plugin_basename(__FILE__));
            echo esc_html('<div class="notice notice-error is-dismissible">'
                . '<p>' . sprintf(__('WooCommerce must be active before using <strong>%s</strong>', 'jipink-for-woocommerce'), self::PLUGIN_NAME) . '</p>'
                . '</div>');
            return false;
        }

        return true;
    }

    /**
     * Check the components required for the plugin to work (PHP, WordPress and WooCommerce)
     *
     * @return array
     */
    private static function check_components()
    {
        global $wp_version;
        $flag = $version = false;

        if (version_compare(PHP_VERSION, '7.0', '<')) {
            $flag = 'PHP';
            $version = '7.0';
        } elseif (version_compare($wp_version, '4.9', '<')) {
            $flag = 'WordPress';
            $version = '4.9';
        } elseif (!defined('WC_VERSION') || version_compare(WC_VERSION, '3.3', '<')) {
            $flag = 'WooCommerce';
            $version = '3.3';
        }

        return [
            'flag' => $flag,
            'version' => $version
        ];
    }

    /**
     * Inits our plugin
     *
     * @return void
     */
    public function setScripts()
    {
        if (!self::check_system()) {
            return false;
        }

        spl_autoload_register(
            function ($class) {
                if (strpos($class, 'Jipink') === false) {
                    return;
                }

                $name = str_replace('\\', '/', $class);
                $name = str_replace('Jipink/', '', $name);
                require_once plugin_dir_path(__FILE__) . $name . '.php';
            }
        );
        include_once __DIR__ . '/Hooks.php';
        Helper::init();
        self::load_textdomain();
    }

    public function set_menu_pages()
    {
        add_menu_page(
            'Ajustes',
            'Jipink',
            'manage_options',
            'wc-jipink-settings',
            ['\Jipink\Settings\Configuration\ConfigurationPage', 'initPage'],
            plugin_dir_url(__FILE__) . 'assets/img/menu.svg'
        );

        add_submenu_page(
            'wc-jipink-settings',
            'Ajustes',
            'Ajustes',
            'manage_options',
            'wc-jipink-settings'
        );

        add_submenu_page(
            'wc-jipink-settings',
            'Ayuda',
            'Ayuda',
            'manage_options',
            'wc-jipink-help',
            ['\Jipink\Settings\Support\SupportPage', 'initPage']
        );

        add_submenu_page(
            'wc-jipink-settings',
            'Logs',
            'Logs',
            'manage_options',
            'wc-jipink-logs',
            ['\Jipink\Settings\Logs\LogsPage', 'initPage']
        );
    }

    /**
     * Registers all scripts to be loaded laters
     *
     * @return void
     */
    public static function register_scripts()
    {
        wp_enqueue_style('wc-jipink-settings-css', Helper::get_assets_folder_url() . '/css/settings.css');
    }

    /**
     * Create a link to the settings page, in the plugins page
     *
     * @param array $links
     * @return array
     */
    public static function create_settings_link(array $links)
    {
        $link = '<a href="' . esc_url(get_admin_url(null, 'admin.php?page=wc-jipink-settings')) . '">' . __('Settings', 'jipink-for-woocommerce') . '</a>';
        array_unshift($links, $link);
        return $links;
    }

    /**
     * Adds our shipping method to WooCommerce
     *
     * @param array $shipping_methods
     * @return array
     */
    public static function add_shipping_method($shipping_methods)
    {
        $shipping_methods['jipink'] = '\Jipink\ShippingMethod\WC_Jipink';
        return $shipping_methods;
    }

    public static function customize_label_shipping_checkout($label, $method)
    {
        if ($method->method_id === 'jipink') {
           if ($method->cost == 0) {
               $label .= ' ¡GRATIS!';
           }
        }
        return $label;
    }

    /**
     * Loads the plugin text domain
     *
     * @return void
     */
    public static function load_textdomain()
    {
        load_plugin_textdomain('wc-jipink', false, basename(dirname(__FILE__)) . '/languages');
    }

    /**
     * Display a message after 10, 30 and 100 shippings.
     *
     * @author Axel candia
     */
    public static function qualify_application()
    {
        global $wpdb;
        $minShippings = get_option('wc-jipink-min-shippings');
        if ($minShippings == null) {
            update_option('wc-jipink-min-shippings', 10);
            $minShippings = 10;
        }

        if ($minShippings == -1) {
            return;
        }

        $order_items_table = $wpdb->prefix . 'woocommerce_order_items';
        $query = "SELECT count(*) FROM $order_items_table WHERE order_item_name = 'Jipink'  ";
        $shippingsWithJipink = $wpdb->get_var($query);
        if ($shippingsWithJipink < $minShippings) {
            return;
        } ?>
        <div class="notice notice-success" id="jipink-rate-app" data-jipink-ajax-url=<?php echo(esc_url(admin_url('admin-ajax.php'))) ?> data-jipink-ajax-nonce=<?php echo esc_textarea((wp_create_nonce('jipink-for-woocommerce'))) ?>>
            <div>
                <p>
                    <?php esc_textarea((sprintf(
            __("Hey! Congratulations for your %d shipping with Jipink!! We hope you are enjoying our plugin.
                            Could you please do me a BIG favor and give it a 5-star rating on WordPress?
                            Just to help us spread the word and boost our motivation.", 'jipink-for-woocommerce'),
            $minShippings
        ))); ?>
                </p>
                <strong><em>~ Axel Candia</em></strong>
            </div>
            <ul>
                <li><a data-rate-action="rate" href="https://wordpress.org/support/plugin/jipink-for-woocommerce/reviews/#postform" target="_blank"><?php echo esc_textarea((__("Yes sure!!", 'jipink-for-woocommerce'))) ?></a> </li>
                <li><a data-rate-action="done-rating" href="#"><?php echo esc_textarea((__("I already did", 'jipink-for-woocommerce'))) ?></a></li>
                <li><a data-rate-action="deny-rating" href="#"><?php echo esc_textarea((__("No thanks", 'jipink-for-woocommerce'))) ?></a></li>
            </ul>
        </div>
<?php
    }
}
$settings_page = new WCJipink();
