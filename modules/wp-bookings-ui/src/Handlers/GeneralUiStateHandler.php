<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers;

use Dhii\Collection\MapInterface;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Event\EventFactoryInterface;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Psr\Container\ContainerInterface;
use Psr\EventManager\EventInterface;
use Psr\EventManager\EventManagerInterface;
use RebelCode\Bookings\WordPress\Module\IteratorToArrayRecursiveCapableTrait;
use RebelCode\Modular\Events\EventsConsumerTrait;
use stdClass;
use Traversable;

/**
 * Handler for UI state config. In the config we pass data needed by UI application to
 * work correctly. Such as site timezone and formats configuration.
 *
 * @since [*next-version*]
 */
class GeneralUiStateHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use EventsConsumerTrait;

    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use GetVisibleStatusesCapable;

    /* @since [*next-version*] */
    use ContainerHasCapableTrait;

    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

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
     * List of statuses keys available in application.
     *
     * @since [*next-version*]
     *
     * @var array|Traversable|stdClass
     */
    protected $statuses;

    /**
     * Map of known statuses keys translations.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|MapInterface
     */
    protected $statusesLabels;

    /**
     * Currency config of application.
     *
     * @since [*next-version*]
     *
     * @var array|Traversable|stdClass
     */
    protected $currencyConfig;

    /**
     * Configuration of application formats to format some data.
     * For example, datetime formats.
     *
     * @since [*next-version*]
     *
     * @var array|Traversable|stdClass
     */
    protected $formatsConfig;

    /**
     * List of links to booking related entities (clients, services).
     *
     * @since [*next-version*]
     *
     * @var array|Traversable|stdClass
     */
    protected $linksConfig;

    /**
     * List of UI action pipes configuration.
     *
     * @since [*next-version*]
     *
     * @var array|Traversable|stdClass
     */
    protected $uiActionsConfig;

    /**
     * List of validators configuration.
     *
     * @since [*next-version*]
     *
     * @var array|Traversable|stdClass
     */
    protected $validatorsConfig;

    /*
     * The WP Rest nonce.
     *
     * @since [*next-version*]
     *
     * @var string|Stringable
     */
    protected $wpRestNonce;

    /**
     * GeneralUiStateHandler constructor.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface          $settingsContainer Settings container.
     * @param array|Traversable|stdClass  $statuses          List of statuses key in application.
     * @param array|stdClass|MapInterface $statusesLabels    Map of known status keys to statuses labels.
     * @param array|Traversable|stdClass  $currencyConfig    Currency config of application.
     * @param array|Traversable|stdClass  $formatsConfig     List of available data formats in application.
     * @param array|Traversable|stdClass  $linksConfig       List of links to booking related entities (clients, services).
     * @param array|Traversable|stdClass  $uiActionsConfig   List of UI action pipes configuration.
     * @param array|Traversable|stdClass  $validatorsConfig  List of validators configuration.
     * @param string|Stringable           $wpRestNonce       The WP Rest nonce.
     * @param EventManagerInterface       $eventManager      The event manager.
     * @param EventFactoryInterface       $eventFactory      The event factory.
     */
    public function __construct(
        $settingsContainer,
        $statuses,
        $statusesLabels,
        $currencyConfig,
        $formatsConfig,
        $linksConfig,
        $uiActionsConfig,
        $validatorsConfig,
        $wpRestNonce,
        $eventManager,
        $eventFactory
    ) {
        $this->settingsContainer = $settingsContainer;

        $this->statuses         = $statuses;
        $this->statusesLabels   = $statusesLabels;
        $this->currencyConfig   = $currencyConfig;
        $this->formatsConfig    = $formatsConfig;
        $this->linksConfig      = $linksConfig;
        $this->uiActionsConfig  = $uiActionsConfig;
        $this->validatorsConfig = $validatorsConfig;
        $this->wpRestNonce      = $wpRestNonce;

        $this->_setEventManager($eventManager);
        $this->_setEventFactory($eventFactory);
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
            /*
             * Get wp rest nonce string.
             */
            'wp_rest_nonce' => $this->_normalizeString($this->wpRestNonce),

            /*
             * All available booking statuses in application.
             */
            'statuses' => $this->_getTranslatedStatuses($this->statuses, $this->statusesLabels),

            'config' => $this->_getUiConfig($this->currencyConfig, $this->formatsConfig, $this->linksConfig, $this->uiActionsConfig, $this->validatorsConfig),
        ]);
    }

    /**
     * Get config for UI application.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable|stdClass $currencyConfig   Currency config of application.
     * @param array|Traversable|stdClass $formatsConfig    List of available data formats in application.
     * @param array|Traversable|stdClass $linksConfig      List of links to booking related entities (clients, services).
     * @param array|Traversable|stdClass $uiActionsConfig  List of UI action pipes configuration.
     * @param array|Traversable|stdClass $validatorsConfig List of validators configuration.
     *
     * @return array UI configuration.
     */
    protected function _getUiConfig($currencyConfig, $formatsConfig, $linksConfig, $uiActionsConfig, $validatorsConfig)
    {
        return [
            'timezone'   => $this->_getWebsiteTimezone(),
            'currency'   => $this->_prepareCurrencyConfig($currencyConfig),
            'formats'    => $this->_prepareFormatsConfig($formatsConfig),
            'links'      => $this->_prepareLinksConfig($linksConfig),
            'uiActions'  => $this->_iteratorToArrayRecursive($uiActionsConfig),
            'validators' => $this->_iteratorToArrayRecursive($validatorsConfig),

            'weekStartsOn'          => $this->settingsContainer->get('week_starts_on'),
            'defaultCalendarView'   => $this->settingsContainer->get('default_calendar_view'),
            'bookingStatusesColors' => $this->settingsContainer->get('booking_statuses_colors'),
        ];
    }

    /**
     * Get all translated statuses.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable|stdClass        $statuses       List of statuses
     * @param array|ContainerInterface|stdClass $statusesLabels Map of statuses keys to status labels
     *
     * @return array Map of statuses codes and translations.
     */
    protected function _getTranslatedStatuses($statuses, $statusesLabels)
    {
        $translatedStatuses = [];

        $statuses = $this->_getVisibleStatuses($statuses);

        foreach ($statuses as $status) {
            $statusLabel                 = $this->_containerHas($statusesLabels, $status) ? $this->_containerGet($statusesLabels, $status) : $status;
            $translatedStatuses[$status] = $this->__($statusLabel);
        }

        return $translatedStatuses;
    }

    /**
     * Get website timezone.
     *
     * @since [*next-version*]
     *
     * @return string Timezone in `America/Indianapolis` form.
     */
    protected function _getWebsiteTimezone()
    {
        $currentOffset = get_option('gmt_offset');
        $tzstring      = get_option('timezone_string');

        // Remove old Etc mappings. Fallback to gmt_offset.
        if (false !== strpos($tzstring, 'Etc/GMT')) {
            $tzstring = '';
        }

        if (empty($tzstring)) {
            if (0 == $currentOffset) {
                $tzstring = 'UTC+0';
            } elseif ($currentOffset < 0) {
                $tzstring = 'UTC' . $currentOffset;
            } else {
                $tzstring = 'UTC+' . $currentOffset;
            }
        }

        return $tzstring;
    }

    /**
     * Prepare application's currency configuration for state.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable|stdClass $currencyConfig Currency config of application.
     *
     * @return array Application currency config.
     */
    protected function _prepareCurrencyConfig($currencyConfig)
    {
        return $this->_normalizeArray($currencyConfig);
    }

    /**
     * Prepare formats config for using in the UI.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable|stdClass $formatsConfig List of available data formats in application.
     *
     * @return array Prepared data formats configuration.
     */
    protected function _prepareFormatsConfig($formatsConfig)
    {
        $preparedFormatsConfig = [];
        foreach ($formatsConfig as $key => $config) {
            $preparedFormatsConfig[$key] = $this->_normalizeArray($config);
        }

        return $preparedFormatsConfig;
    }

    /**
     * Prepare list of links to booking related entities for using in the UI.
     *
     * @since [*next-version*]
     *
     * @param array|Traversable|stdClass $linksConfig List of links to booking related entities (clients, services).
     *
     * @return array Prepared links list.
     */
    protected function _prepareLinksConfig($linksConfig)
    {
        $preparedLinksConfig = [];
        foreach ($linksConfig as $key => $link) {
            $preparedLinksConfig[$key] = admin_url($link);
        }

        return $preparedLinksConfig;
    }
}
