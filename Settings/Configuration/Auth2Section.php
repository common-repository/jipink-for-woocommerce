<?php

namespace Jipink\Settings\Configuration;


use Jipink\Settings\Sections\Section;
use Jipink\Settings\Sections\SectionInterface;

/**
 * Auth2Section class
 */
class Auth2Section extends Section implements SectionInterface
{
    private $data = [
        'slug' => 'wc-jipink-auth2-settings'
    ];

    /**
     * Default constructor
     */
    public function __construct()
    {
        $this->data['name'] = __('Auth 2.0', 'jipink-for-woocommerce');
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
            'pat' => [
                'name' => __('Token', 'jipink-for-woocommerce'),
                'slug' => 'pat',
                'description' => __('Personal Access token (PAT) de tus credenciales.', 'jipink-for-woocommerce'),
                'type' => 'password'
            ],
        ];
    }
}
