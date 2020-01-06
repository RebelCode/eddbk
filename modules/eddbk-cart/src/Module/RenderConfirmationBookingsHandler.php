<?php

namespace RebelCode\EddBookings\Cart\Module;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Dhii\Cache\ContainerInterface as CacheContainerInterface;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeContainerCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Data\StateAwareInterface;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Exception\CreateRuntimeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Output\TemplateAwareTrait;
use Dhii\Output\TemplateInterface;
use Dhii\Storage\Resource\SelectCapableInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Psr\EventManager\EventInterface;
use RebelCode\Bookings\BookingFactoryInterface;
use RebelCode\Bookings\BookingInterface;
use RebelCode\Entity\GetCapableManagerInterface;
use stdClass;
use Traversable;

/**
 * The handler that renders the bookings information table in the EDD purchase confirmation page.
 *
 * @since [*next-version*]
 */
class RenderConfirmationBookingsHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use TemplateAwareTrait;

    /* @since [*next-version*] */
    use GetBookingDisplayTimezoneCapableTrait;

    /* @since [*next-version*] */
    use GetBookingSessionInfoCapableTrait;

    /* @since [*next-version*] */
    use GetBookingSessionTypeCapableTrait;

    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use ContainerHasCapableTrait;

    /* @since [*next-version*] */
    use NormalizeTimezoneNameCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use NormalizeContainerCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateRuntimeExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The template for rendering booking rows.
     *
     * @since [*next-version*]
     *
     * @var TemplateInterface
     */
    protected $bookingRowTemplate;

    /**
     * The bookings SELECT resource model.
     *
     * @since [*next-version*]
     *
     * @var SelectCapableInterface
     */
    protected $bookingsSelectRm;

    /**
     * The factory for creating booking instances.
     *
     * @since [*next-version*]
     *
     * @var BookingFactoryInterface
     */
    protected $bookingFactory;

    /**
     * The services manager for retrieving services by ID.
     *
     * @since [*next-version*]
     *
     * @var GetCapableManagerInterface
     */
    protected $servicesManager;

    /**
     * The resources manager for retrieving resources by ID.
     *
     * @since [*next-version*]
     *
     * @var GetCapableManagerInterface
     */
    protected $resourcesManager;

    /**
     * The services cache.
     *
     * @since [*next-version*]
     *
     * @var CacheContainerInterface
     */
    protected $servicesCache;

    /**
     * The resources cache.
     *
     * @since [*next-version*]
     *
     * @var CacheContainerInterface
     */
    protected $resourcesCache;

    /**
     * The expression builder.
     *
     * @since [*next-version*]
     *
     * @var object
     */
    protected $exprBuilder;

    /**
     * The booking date and time format.
     *
     * @since [*next-version*]
     *
     * @var string|Stringable
     */
    protected $bookingFormat;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param TemplateInterface          $bookingTableTemplate The bookings table template.
     * @param TemplateInterface          $bookingRowTemplate   The bookings table row template.
     * @param SelectCapableInterface     $bookingsSelectRm     The bookings SELECT resource model.
     * @param BookingFactoryInterface    $bookingFactory       The bookings factory.
     * @param GetCapableManagerInterface $servicesManager      The services manager for retrieving services by ID.
     * @param GetCapableManagerInterface $resourcesManager     The resources manager for retrieving resources by ID.
     * @param CacheContainerInterface    $serviceCache         The cache for service.
     * @param CacheContainerInterface    $resourceCache        The cache for resource.
     * @param object                     $exprBuilder          The expression builder.
     * @param string|Stringable          $bookingFormat        The bookings date and time format.
     * @param string|Stringable|null     $fallbackTz           The fallback timezone name.
     */
    public function __construct(
        TemplateInterface $bookingTableTemplate,
        TemplateInterface $bookingRowTemplate,
        SelectCapableInterface $bookingsSelectRm,
        BookingFactoryInterface $bookingFactory,
        GetCapableManagerInterface $servicesManager,
        GetCapableManagerInterface $resourcesManager,
        CacheContainerInterface $serviceCache,
        CacheContainerInterface $resourceCache,
        $exprBuilder,
        $bookingFormat,
        $fallbackTz
    ) {
        $this->_setTemplate($bookingTableTemplate);
        $this->_setFallbackTz($fallbackTz);

        $this->bookingRowTemplate = $bookingRowTemplate;
        $this->bookingsSelectRm   = $bookingsSelectRm;
        $this->bookingFactory     = $bookingFactory;
        $this->servicesManager    = $servicesManager;
        $this->resourcesManager   = $resourcesManager;
        $this->servicesCache      = $serviceCache;
        $this->resourcesCache     = $resourceCache;
        $this->exprBuilder        = $exprBuilder;
        $this->bookingFormat      = $bookingFormat;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        $event = func_get_arg(0);

        if (!($event instanceof EventInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not an event instance'), null, null, $event
            );
        }

        $payment   = $event->getParam(0);
        $paymentId = $payment->ID;

        // Alias expression builder
        $b = $this->exprBuilder;

        // Fetch the corresponding booking from storage
        $condition = $b->eq($b->var('payment_id'), $b->lit($paymentId));
        $bookings  = $this->bookingsSelectRm->select($condition);

        echo $this->_renderBookingsTable($bookings);
    }

    /**
     * Renders the bookings table.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $bookings The list of bookings to render in the table, where each element may
     *                                             be any of the following types:
     *                                             * array
     *                                             * stdClass
     *                                             * ArrayAccess
     *                                             * ContainerInterface
     *
     * @return string|Stringable The render output.
     */
    protected function _renderBookingsTable($bookings)
    {
        $rows = '';
        foreach ($bookings as $_bookingData) {
            $_booking = ($_bookingData instanceof BookingInterface)
                ? $_bookingData
                : $this->bookingFactory->make([BookingFactoryInterface::K_DATA => $_bookingData]);

            $rows .= $this->_renderBookingRow($_booking);
        }

        return $this->_getTemplate()->render([
            'table_heading'        => $this->__('Bookings'),
            'service_column'       => $this->__('Service'),
            'booking_start_column' => $this->__('Date'),
            'booking_rows'         => $rows,
        ]);
    }

    /**
     * Renders a booking row.
     *
     * @since [*next-version*]
     *
     * @param BookingInterface|StateAwareInterface $booking The booking.
     *
     * @return string|Stringable The rendered booking row.
     */
    protected function _renderBookingRow(BookingInterface $booking)
    {
        $bookingData = $booking->getState();

        $timezone = $this->_getDisplayTimezone($bookingData);

        // Get timestamps from booking
        $startTs = $booking->getStart();
        $endTs   = $booking->getEnd();

        // Create date time helper instances
        $startDt = Carbon::createFromTimestampUTC($startTs);
        $endDt   = Carbon::createFromTimestampUTC($endTs);

        // Shift to client timezone, if available
        if ($timezone !== null) {
            $startDt->setTimezone($timezone);
            $endDt->setTimezone($timezone);
        }

        // Format times to strings
        $startStr = $startDt->format($this->bookingFormat);
        $endStr   = $endDt->format($this->bookingFormat);

        // Get the ID and name of the booking's service
        $serviceId   = $this->_containerGet($bookingData, 'service_id');
        $serviceName = $this->_getServiceName($serviceId);
        // Get the matching session's info
        $sessionInfo = $this->_getBookingSessionInfo($booking);
        // Append the session label to the service name if it exists
        $sessionLabel = $this->_containerGet($sessionInfo, 'session_label');
        if (empty($sessionLabel)) {
            $sessionLabel = CarbonInterval::seconds($booking->getDuration())->cascade()->forHumans();
        }
        // Prepare the resources text
        $resources     = $this->_containerGet($sessionInfo, 'resource_names');
        $resourcesText = !empty($resources)
            ? sprintf('%s %s', $this->__('with'), implode(', ', $resources))
            : '';

        return $this->bookingRowTemplate->render([
            'service_name'      => sprintf('%s – %s', $serviceName, $sessionLabel),
            'resources'         => $resourcesText,
            'start_text'        => $startStr,
            'end_text'          => $endStr,
            'timezone'          => $this->_normalizeTimezoneName($timezone->getName()),
        ]);
    }

    /**
     * Retrieves the name of the service.
     *
     * @since [*next-version*]
     *
     * @param int|string|Stringable $serviceId The ID of the service.
     *
     * @return string|Stringable The name of the service.
     */
    protected function _getServiceName($serviceId)
    {
        $service = $this->servicesCache->get($serviceId, function ($serviceId) {
            return $this->servicesManager->get($serviceId);
        });

        return $this->_containerGet($service, 'name');
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getResourcesManager()
    {
        return $this->resourcesManager;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getResourceCache()
    {
        return $this->resourcesCache;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getServiceCache()
    {
        return $this->servicesCache;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getServicesManager()
    {
        return $this->servicesManager;
    }
}
