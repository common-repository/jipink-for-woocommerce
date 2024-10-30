<?php

namespace Jipink\Settings\Sections;

use Jipink\Settings\FieldFactory;

/**
 * Base Section class
 */
class Section
{
    private $data = [];

    /**
     * Default constructor
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Adds the section itself into the settings page
     *
     * @return void
     */
    public function add($pageName)
    {
        add_settings_section(
            $this->data['slug'],
            $this->data['name'],
            '',
            $pageName
        );

        $settings_fields = $this->get_fields();
        foreach ($settings_fields as $setting) {
            add_settings_field(
                'wc-jipink-' . $setting['slug'],
                $setting['name'],
                function () use ($setting) {
                    $fFactory = new FieldFactory();
                    $field = $fFactory->create($setting);
                    if ($field !== false) {
                        $field->render();
                    }
                },
                $pageName,
                $this->data['slug']
            );
        }
    }
}
