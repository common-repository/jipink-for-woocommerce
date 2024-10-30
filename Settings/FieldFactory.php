<?php

namespace Jipink\Settings;

use Jipink\Settings\Fields\DescriptionField;
use Jipink\Settings\Fields\NumberField;
use Jipink\Settings\Fields\SelectField;
use Jipink\Settings\Fields\TextField;
use Jipink\Settings\Fields\PasswordField;

/**
 * This factory creates a FieldInterface
 */
class FieldFactory
{

    /**
     * Creates a Field
     *
     * @param string $slug
     * @return FieldInterface|false
     */
    public function create($fields)
    {
        switch ($fields['type']) {
            case 'text':
                $field = new TextField($fields);
                break;
            case 'password':
                $field = new PasswordField($fields);
                break;
            case 'select':
                $field = new SelectField($fields);
                break;
            case 'number':
                $field = new NumberField($fields);
                break;
            case 'description':
                $field = new DescriptionField($fields);
                break;
            default:
                $field = false;
        }
        return $field;
    }
}
