<?php

defined('ABSPATH') || exit;

// --- Init Hooks
add_action('admin_notices', ['Jipink\Helper\Helper', 'check_notices']);

// --- Settings
add_filter('plugin_action_links_' . plugin_basename(WCJipink::MAIN_FILE), ['WCJipink', 'create_settings_link']);
add_action('admin_enqueue_scripts', ['Jipink\Helper\Helper', 'add_assets_files']);

// --- Initiate fields in pages
add_action('admin_init', ['\Jipink\Settings\Configuration\ConfigurationPage', 'init_settings']);

// --- Shipment Method
add_filter('woocommerce_shipping_methods', ['WCJipink', 'add_shipping_method']);
add_filter('woocommerce_cart_shipping_method_full_label', ['WCJipink', 'customize_label_shipping_checkout'], 10, 2);

// --- Order section
add_action('woocommerce_order_status_changed', ['\Jipink\Orders\Processor', 'handle_order_status'], 10, 4);
add_action('add_meta_boxes', ['\Jipink\Orders\Metabox', 'create']);

// --- Webhook
add_action('woocommerce_api_jipink-get-order', ['\Jipink\Orders\Webhooks', 'get_order']);
add_action('woocommerce_api_jipink-promote-order', ['\Jipink\Orders\Webhooks', 'promote_order']);

// ---- Ask for review
add_action('admin_notices', ['WCJipink', 'qualify_application']);