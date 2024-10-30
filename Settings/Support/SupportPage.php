<?php

namespace Jipink\Settings\Support;

use Jipink\Helper\Helper;


defined('ABSPATH') || exit;

/**
 * A main class that holds all our settings logic
 */
class SupportPage
{

    public static function initPage()
    {
        $logo_url = Helper::get_assets_folder_url() . '/img/logo.png';
?>
        <div class="jipink-form-wrapper wrap">
            <div class="settings-header">
                <img src="<?php echo esc_url($logo_url); ?>" class="logo">
            </div>

            <div class="form-wrapper">
                <h1> <?php echo esc_textarea(__('Ayuda', 'jipink-for-woocommerce')) ?></h1>
                <p> <?php echo esc_textarea(__('Si tenés dudas sobre nuestra integración con WooCommerce, revisá nuestra documentación haciendo click', 'jipink-for-woocommerce')) ?>
                    <a target="_blank" href="https://academy.jipink.com/docs/integraciones/integracion-con-woocommerce">
                        <?php echo esc_textarea(__('acá', 'jipink-for-woocommerce')) ?></a>
                </p>
            </div>
    <?php
    }
}
