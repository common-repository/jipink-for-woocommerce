<?php

namespace Jipink\Settings\Configuration;


use Jipink\Settings\Sections\Section;
use Jipink\Settings\Sections\SectionInterface;

/**
 * DevelopSection class
 */
class DevelopSection extends Section implements SectionInterface
{
    private $data = [
        'slug' => 'wc-jipink-cds-settings'
    ];

    /**
     * Default constructor
     */
    public function __construct()
    {
        $this->data['name'] = __('Desarrollo', 'jipink-for-woocommerce');
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
            'dev_pat' => [
                'name' => __('Dev Token', 'jipink-for-woocommerce'),
                'slug' => 'dev_pat',
                'description' => __('Personal Access Token (PAT) para el ambiente de Desarrollo. Esta opción es utilizada principalmente por el equipo de desarrollo de Jipink.', 'jipink-for-woocommerce'),
                'type' => 'password'
            ],
            'environment' => [
                'name' => __('Environment', 'jipink-for-woocommerce'),
                'slug' => 'environment',
                'description' => __('Cambiar el ambiente utilizado por el plugin. Esta opción es utilizada principalmente por el equipo de desarrollo de Jipink.', 'jipink-for-woocommerce'),
                'type' => 'select',
                'options' => [
                    'prod' => __('Producción', 'jipink-for-woocommerce'),
                    'test' => __('Desarrollo', 'jipink-for-woocommerce')
                ]
            ],
            'debug' => [
                'name' => __('Debug', 'jipink-for-woocommerce'),
                'slug' => 'debug',
                'description' => __('Activar el modo debug para desarrolladores, permitiendo tener mayor nivel de detalle en los Logs generados por el plugin.', 'jipink-for-woocommerce'),
                'type' => 'select',
                'options' => [
                    '0' => 'No',
                    '1' => 'Sí'
                ]
            ]

        ];
    }
}
