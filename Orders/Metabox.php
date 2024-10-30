<?php

namespace Jipink\Orders;

use Jipink\Helper\Helper;

defined('ABSPATH') || exit;

class Metabox
{
    public static function create()
    {
        $order_types = wc_get_order_types('order-meta-boxes');
        foreach ($order_types as $order_type) {
            add_meta_box(
                'jipink_metabox',        // Unique ID
                'Jipink',                // Box title
                [__CLASS__, 'content'],  // Content callback, must be of type callable
                $order_type,
                'side',
                'default'
            );
        }
    }

    public static function content($post, $metabox)
    {
        $order = wc_get_order($post->ID);
        if (empty($order)) {
            return false;
        }
        $shipping_method = Helper::get_shipping_method($order);
        if (!$shipping_method) {
            return;
        }

        $container_jipink = '';
        if (!empty($shipping_method->get_meta('tracking_code'))) {
            $env = Helper::get_env() === 'test' ? 'dev-' : '';
            $tracking_code = $shipping_method->get_meta('tracking_code');
            $tracking_url = $shipping_method->get_meta('tracking_url');
            $detail_url = 'https://'.$env.'desk.jipink.com/shipments/'.$tracking_code.'/detail';
            $container_jipink .= '<p>';
            $container_jipink .= 'Código: <strong>'.$tracking_code.'</strong>';
            $container_jipink .= '</p>';
            $container_jipink .= '<p style="padding: 10px 0px;">';
            $container_jipink .= '<a style="float: none;" href="' . $tracking_url . '" target="_blank">' . __('Ir al tracking', 'jipink-for-woocommerce') . '</a>';
            $container_jipink .= '<a class="button-primary" style="float: right;" href="' . $detail_url . '" target="_blank">' . __('Gestionar', 'jipink-for-woocommerce') . '</a>';
            $container_jipink .= '</p>';
        } else {
            $container_jipink .= __('La orden aún no fue procesada.', 'jipink-for-woocommerce');
        }
        echo $container_jipink;
    }
}
