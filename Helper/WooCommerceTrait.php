<?php

namespace Jipink\Helper;

trait WooCommerceTrait
{
    /**
     * Gets the order matching the given number.
     */
    public static function get_order_by_number($order_number)
    {
        $args = array(
            'post_type' => 'shop_order',
            'post_status' => 'any',
            'meta_query' => array(
                array(
                    'key' => '_order_number',
                    'value' => $order_number,
                    'compare' => '=',
                )
            )
        );
        $query = new \WP_Query($args);
        $order = null;
        if (!empty($query->posts)) {
            // Get order by number
            $order_id = $query->posts[0]->ID;
            $order = wc_get_order($order_id);
        } else {
            // Get order by id instead
            $order = wc_get_order($order_number);
        }
        if ($order) {
            return self::get_shipping_data($order);
        }
        return null;
    }

    /**
     * Get the shipping data from a given order.
     */
    public static function get_shipping_data(\WC_Order $order)
    {
        $destination = Helper::get_customer_from_cart($order);
        $items = Helper::get_items_from_order($order);
        $parse = parse_url(get_site_url());
        return [
            'ref' => $order->get_order_number(),
            'site' => get_site_url(),
            'notes' => $order->get_customer_note(),
            'external' => $order->get_id(),
            'value' => $order->get_total(),
            'destination' => $destination,
            'items' => $items,
        ];
    }

    /**
     * Gets the customer from a WooCommerce Cart
     *
     * @param WC_Customer $customer
     * @return array|false
     */
    public static function get_customer_from_cart($customer)
    {
        $first_name = self::get_customer_first_name($customer);
        $last_name = self::get_customer_last_name($customer);
        $name = "$first_name $last_name";
        $email = $customer->get_billing_email();
        $phone = $customer->get_billing_phone();
        $postal_code = self::get_postal_code($customer);
        $street = self::get_address($customer);
        $apartment = self::get_apartment($customer);
        $province = self::get_province($customer);
        $locality = self::get_locality($customer);
        $country = $customer->get_shipping_country();

        return [
            "contact"=>[
                "name"=> $name,
                "email"=> $email,
                "phone"=> $phone,
            ],
            "address"=>[
                "street" => $street,
                "apartment" => $apartment,
                "city" => $locality,
                "state" => $province,
                "country" => $country,
                "zip" => $postal_code,
            ]
        ];
    }

    public static function get_custom_shipping_type($type, $customer)
    {
        if (session_status() == PHP_SESSION_NONE) {
            return $customer->get_meta("_billing_jipink_$type");
        } elseif (isset(WC()->session)) {
            return WC()->session->get("jipink_$type");
        }
        return '';
    }

    public static function get_shipping_method($order)
    {
        if (!$order->has_shipping_method('jipink')) {
            return null;
        }

        $method = null;
        foreach ($order->get_shipping_methods() as $shipping_method) {
            $is_jipink = ($shipping_method['method_id'] === 'jipink');
            if ($is_jipink) {
                $method = $shipping_method;
            }
        }
        return $method;
    }

    public static function get_province($customer)
    {
        $province = strtolower(self::get_province_wc($customer));
        $map = [
            'metropolitana de santiago' => 'Region Metropolitana de Santiago'
        ];
        return isset($map[$province]) ? $map[$province] : $province;
    }
    /**
     * Gets the province from a customer
     *
     * @param WC_Customer $customer
     * @return string
     */
    private static function get_province_wc($customer)
    {
        $province = '';
        if (!($province = $customer->get_shipping_state())) {
            $province = $customer->get_billing_state();
        }

        $country = $customer->get_shipping_country();
        $states =  WC()->countries->get_shipping_country_states();
        if (!isset($states[$country])) {
            return $province;
        }

        $stateOptions = $states[$country];
        if (isset($stateOptions[$province])) {
            return $stateOptions[$province];
        }
        return $province;
    }

    /**
     * Gets the locality from a customer
     *
     * @param WC_Customer $customer
     * @return string
     */
    public static function get_locality($customer)
    {
        $locality = '';
        if (!($locality = $customer->get_shipping_city())) {
            $locality = $customer->get_billing_city();
        }
        return $locality;
    }

    /**
     * Gets the postal code from a customer
     *
     * @param WC_Customer $customer
     * @return string
     */
    public static function get_postal_code($customer)
    {
        $postal_code = '';
        if (!($postal_code = $customer->get_shipping_postcode())) {
            $postal_code = $customer->get_billing_postcode();
        }
        return $postal_code;
    }

