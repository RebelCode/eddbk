<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers;

use Dhii\Collection\MapInterface;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerGetPathCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Object\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Psr\Container\ContainerInterface;
use Psr\EventManager\EventInterface;
use RebelCode\Bookings\WordPress\Module\IteratorToArrayRecursiveCapableTrait;
use stdClass;
use Traversable;

/**
 * Handler for settings UI state.
 *
 * @since [*next-version*]
 */
class SettingsStateHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use ContainerGetPathCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /* @since [*next-version*] */
    use IteratorToArrayRecursiveCapableTrait;

    /**
     * Settings container.
     *
     * @since [*next-version*]
     *
     * @var ContainerInterface
     */
    protected $settingsContainer;

    /**
     * Map of available fields to their available options.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|MapInterface
     */
    protected $fieldsOptions;

    /**
     * List of settings fields.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|Traversable
     */
    protected $fields;

    /**
     * Update endpoint configuration.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|MapInterface
     */
    protected $updateEndpoint;

    /**
     * Wizard default labels.
     *
     * @since [*next-version*]
     *
     * @var array
     */
    protected $defaultWizardLabels;

    /**
     * SettingsStateHandler constructor.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface          $settingsContainer   Settings container.
     * @param array|stdClass|MapInterface $fieldsOptions       Map of available fields to their available options.
     * @param array|stdClass|Traversable  $fields              List of settings fields.
     * @param array|stdClass|MapInterface $updateEndpoint      Configuration of update endpoint.
     * @param array                       $defaultWizardLabels Wizard default labels.
     */
    public function __construct($settingsContainer, $fieldsOptions, $fields, $updateEndpoint, $defaultWizardLabels)
    {
        $this->settingsContainer   = $settingsContainer;
        $this->fieldsOptions       = $fieldsOptions;
        $this->fields              = $fields;
        $this->updateEndpoint      = $updateEndpoint;
        $this->defaultWizardLabels = $defaultWizardLabels;
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

        $event->setParams([
            'settingsUi' => [
                'preview'            => $this->_getPreviewSettingsFields(),
                'options'            => $this->_iteratorToArrayRecursive($this->fieldsOptions),
                'labels'             => $this->defaultWizardLabels,
                'values'             => $this->_prepareSettingsValues(),
                'updateEndpoint'     => $this->_normalizeArray($this->updateEndpoint),
                'generalSettingsUrl' => $this->_getGeneralSettingsUrl(),
            ],
        ]);
    }

    /**
     * Get website general settings URL.
     *
     * @since [*next-version*]
     *
     * @return string General settings URL.
     */
    protected function _getGeneralSettingsUrl()
    {
        return admin_url('options-general.php');
    }

    /**
     * List of data that can be only previewed. Changing is happening on separate page.
     *
     * @since [*next-version*]
     *
     * @return array Preview fields.
     */
    protected function _getPreviewSettingsFields()
    {
        return [
            'datetimeFormats' => $this->_getWebsiteFormatsPreview(),
        ];
    }

    /**
     * Get website date and time preview.
     *
     * @since [*next-version*]
     *
     * @return string|Stringable Website date and time preview.
     */
    protected function _getWebsiteFormatsPreview()
    {
        $dateFormat = get_option('date_format');
        $timeFormat = get_option('time_format');

        return date($dateFormat) . ' | ' . date($timeFormat);
    }

    /**
     * Prepare settings values for displaying in UI state.
     *
     * @since [*next-version*]
     *
     * @return array Prepared settings values for displaying in UI state.
     */
    protected function _prepareSettingsValues()
    {
        $values = [];
        foreach ($this->fields as $field) {
            $values[$field] = $this->_getSettingValue($field);
        }

        return $values;
    }

    /**
     * Get setting value of field.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $field Field name.
     *
     * @return array|mixed Setting value.
     */
    protected function _getSettingValue($field)
    {
        $field = $this->_normalizeString($field);

        return $this->settingsContainer->get($field);
    }
}
