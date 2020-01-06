<?php

namespace RebelCode\EddBookings\Emails\Module;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use DateTimeZone;
use Dhii\Cache\ContainerInterface as CacheContainerInterface;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeContainerCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Data\StateAwareInterface;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Exception\CreateRuntimeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Iterator\CountIterableCapableTrait;
use Dhii\Iterator\ResolveIteratorCapableTrait;
use Dhii\Output\CreateRendererExceptionCapableTrait;
use Dhii\Output\CreateTemplateRenderExceptionCapableTrait;
use Dhii\Output\TemplateAwareTrait;
use Dhii\Output\TemplateInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception;
use InvalidArgumentException;
use Psr\Container\NotFoundExceptionInterface;
use RebelCode\Bookings\BookingFactoryInterface;
use RebelCode\Bookings\BookingInterface;
use RebelCode\EddBookings\Cart\Module\GetBookingSessionInfoCapableTrait;
use RebelCode\EddBookings\Cart\Module\GetBookingSessionTypeCapableTrait;
use RebelCode\Entity\GetCapableManagerInterface;

/**
 * The template for the bookings email tag.
 *
 * This implementation uses another template for the bookings receipt list.
 *
 * @since [*next-version*]
 */
class BookingsEmailTagTemplate implements TemplateInterface
{
    /* @since [*next-version*] */
    use TemplateAwareTrait;

    /* @since [*next-version*] */
    use GetBookingSessionTypeCapableTrait;

    /* @since [*next-version*] */
    use GetBookingSessionInfoCapableTrait;

    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use CountIterableCapableTrait;

    /* @since [*next-version*] */
    use ResolveIteratorCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIntCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use NormalizeContainerCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateTemplateRenderExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateRendererExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateRuntimeExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

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
     * The format to use to render booking dates and times.
     *
     * @since [*next-version*]
     *
     * @var string|Stringable
     */
    protected $bookingDateTimeFormat;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param TemplateInterface          $layoutTemplate        The layout template.
     * @param BookingFactoryInterface    $bookingFactory        The bookings factory.
     * @param GetCapableManagerInterface $servicesManager       The services manager for retrieving services by ID.
     * @param GetCapableManagerInterface $resourcesManager      The resources manager for retrieving resources by ID.
     * @param CacheContainerInterface    $serviceCache          The cache for service.
     * @param CacheContainerInterface    $resourceCache         The cache for resource.
     * @param Stringable|string          $bookingDateTimeFormat The booking date and time format.
     */
    public function __construct(
        TemplateInterface $layoutTemplate,
        BookingFactoryInterface $bookingFactory,
        GetCapableManagerInterface $servicesManager,
        GetCapableManagerInterface $resourcesManager,
        CacheContainerInterface $serviceCache,
        CacheContainerInterface $resourceCache,
        $bookingDateTimeFormat
    ) {
        $this->_setTemplate($layoutTemplate);

        $this->bookingFactory        = $bookingFactory;
        $this->servicesManager       = $servicesManager;
        $this->resourcesManager      = $resourcesManager;
        $this->servicesCache         = $serviceCache;
        $this->resourcesCache        = $resourceCache;
        $this->bookingDateTimeFormat = $bookingDateTimeFormat;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function render($context = null)
    {
        try {
            $bookings = $this->_containerGet($context, 'bookings');
        } catch (NotFoundExceptionInterface $exception) {
            throw $this->_createTemplateRenderException(
                $this->__('Bookings not found in render context'), null, null, $this, $context
            );
        }

        try {
            $this->_normalizeIterable($bookings);
        } catch (InvalidArgumentException $exception) {
            throw $this->_createTemplateRenderException(
                $this->__('Invalid bookings in render context - not an iterable'), null, null, $this, $context
            );
        }

        $bookingsList = '';
        foreach ($bookings as $_bookingData) {
            $_booking = $this->bookingFactory->make([
                BookingFactoryInterface::K_DATA => $_bookingData,
            ]);

            $bookingsList .= $this->_renderBooking($_booking);
        }

        return $this->_getTemplate()->render([
            'bookings' => $bookingsList,
        ]);
    }

    /**
     * Renders a single booking.
     *
     * @since [*next-version*]
     *
     * @param BookingInterface|StateAwareInterface $booking The booking. Must also be state aware.
     *
     * @return string|Stringable The rendered result.
     */
    protected function _renderBooking($booking)
    {
        $serviceId = $booking->getState()->get('service_id');

        // Create date time helper instance for the booking start time
        $startTs = $booking->getStart();
        $startDt = Carbon::createFromTimestampUTC($startTs);

        // Shift to client timezone, if available
        try {
            $clientTz = $booking->getState()->get('client_tz');
            if (!empty($clientTz)) {
                $startDt->setTimezone(new DateTimeZone($clientTz));
            }
        } catch (NotFoundExceptionInterface $nfException) {
            // booking has no client timezone
        } catch (Exception $exception) {
            // failed to parse timezone name
        }

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

        try {
            // Get the service
            $service = $this->servicesManager->get($serviceId);
        } catch (NotFoundExceptionInterface $exception) {
            throw $this->_createRuntimeException(
                $this->__('Failed to retrieve the service for the booking'), null, null
            );
        }

        $serviceName = $this->_containerGet($service, 'name');

        $session = sprintf('<b>%s (%s)</b> %s', $serviceName, $sessionLabel, $resourcesText);
        $dateStr = $startDt->format($this->bookingDateTimeFormat);

        return sprintf('<li>%s - <i>%s</i></li>', $session, $dateStr);
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
    protected function _getServicesManager()
    {
        return $this->servicesManager;
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
}
