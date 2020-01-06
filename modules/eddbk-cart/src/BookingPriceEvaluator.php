<?php

namespace RebelCode\EddBookings\Cart;

use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeContainerCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Data\StateAwareInterface;
use Dhii\Data\ValueAwareInterface;
use Dhii\Evaluable\EvaluableInterface;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Exception\CreateRuntimeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Iterator\CountIterableCapableTrait;
use Dhii\Iterator\ResolveIteratorCapableTrait;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Psr\Container\NotFoundExceptionInterface;
use RebelCode\Bookings\BookingInterface;
use RebelCode\Entity\GetCapableManagerInterface;

/**
 * Evaluates booking prices.
 *
 * @since [*next-version*]
 */
class BookingPriceEvaluator implements EvaluableInterface
{
    /* @since [*next-version*] */
    use CountIterableCapableTrait;

    /* @since [*next-version*] */
    use ResolveIteratorCapableTrait;

    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIntCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use NormalizeContainerCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

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
     * The services manager for retrieving services by ID.
     *
     * @since [*next-version*]
     *
     * @var GetCapableManagerInterface
     */
    protected $servicesManager;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param GetCapableManagerInterface $servicesManager The services manager for retrieving services by ID.
     */
    public function __construct(GetCapableManagerInterface $servicesManager)
    {
        $this->servicesManager = $servicesManager;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function evaluate(ValueAwareInterface $ctx = null)
    {
        $booking = $ctx->getValue();

        if (!($booking instanceof StateAwareInterface) || !($booking instanceof BookingInterface)) {
            throw $this->_createRuntimeException(
                $this->__('Cannot determine price - argument is not a state-aware booking instance'), null, null
            );
        }

        $bookingDuration  = $this->_normalizeInt($booking->getDuration());
        $bookingResources = $this->_normalizeArray($booking->getResourceIds());

        try {
            $serviceId = $booking->getState()->get('service_id');
            $service   = $this->servicesManager->get($serviceId);
        } catch (NotFoundExceptionInterface $exception) {
            throw $this->_createRuntimeException(
                $this->__('Cannot determine booking price - the booked service does not exist'), null, $exception
            );
        }

        $sessionTypes = $this->_containerGet($service, 'session_types');
        $sessionTypes = $this->_normalizeIterable($sessionTypes);

        foreach ($sessionTypes as $_sessionType) {
            $_data          = $this->_containerGet($_sessionType, 'data');
            $_duration      = $this->_normalizeInt($this->_containerGet($_data, 'duration'));
            $_resources     = $this->_containerGet($_data, 'resources');
            $_rIntersection = array_intersect($_resources, $bookingResources);

            if ($bookingDuration === $_duration && !empty($_rIntersection)) {
                return $this->_containerGet($_sessionType, 'price');
            }
        }

        throw $this->_createRuntimeException(
            $this->__('Cannot determine booking price - booking does not match any session type'), null, null
        );
    }
}
