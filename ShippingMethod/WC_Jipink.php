<?php

namespace Jipink\ShippingMethod;

use Jipink\Helper\Helper;
use Jipink\Api\JipinkSdk;

defined('ABSPATH') || class_exists('\WC_Shipping_method') || exit;

/**
 * Our main payment method class
 */
class WC_Jipink extends \WC_Shipping_method
{
    /**
     * Default constructor, loads settings and MercadoPago's SDK
     */
    public function __construct($instance_id = 0)
    {
        $this->instance_id = absint($instance_id);
        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();
        // Setup general properties.
        $this->setup_properties();
    }

    /**
     * Establishes default settings, and loads IPN Processor
     *
     * @return void
     */
    private function setup_properties()
    {
        $this->id = 'jipink';
        $this->title = 'Envío por Jipink';
        $this->method_title = 'Jipink';
        $this->method_description = __('Permite que tus compradores reciban sus pedidos con Jipink', 'jipink-for-woocommerce');
        $this->availability = 'including';
        $this->countries = array('AR');
        $this->supports = array(
            'shipping-zones',
            'instance-settings',
            'instance-settings-modal'
        );
    }

    /**
     * Declares our instance configuration
     *
     * @return void
     */
    public function init_form_fields() {
        $this->instance_form_fields = [
            'title' => [
                'title' => __('Title', 'woocommerce'),
                'type' => 'text',
                'placeholder' => 'Utilizar título por defecto',
                'description' => __('Elegí el nombre que sus clientes verán en la página de pago', 'wc-jipink')
            ]
        ];
    }

    /**
     * Calculates the shipping
     *
     * @return void
     */
    public function calculate_shipping($package = [])
    {
        Helper::log_info('calculate_shipping - using single origin');
        $jipinkSdk = new JipinkSdk();
        $items = Helper::get_items_from_cart(WC()->cart);
        $customer = Helper::get_customer_from_cart(WC()->customer);
        $req = [
            'address' => [
                'zip' => $customer['address']['zip'],
                'country' => $customer['address']['country'],
            ],
            'items' => $items,
        ];
        $rate = $jipinkSdk->get_price($req);
        if ($rate) {
            $title = $this->get_option('title');
            if (empty($title)) {
                $title = $rate['name'];
            }
            $cost = $rate['price'];
            $isFreeEnabled = Helper::get_option('has_free_shipping', false);
            $freePrice = Helper::get_option('free_shipping_value', 0);
            $freeZone = Helper::get_option('free_shipping_zone', 100);
            $cartPrice = WC()->cart->get_cart_contents_total();
            $cartZone = $rate['zone'];
            if ($isFreeEnabled && $cartZone <= $freeZone && $cartPrice >= $freePrice) {
                Helper::log_info('calculate_shipping - cart has free shipping');
                $cost = 0;
            }
            $this->add_rate([
                'id'        => $rate['code'],   // ID for the rate.
                'label'     => $title,          // Label for the rate.
                'cost'      => $cost            // Amount or array of costs (per item shipping).
            ]);
        }
    }
}
