<?php

namespace RebelCode\EddBookings\Cart\Module;

use ArrayAccess;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerGetPathCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Data\TransitionerInterface;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Iterator\CountIterableCapableTrait;
use Dhii\Iterator\ResolveIteratorCapableTrait;
use Dhii\Storage\Resource\SelectCapableInterface;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Dhii\Validation\Exception\ValidationFailedExceptionInterface;
use Dhii\Validation\ValidatorInterface;
use EDD_Cart;
use Exception as RootException;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use RebelCode\Bookings\BookingFactoryInterface;
use RebelCode\Bookings\BookingInterface;
use RebelCode\Bookings\Exception\CouldNotTransitionExceptionInterface;
use RebelCode\EddBookings\Logic\Module\BookingTransitionInterface as Transition;
use RebelCode\Sessions\ValidatorAwareTrait;
use stdClass;

/**
 * The handler that validates bookings in the EDD cart.
 *
 * @since [*next-version*]
 */
class ValidateCartBookingHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use ValidatorAwareTrait;

    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use ContainerGetPathCapableTrait;

    /* @since [*next-version*] */
    use CountIterableCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIntCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /* @since [*next-version*] */
    use ResolveIteratorCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

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
     * The booking transitioner.
     *
     * @since [*next-version*]
     *
     * @var TransitionerInterface
     */
    protected $transitioner;

    /**
     * The booking factory.
     *
     * @since [*next-version*]
     *
     * @var BookingFactoryInterface
     */
    protected $bookingFactory;

    /**
     * The bookings SELECT resource model.
     *
     * @since [*next-version*]
     *
     * @var SelectCapableInterface
     */
    protected $bookingsSelectRm;

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
     * @param ValidatorInterface                            $validator        The validator for validating bookings.
     * @param BookingFactoryInterface                       $bookingFactory   The factory for creating bookings.
     * @param SelectCapableInterface                        $bookingsSelectRm The bookings SELECT resource model.
     * @param array|stdClass|ArrayAccess|ContainerInterface $cartItemConfig   The cart item data config.
     * @param object                                        $exprBuilder      The expression builder.
     */
    public function __construct(
        EDD_Cart $eddCart,
        ValidatorInterface $validator,
        BookingFactoryInterface $bookingFactory,
        SelectCapableInterface $bookingsSelectRm,
        $exprBuilder,
        $cartItemConfig
    ) {
        $this->_setValidator($validator);

        $this->eddCart          = $eddCart;
        $this->bookingsSelectRm = $bookingsSelectRm;
        $this->exprBuilder      = $exprBuilder;
        $this->cartItemConfig   = $cartItemConfig;
        $this->bookingFactory = $bookingFactory;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        $items = $this->eddCart->get_contents();

        // Get the cart item data keys from the cart item config
        $dataKey       = $this->_containerGetPath($this->cartItemConfig, ['data', 'key']);
        $eddBkKey      = $this->_containerGetPath($this->cartItemConfig, ['data', 'eddbk_key']);
        $bookingIdKey  = $this->_containerGetPath($this->cartItemConfig, ['data', 'booking_id_key']);
        $bookingIdPath = [$dataKey, $eddBkKey, $bookingIdKey];

        // Alias expression builder
        $b = $this->exprBuilder;

        foreach ($items as $_item) {
            try {
                // Get the booking ID
                $bookingId = $this->_containerGetPath($_item, $bookingIdPath);
            } catch (NotFoundExceptionInterface $exception) {
                continue; // If ID not found, cart item does not represent a booking
            }

            // Get the booking that matches the ID
            $bookings = $this->bookingsSelectRm->select(
                $b->eq(
                    $b->ef('booking', 'id'),
                    $b->lit($bookingId)
                )
            );

            // Check that only 1 booking was retrieved
            if ($this->_countIterable($bookings) !== 1) {
                $this->_addEddCheckoutError(
                    'eddbk_invalid_booking_id',
                    $this->__('A cart item has an invalid booking ID.')
                );
            }

            // Get the booking data
            $bookingData = reset($bookings);
            // Create the booking instance
            $booking = $this->bookingFactory->make([
                BookingFactoryInterface::K_DATA => $bookingData,
            ]);

            try {
                // Validate it
                $this->_getValidator()->validate($booking);
            } catch (ValidationFailedExceptionInterface $exception) {
                foreach ($this->_getBookingValidationErrors($exception) as $key => $error) {
                    $this->_addEddCheckoutError($key, $error);
                }
            }
        }
    }

    /**
     * Retrieves the booking validation errors when a booking in the cart fails validation.
     *
     * @since [*next-version*]
     *
     * @param ValidationFailedExceptionInterface $exception The validation exception.
     *
     * @return string[] The validation errors related to bookings, keyed by a unique code.
     */
    protected function _getBookingValidationErrors(ValidationFailedExceptionInterface $exception)
    {
        return [
            'eddbk_unavailable_booking' => $this->__('This booking is not available. Please contact the site administrator for more details'),
        ];
    }

    /**
     * Adds an EDD cart checkout error.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $key     The error key.
     * @param string|Stringable $message The error message.
     */
    protected function _addEddCheckoutError($key, $message)
    {
        \edd_set_error($key, $message);
    }
}
