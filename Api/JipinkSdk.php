<?php

namespace Jipink\Api;

use Jipink\Api\JipinkApi;

use Jipink\Helper\Helper;
use Exception;

class JipinkSdk
{
    private $api;
    public function __construct()
    {
        $this->api = new JipinkApi(
            Helper::get_option('customer_id', ''),
            Helper::get_env_option('pat', ''),
            Helper::get_option('environment', 'prod')
        );
    }

    /**
     * Setup plugin for account
     *
     * @return false|array
     */
    public function setup()
    {
        $body = [
            "name" => get_bloginfo('name'),
            "pat" => Helper::get_env_option('pat'),
            "url" => get_site_url(),
        ];
        $res = $this->api->post("/pub/woo/setup", $body);
        Helper::log_info(sprintf(__('%s - Data sent to Jipink: %s', 'jipink-for-woocommerce'), __FUNCTION__, json_encode($body)));
        Helper::log_info(sprintf(__('%s - Data received from Jipink: %s', 'jipink-for-woocommerce'), __FUNCTION__, json_encode($res)));
        return $res;
    }

    /**
     * Gets a quote for an order
     *
     * @param array $from
     * @param array $to
     * @param array $items
     * @return array|false
     */
    public function get_price(array $req)
    {
        try {
            $res = $this->api->post('/pub/woo/quote', $req);
            Helper::log_info(sprintf(__('%s - Data sent to Jipink: %s', 'jipink-for-woocommerce'), __FUNCTION__, json_encode($req)));
            Helper::log_info(sprintf(__('%s - Data received from Jipink: %s', 'jipink-for-woocommerce'), __FUNCTION__, json_encode($res)));
            if ($res) {
                $rates = $res['rates'];
                if (!empty($rates)) {
                    return $rates[0];
                }
            }

        } catch (Exception $e) {
            Helper::log_info(__('Quote could not be processed', 'jipink-for-woocommerce'));
            Helper::log_info($e->getMessage());
        }
        return false;
    }

    /**
     * Process an order in Jipink's Api
     *
     * @return array|false
     */
    public function process_order(\WC_Order $order)
    {
        $data_to_send = Helper::get_shipping_data($order);
        Helper::log_info(sprintf(__('%s - Data sent to Jipink: %s', 'jipink-for-woocommerce'), __FUNCTION__, json_encode($data_to_send)));
        $res = $this->api->post('/pub/woo/notify', $data_to_send);
        Helper::log_info(sprintf(__('%s - Data received from Jipink: %s', 'jipink-for-woocommerce'), __FUNCTION__, json_encode($res)));

        if (!$res) {
            Helper::log_error(__('Order could not be processed', 'jipink-for-woocommerce'));
            Helper::log_error(sprintf(__('%s - Data sent to Jipink: %s', 'jipink-for-woocommerce'), __FUNCTION__, json_encode($data_to_send)));
            return false;
        }
        return $res;
    }
}
