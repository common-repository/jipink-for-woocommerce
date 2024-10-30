<?php

namespace Jipink\Orders;

use Jipink\Helper\DatabaseTrait;
use Jipink\Helper\Helper;
use Jipink\Api\JipinkSdk;

defined('ABSPATH') || exit;

class Webhooks
{
    /**
     * Receives the webhook to get an order by number
     *
     * @return void
     */
    public static function get_order()
    {
        try {
            $raw_input = file_get_contents('php://input');
            $json_input = json_decode($raw_input);
            $order_number = $json_input->number;
            if ($order_number) {
                $response = Helper::get_order_by_number($order_number);
                if ($response) {
                    header('Content-Type: application/json');
                    echo json_encode($response);
                    exit;
                } else {
                    wp_die('Order Not Found', '404 Not Found', array('response' => 404));
                }
            } else {
                wp_die('Bad Request', '400 Bad Request', array('response' => 400));
            }
        } catch (\Throwable $e) {
            Helper::log_info('Failed to get order by number');
            Helper::log_info($e->getMessage());
            wp_die('Internal Server Error', '500 Internal Server Error', array('response' => 500));
        }
    }

    /**
     * Receives the webhook to notify an order was promoted
     *
     * @return void
     */
    public static function promote_order()
    {
        try {
            $raw_input = file_get_contents('php://input');
            $json_input  = json_decode($raw_input);
            $response = array(
                'status' => 'success',
                'message' => 'Method not implemented yet',
            );
            header('Content-Type: application/json');
            echo json_encode($response);
            exit;
        } catch (\Throwable $e) {
            Helper::log_info('Failed to promote an order');
            Helper::log_info($e->getMessage());
            wp_die('Internal Server Error', '500 Internal Server Error', array('response' => 500));
        }
    }
}
