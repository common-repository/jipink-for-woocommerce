<?php

namespace Jipink\Settings\Configuration;


use Jipink\Settings\Sections\Section;
use Jipink\Settings\Sections\SectionInterface;

/**
 * FreeShippingSection class
 */
class FreeShippingSection extends Section implements SectionInterface
{
    private $data = [
        'slug' => 'wc-jipink-free-shipping-settings'
    ];

    /**
     * Default constructor
     */
    public function __construct()
    {
        $this->data['name'] = __('Envío gratis', 'jipink-for-woocommerce');
        parent::__construct($this->data);
    }

    /**
     * Gets all our fields in this section
     *
     * @return array
     */
    public static function get_fields()
    {
        return [
            'has_free_shipping' => [
                'name' => __('Ofrecer envíos gratis', 'jipink-for-woocommerce'),
                'slug' => 'has_free_shipping',
                'description' => __('Ofrecer envíos gratis a partir de cierto monto.', 'jipink-for-woocommerce'),
                'options' => [
                    '0' => 'No',
                    '1' => 'Si'
                ],
                'default' => '0',
                'type' => 'select'
            ],
            'free_shipping_value' => [
                'name' => __('Monto mínimo', 'jipink-for-woocommerce'),
                'slug' => 'free_shipping_value',
                'description' => __('Monto mínimo de la orden para ofrecer envío gratis.', 'jipink-for-woocommerce'),
                'type' => 'number'
            ],
            'free_shipping_zone' => [
                'name' => __('Zona máxima', 'jipink-for-woocommerce'),
                'slug' => 'free_shipping_zone',
                'description' => __('Zona máxima donde se ofrece envío gratis.', 'jipink-for-woocommerce'),
                'type' => 'select',
                'default' => '100',
                'options' => [
                    '100' => 'Todas',
                    '0' => 'Zona 0',
                    '1' => 'Zona 1',
                    '2' => 'Zona 2',
                    '3' => 'Zona 3',
                    '4' => 'Zona 4',
                    '5' => 'Zona 5',
                    '6' => 'Zona 6',
                    '7' => 'Zona 7'
                ]
            ],
        ];
    }
}
