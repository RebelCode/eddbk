<?php

namespace RebelCode\EddBookings\Cart\Module;

use ArrayAccess;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerGetPathCapableTrait;
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
use Dhii\Iterator\CountIterableCapableTrait;
use Dhii\Iterator\ResolveIteratorCapableTrait;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use EDD_Cart;
use InvalidArgumentException;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventManager\EventInterface;
use RebelCode\Bookings\BookingInterface;
use RebelCode\EddBookings\Logic\Module\BookingTransitionInterface as Transition;
use RebelCode\Entity\GetCapableManagerInterface;
use RuntimeException;
use stdClass;

/**
 * The handler for adding bookings to the EDD cart.
 *
 * @since [*next-version*]
 */
class AddBookingToCartHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use ContainerGetPathCapableTrait;

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
    use NormalizeContainerCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

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
     * The EDD cart instance.
     *
     * @since [*next-version*]
     *
     * @var EDD_Cart
     */
    protected $eddCart;

    /**
     * The cart item data config.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|ArrayAccess|ContainerInterface
     */
    protected $cartItemConfig;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param GetCapableManagerInterface $servicesManager The services manager for retrieving services by ID.
     * @param EDD_Cart                   $eddCart         The EDD cart instance.
     * @param Stringable|string          $cartItemConfig  The cart item data config.
     */
    public function __construct(GetCapableManagerInterface $servicesManager, EDD_Cart $eddCart, $cartItemConfig)
    {
        $this->servicesManager = $servicesManager;
        $this->eddCart         = $eddCart;
        $this->cartItemConfig  = $cartItemConfig;
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

        // Only continue if the booking is transitioning to the cart
        if ($event->getParam('transition') !== Transition::TRANSITION_CART) {
            return;
        }

        // Get and validate the booking from the event
        $booking = $event->getParam('booking');

        // Booking must implement both of these interfaces
        if (!($booking instanceof BookingInterface) || !($booking instanceof StateAwareInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Booking in event is not a valid state-aware instance'), null, null, $booking
            );
        }

        $this->_addToCart($booking);
    }

    /**
     * Adds the booking to the EDD cart.
     *
     * @since [*next-version*]
     *
     * @param BookingInterface|StateAwareInterface $booking The booking.
     *
     * @throws InvalidArgumentException    If the booking is not a valid container.
     * @throws NotFoundExceptionInterface  If the service ID was not be found in the booking data.
     * @throws ContainerExceptionInterface If an error occurred while reading the booking data.
     * @throws RuntimeException            If the service for which the booking was made does not exist.
     */
    protected function _addToCart($booking)
    {
        $state = $booking->getState();

        $bookingId  = $state->get('id');
        $serviceId  = $state->get('service_id');
        $bkDuration = $booking->getDuration();

        // Get the cart item data keys from the cart item config
        $eddBkKey     = $this->_containerGetPath($this->cartItemConfig, ['data', 'eddbk_key']);
        $bookingIdKey = $this->_containerGetPath($this->cartItemConfig, ['data', 'booking_id_key']);
        $priceIdKey   = $this->_containerGetPath($this->cartItemConfig, ['data', 'price_id_key']);

        try {
            $service = $this->servicesManager->get($serviceId);
        } catch (NotFoundExceptionInterface $exception) {
            throw $this->_createRuntimeException(
                $this->__('Service with ID "%s" does not exist', [$serviceId]), null, $exception
            );
        }

        // Find the price ID
        $types   = $this->_containerGet($service, 'session_types');
        $priceId = null;
        foreach ($types as $_idx => $_type) {
            $_data     = $this->_containerGet($_type, 'data');
            $_duration = (int) $this->_containerGet($_data, 'duration');

            if ($_duration === $bkDuration) {
                $priceId = $_idx;
                break;
            }
        }

        // Create the cart item data
        $data = [
            $priceIdKey => $priceId,
            $eddBkKey   => [
                $bookingIdKey => $bookingId,
            ],
        ];

        // Add the service (EDD Download) to the cart with the additional EDD Bookings data
        $this->eddCart->add($serviceId, $data);
    }
}
