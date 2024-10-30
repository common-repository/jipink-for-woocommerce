<?php

namespace Jipink\Orders;

use Jipink\Helper\DatabaseTrait;
use Jipink\Helper\Helper;
use Jipink\Api\JipinkSdk;
use Error;
use Exception;
use TypeError;

defined('ABSPATH') || exit;

class Processor
{
    /**
     * Handles the WooCommerce order status
     *
     * @param int $order_id
     * @param string $status_from
     * @param string $status_to
     * @param WC_Order $order
     * @return void
     */
    public static function handle_order_status(int $order_id, string $status_from, string $status_to, \WC_Order $order)
    {
        $shipping_method = Helper::get_shipping_method($order);
        if (!$shipping_method) {
            Helper::log_info("Skipping order $order_id because it does not have our shipping method.");
            return;
        }
        Helper::log_info("Handling status from [$status_from] to [$status_to] for order $order_id.");
        $tracking_code = $shipping_method->get_meta('tracking_code');
        if (empty($tracking_code) && $status_to === 'processing') {
            self::process_order_and_childrens($order, $shipping_method);
        } else {
            Helper::log_info("Nothing to do with order $order_id.");
        }
    }

    /**
     * Process an order in Jipink
     *
     * @return void
     */
    public static function process_order_and_childrens($order, $shipping_method = null)
    {
        $list_of_child_orders = DatabaseTrait::get_orders_by_parent_id($order->get_id());
        if (empty($list_of_child_orders)) {
            return (array) self::format_creation($order, $shipping_method);
        }
        $list_of_tracking_ids = [];
        foreach ($list_of_child_orders as $child) {
            $child_order = wc_get_order($child->id);
            $tracking_id = self::format_creation($child_order);
            if ($tracking_id) {
                $list_of_tracking_ids[] = $tracking_id;
            }
        }

        return $list_of_tracking_ids;
    }

    public static function format_creation($order, $shipping_method = null)
    {
        try {
            Helper::log_info("Processing order {$order->get_id()} for creation");
            $jipinkSdk = new JipinkSdk();
            $res = $jipinkSdk->process_order($order);
            if (!$res) {
                Helper::log_info($res);
                return false;
            }
            $tracking_code = $res['code'];
            self::set_shipping_method_in_order($order, $res, $shipping_method);
            return $tracking_code;
        } catch (Exception $e) {
            Helper::log_info($e->getMessage());
            return null;
        } catch (TypeError $e) {
            Helper::log_info($e->getMessage());
            return null;
        } catch (Error $e) {
            Helper::log_info($e->getMessage());
            return null;
        }
    }

    public static function set_shipping_method_in_order($order, $jipinkOrder, $shipping_method = null)
    {
        $item = $shipping_method ?? new  \WC_Order_Item_Shipping();
        $jipinkShippingmethod = WC()->shipping->get_shipping_methods()['jipink'];
        $tracking_code = $jipinkOrder['code'];
        $tracking_url = $jipinkOrder['url'];
        $item->set_method_title($jipinkShippingmethod->method_title);
        $item->set_method_id($jipinkShippingmethod->id);
        $item->update_meta_data('tracking_code', $tracking_code);
        $item->update_meta_data('tracking_url', $tracking_url);
        $order->add_item($item);
        $order->save();
    }
}
