<?php

namespace Jipink\Helper;

use Jipink\Api\JipinkSdk;

trait PageTrait
{
    /**
     * Adds our assets into our settings page
     *
     * @param string $hook
     * @return void
     */
    public static function add_assets_files(string $hook)
    {
        if ($hook === 'settings_page_wc-jipink-settings') {
            wp_enqueue_style('wc-jipink-settings-css');
        }
    }

    public static function render_page($pageName, $fields)
    {
        if (!is_admin() || !current_user_can('manage_options')) {
            die('what are you doing here?');
        }

        $nonce = Helper::get_value($_REQUEST, '_wpjipinknonce') ?? null;
        if ($nonce && !wp_verify_nonce($nonce, 'wc-jipink-save-preferences')) {
            die('what are you doing here?');
        }
        $submit = Helper::get_value($_REQUEST, 'submit') ?? null;
        if ($submit) {
            self::save_settings($_POST, $fields);
        }
        wp_enqueue_script("jquery-ui-core");
        wp_enqueue_script("jquery-ui-autocomplete");
        $logo_url = Helper::get_assets_folder_url() . '/img/logo.png';
        $video_url = 'https://www.youtube.com/embed/TxfKqg1Qh5Q';
?>
        <div class="jipink-form-wrapper wrap">
            <div class="settings-header">
                <img src="<?php echo esc_url_raw($logo_url) ?>" class="logo">
            </div>
            <form action="admin.php?page=<?php echo esc_attr($pageName); ?>&submit=true" method="post" class="form-wrapper">
                <?php
                settings_fields($pageName);
                if ($video_url) {
                ?>
                    <iframe width="600" height="400" src="<?php echo esc_url($video_url); ?>"></iframe>
                <?php
                }
                do_settings_sections($pageName);
                wp_nonce_field('_wpjipinknonce', 'wc-jipink-save-preferences');
                submit_button(__('Guardar', 'jipink-for-woocommerce'));
                ?>
            </form>
        </div>

<?php
    }

    /**
     * Saves all our fields, and sanitizes them.
     *
     * @param array $post_data
     * @return bool
     */
    public static function save_settings(array $post_data, $settings_fields)
    {
        $saved = false;
        foreach ($settings_fields as $setting) {
            $slug = $setting['slug'];
            if (!isset($post_data[$slug])) {
                continue;
            }
            $value = sanitize_text_field($post_data[$setting['slug']]);
            $value = sanitize_text_field($value);
            $value = strip_tags($value);
            update_option('wc-jipink-' . $setting['slug'], $value);
            $saved = true;
        }
        $pat = Helper::get_env_option('pat');
        if ($pat) {
            $jipink_sdk = new JipinkSdk();
            $response = $jipink_sdk->setup();
            $saved = ($response !== false);
        }
        if ($saved) {
            Helper::add_success(__('Se guardaron tus ajustes', 'jipink-for-woocommerce'), true);
        } else {
            Helper::add_error(__('No se pudieron guardar tus ajustes', 'jipink-for-woocommerce'), true);
        }
        return $saved;
    }
}
