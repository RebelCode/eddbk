<?php

namespace RebelCode\EddBookings\Cart\Module;

use ArrayAccess;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerGetPathCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Data\StateAwareFactoryInterface;
use Dhii\Data\StateAwareInterface;
use Dhii\Evaluable\EvaluableInterface;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Factory\FactoryAwareTrait;
use Dhii\Factory\FactoryInterface;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Iterator\CountIterableCapableTrait;
use Dhii\Iterator\ResolveIteratorCapableTrait;
use Dhii\Storage\Resource\SelectCapableInterface;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Exception;
use InvalidArgumentException;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\EventManager\EventInterface;
use stdClass;

/**
 * The handler that filters a cart item's price to the matching booking session type price, if it is a cart booking.
 *
 * @since [*next-version*]
 */
class FilterCartItemPriceHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use FactoryAwareTrait {
        _getFactory as _getBookingValueAwareFactory;
        _setFactory as _setBookingValueAwareFactory;
    }

    /* @since [*next-version*] */
    use ContainerGetPathCapableTrait;

    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIntCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /* @since [*next-version*] */
    use CountIterableCapableTrait;

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
     * The bookings SELECT resource model.
     *
     * @since [*next-version*]
     *
     * @var SelectCapableInterface
     */
    protected $bookingsSelectRm;

    /**
     * The factory for creating state-aware bookings.
     *
     * @since [*next-version*]
     *
     * @var StateAwareFactoryInterface
     */
    protected $stateAwareFactory;

    /**
     * The evaluable instance that evaluates a booking's price.
     *
     * @since [*next-version*]
     *
     * @var EvaluableInterface|null
     */
    protected $priceEvaluator;

    /**
     * The cart item data config.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|ArrayAccess|ContainerInterface
     */
    protected $cartItemConfig;

    /**
     * The expression builder.
     *
     * @since [*next-version*]
     *
     * @var object
     */
    protected $exprBuilder;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param SelectCapableInterface                        $bookingsSelectRm  The bookings SELECT resource model.
     * @param StateAwareFactoryInterface                    $stateAwareFactory The factory for creating state-aware
     *                                                                         bookings.
     * @param EvaluableInterface|null                       $priceEvaluator    The booking price evaluator.
     * @param FactoryInterface                              $valueAwareFactory The value aware factory.
     * @param object                                        $exprBuilder       The expression builder.
     * @param array|ArrayAccess|ContainerInterface|stdClass $cartItemConfig    The cart item configuration.
     */
    public function __construct(
        SelectCapableInterface $bookingsSelectRm,
        StateAwareFactoryInterface $stateAwareFactory,
        EvaluableInterface $priceEvaluator,
        FactoryInterface $valueAwareFactory,
        $exprBuilder,
        $cartItemConfig
    ) {
        $this->_setPriceEvaluator($priceEvaluator);
        $this->_setBookingValueAwareFactory($valueAwareFactory);

        $this->bookingsSelectRm  = $bookingsSelectRm;
        $this->exprBuilder       = $exprBuilder;
        $this->cartItemConfig    = $cartItemConfig;
        $this->stateAwareFactory = $stateAwareFactory;
    }

    /**
     * Retrieves the booking price evaluator.
     *
     * @since [*next-version*]
     *
     * @return EvaluableInterface|null The price evaluator instance, if any.
     */
    protected function _getPriceEvaluator()
    {
        return $this->priceEvaluator;
    }

    /**
     * Sets the booking price evaluator.
     *
     * @since [*next-version*]
     *
     * @param EvaluableInterface|null $priceEvaluator The price evaluator instance, if any.
     *
     * @throws InvalidArgumentException If the argument is not an evaluable instance.
     */
    protected function _setPriceEvaluator($priceEvaluator)
    {
        if ($priceEvaluator !== null && !($priceEvaluator instanceof EvaluableInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not an evaluable instance'), null, null, $priceEvaluator
            );
        }

        $this->priceEvaluator = $priceEvaluator;
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

        $options = $event->getParam(2);

        $eddBkKey      = $this->_containerGetPath($this->cartItemConfig, ['data', 'eddbk_key']);
        $bookingIdKey  = $this->_containerGetPath($this->cartItemConfig, ['data', 'booking_id_key']);
        $bookingIdPath = [$eddBkKey, $bookingIdKey];

        try {
            $bookingId = $this->_containerGetPath($options, $bookingIdPath);
        } catch (NotFoundExceptionInterface $exception) {
            return;
        }

        // Alias expression builder
        $b = $this->exprBuilder;

        // Fetch the corresponding booking from storage
        $condition = $b->eq($b->var('id'), $b->lit($bookingId));
        $bookings  = $this->bookingsSelectRm->select($condition);
        // Stop if no booking was found, or if multiple bookings matched the ID (for some reason?)
        if ($this->_countIterable($bookings) !== 1) {
            return;
        }
        // Get the booking data
        $bookingData = reset($bookings);
        // Create the booking
        $booking = $this->stateAwareFactory->make([
            StateAwareFactoryInterface::K_DATA => $bookingData,
        ]);

        try {
            $price = $this->_evaluateBookingPrice($booking);
        } catch (Exception $exception) {
            return;
        }

        $event->setParams([0 => $price] + $event->getParams());
    }

    /**
     * Evaluates the price of the given booking.
     *
     * @since [*next-version*]
     *
     * @param StateAwareInterface $booking The booking instance.
     *
     * @return int|float|string|Stringable The booking price.
     */
    protected function _evaluateBookingPrice($booking)
    {
        $context = $this->_getBookingValueAwareFactory()->make(['booking' => $booking]);
        $price   = $this->_getPriceEvaluator()->evaluate($context);

        return $price;
    }
}