    /**
     * Gets the customer first name
     *
     * @param WC_Customer $customer
     * @return string
     */
    public static function get_customer_first_name($customer)
    {
        $name = '';
        if ($customer->get_shipping_first_name()) {
            $name = $customer->get_shipping_first_name();
        } else {
            $name = $customer->get_billing_first_name();
        }
        return $name;
    }

    /**
     * Gets the customer last name
     *
     * @param WC_Customer $customer
     * @return string
     */
    public static function get_customer_last_name($customer)
    {
        $name = '';
        if ($customer->get_shipping_last_name()) {
            $name = $customer->get_shipping_last_name();
        } else {
            $name = $customer->get_billing_last_name();
        }
        return $name;
    }

    /**
     * Gets the address of an order
     *
     * @param WC_Order $order
     * @return false|array
     */
    public static function get_address($order)
    {
        if (!$order) {
            return false;
        }
        if ($order->get_shipping_address_1()) {
            return $order->get_shipping_address_1();
        }
        return $order->get_billing_address_1();
    }

    /**
     * Gets the address of an order
     *
     * @param WC_Order $order
     * @return false|array
     */
    public static function get_apartment($order)
    {
        if (!$order) {
            return false;
        }
        if ($order->get_shipping_address_2()) {
            return $order->get_shipping_address_2();
        }
        return $order->get_billing_address_2();
    }
    /**
     * Gets product dimensions and details
     *
     * @param int $product_id
     * @return false|array
     */
    public static function get_product_dimensions($product_id, $quantity = 1)
    {
        $product = wc_get_product($product_id);
        if (!$product) {
            return false;
        }
        $dimension_unit = 'cm';
        $weight_unit = 'kg';
        $new_product = array(
            'height' => round(
                wc_get_dimension(floatval($product->get_height()), $dimension_unit),
                2
            ),
            'width' => round(
                wc_get_dimension(floatval($product->get_width()), $dimension_unit),
                2
            ),
            'length' => round(
                wc_get_dimension(floatval($product->get_length()), $dimension_unit),
                2
            ),
            'weight' => round(
                wc_get_weight(floatval($product->get_weight()), $weight_unit),
                2
            ),
            'price' => $product->get_price(),
            'name' => $product->get_name(),
            'quantity' => $quantity,
        );
        return ["data" => $new_product];
    }

    /**
     * Gets all items from a cart
     *
     * @param WC_Cart $cart
     * @return false|array
     */
    public static function get_items_from_cart($cart)
    {
        $products = array();
        $items = $cart->get_cart();
        foreach ($items as $item) {
            $product_id = $item['data']->get_id();
            $new_product = self::get_product_dimensions($product_id, $item['quantity']);
            $products[] = $new_product;
        }
        return $products;
    }

    /*
    */
    public static function get_items_per_package($package)
    {
        $parsed_items = [];
        foreach ($package['contents'] as $item) {
            $product_id = $item['product_id'];
            $parsed_items[] = self::get_product_dimensions($product_id, $item['quantity']);
        }
        return $parsed_items;
    }

    /**
     * Gets items by vendor
     *
     * @param WC_Cart $cart
     * @return false|array
     */
    public static function divide_items_per_vendor($cart)
    {
        $vendor_items = array();
        $items = $cart->get_cart();
        foreach ($items as $item) {
            $product_id = $item['data']->get_id();
            $new_product = self::get_product_dimensions($product_id, $item['quantity']);
            $product = wc_get_product($product_id);
            $vendor_id = $product->post->post_author;
            if (!isset($vendor_items[$vendor_id])) {
                $vendor_items[$vendor_id] = [];
            }
            $vendor_items[$vendor_id][] = $new_product;
        }
        return $vendor_items;
    }

    /**
     * Gets items from an order
     *
     * @param WC_Order $order
     * @return false|array
     */
    public static function get_items_from_order($order)
    {
        $products = array();
        $items = $order->get_items();
        foreach ($items as $item) {
            $product_id = $item->get_variation_id();
            if (!$product_id) {
                $product_id = $item->get_product_id();
            }
            $new_product = self::get_product_dimensions($product_id, $item['quantity']);
            $products[] = $new_product;
        }
        return $products;
    }
}
