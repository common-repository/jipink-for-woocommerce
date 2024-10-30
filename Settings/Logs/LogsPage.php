<?php

namespace Jipink\Settings\Logs;

use Jipink\Helper\Helper;

defined('ABSPATH') || exit;

/**
 * A main class that holds all our settings logic
 */
class LogsPage
{
    public static function initPage()
    {
        $logo_url = Helper::get_assets_folder_url() . '/img/logo.png';
        $upload_dir   = wp_upload_dir();
        $logPath = $upload_dir["basedir"] . "/wc-logs/WooCommerce-Jipink-*";
        $logs = array_reverse(glob($logPath));

        $fileContent = null;
        $fileLogName = null;
        if (sizeof($logs) > 0) {
            $fileLogName = $logs[0];
            $fileContent = nl2br(htmlentities(file_get_contents($fileLogName)));
        } ?>
        <div class="jipink-form-wrapper wrap">
            <div class="settings-header">
                <img src="<?php echo esc_url($logo_url); ?>" class="logo">
            </div>

            <div class="form-wrapper">
                <h1> <?php echo esc_textarea(__('Logs', 'jipink-for-woocommerce')) ?></h1>
                <?php
                if ($fileLogName) {
                    ?>
                    <div class="form-group">
                        <label for="exampleFormControlSelect1">Leyendo desde <?php echo esc_textarea($fileLogName) ?> </label>
                        </br></br>
                        <p id="log_code">
                            <?php echo $fileContent ?>
                        </p>
                    </div>
                <?php
                } ?>


            </div>
        </div>
<?php
    }
}
