<?php

namespace RebelCode\EddBookings\Cart\Module;

use ArrayAccess;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerGetPathCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeContainerCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Iterator\CountIterableCapableTrait;
use Dhii\Iterator\ResolveIteratorCapableTrait;
use Dhii\Storage\Resource\DeleteCapableInterface;
use Dhii\Storage\Resource\SelectCapableInterface;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use EDD_Cart;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventManager\EventInterface;
use RebelCode\EddBookings\Logic\Module\BookingStatusInterface as Status;
use stdClass;

/**
 * The handler that removes bookings from the database when they are removed from the EDD cart.
 *
 * @since [*next-version*]
 */
class RemoveBookingFromCartHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use CountIterableCapableTrait;

    /* @since [*next-version*] */
    use ResolveIteratorCapableTrait;

    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use ContainerGetPathCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIntCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

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
    use StringTranslatingTrait;

    /**
     * The EDD cart instance.
     *
     * @since [*next-version*]
     *
     * @var EDD_Cart
     */
    protected $eddCart;

    /**
     * The bookings SELECT resource model.
     *
     * @since [*next-version*]
     *
     * @var SelectCapableInterface
     */
    protected $bookingsSelectRm;

    /**
     * The bookings DELETE resource model.
     *
     * @since [*next-version*]
     *
     * @var DeleteCapableInterface
     */
    protected $bookingsDeleteRm;

    /**
     * The expression builder.
     *
     * @since [*next-version*]
     *
     * @var object
     */
    protected $exprBuilder;

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
     * @param EDD_Cart                                      $eddCart          The EDD cart instance.
     * @param SelectCapableInterface                        $bookingsSelectRm The bookings SELECT resource model.
     * @param DeleteCapableInterface                        $bookingsDeleteRm The bookings DELETE resource model.
     * @param object                                        $exprBuilder      The expression builder.
     * @param array|stdClass|ArrayAccess|ContainerInterface $cartItemConfig   The cart item data config.
     */
    public function __construct(
        EDD_Cart $eddCart,
        SelectCapableInterface $bookingsSelectRm,
        DeleteCapableInterface $bookingsDeleteRm,
        $exprBuilder,
        $cartItemConfig
    ) {
        $this->eddCart          = $eddCart;
        $this->bookingsSelectRm = $bookingsSelectRm;
        $this->bookingsDeleteRm = $bookingsDeleteRm;
        $this->exprBuilder      = $exprBuilder;
        $this->cartItemConfig   = $cartItemConfig;
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

        // Get the cart index from the event (provided by the EDD hook) and normalize it
        $cartIndex = $event->getParam(0);
        $cartIndex = $this->_normalizeInt($cartIndex);
        // Get the cart item data keys from the cart item config
        $dataKey      = $this->_containerGetPath($this->cartItemConfig, ['data', 'key']);
        $eddBkKey     = $this->_containerGetPath($this->cartItemConfig, ['data', 'eddbk_key']);
        $bookingIdKey = $this->_containerGetPath($this->cartItemConfig, ['data', 'booking_id_key']);
        // The booking ID path in the cart item
        $bookingIdPath = [$cartIndex, $dataKey, $eddBkKey, $bookingIdKey];

        // Get all the cart items - EDD does not provide a single cart item getter!
        $cartItems = $this->eddCart->get_contents();

        try {
            // Get the booking ID from the cart item at the index
            $bookingId = $this->_containerGetPath($cartItems, $bookingIdPath);
        } catch (NotFoundExceptionInterface $exception) {
            // Item does not have a booking ID.
            return;
        }

        $this->_removeBookingFromCart($bookingId);
    }

    /**
     * Removes the booking with the given ID from the EDD cart.
     *
     * @since [*next-version*]
     *
     * @param int|string|Stringable $bookingId The booking ID.
     */
    protected function _removeBookingFromCart($bookingId)
    {
        // Build the condition for selecting the booking with the booking ID
        $b = $this->exprBuilder;
        $c = $b->eq($b->var('id'), $b->lit($bookingId));

        // Get the bookings that match - only 1 should match
        $bookings = $this->bookingsSelectRm->select($c);
        if ($this->_countIterable($bookings) !== 1) {
            return;
        }

        // If the booking has a cart status, delete it
        $booking = reset($bookings);

        if ($this->_containerGet($booking, 'status') === Status::STATUS_IN_CART) {
            $this->bookingsDeleteRm->delete($c);
        }
    }
}
