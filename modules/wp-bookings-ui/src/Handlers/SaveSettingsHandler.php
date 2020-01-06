<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers;

use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Exception\CreateRuntimeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Psr\EventManager\EventInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use stdClass;
use Traversable;

/**
 * Handle saving of settings.
 *
 * @since [*next-version*]
 */
class SaveSettingsHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIntCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateRuntimeExceptionCapableTrait;

    /**
     * List of settings fields.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|Traversable
     */
    protected $fields;

    /**
     * List of settings fields that should be serialized.
     *
     * @since [*next-version*]
     *
     * @var array
     */
    protected $arrayFields;

    /**
     * Setting option prefix.
     *
     * @since [*next-version*]
     *
     * @var string|Stringable
     */
    protected $prefix;

    /**
     * SaveSettingsHandler constructor.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $fields      List of settings fields.
     * @param array|stdClass|Traversable $arrayFields List of settings fields that should be serialized.
     * @param string|Stringable          $prefix      Setting option prefix.
     */
    public function __construct($fields, $arrayFields, $prefix)
    {
        $this->fields      = $fields;
        $this->arrayFields = $this->_normalizeArray($arrayFields);
        $this->prefix      = $prefix;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        /* @var $event EventInterface */
        $event = func_get_arg(0);

        if (!($event instanceof EventInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not an event instance'), null, null, $event
            );
        }

        $this->_handleSave();
    }

    /**
     * Save settings.
     *
     * @since [*next-version*]
     */
    protected function _handleSave()
    {
        $data   = $this->_getRequestData();
        $fields = $this->_normalizeArray($this->fields);

        foreach ($data as $key => $value) {
            if (in_array($key, $fields)) {
                $this->_saveSetting($key, $value);
            }
        }
    }

    /**
     * Get data from request.
     *
     * @since [*next-version*]
     *
     * @return array|mixed|object Request data.
     */
    protected function _getRequestData()
    {
        return json_decode(file_get_contents('php://input'), true);
    }

    /**
     * Update setting.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $field Setting field.
     * @param mixed             $value Setting value.
     */
    protected function _saveSetting($field, $value)
    {
        $field = $this->_normalizeString($field);
        $key   = $this->_getFieldKey($field);

        update_option($key, $value);
    }

    /**
     * Prepare setting option key from field name.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $field Field name to prepare key from.
     *
     * @return string Prepared setting option key from field name.
     */
    protected function _getFieldKey($field)
    {
        $field = $this->_normalizeString($field);

        return $this->prefix . '/' . $field;
    }
}
