<?php

namespace RebelCode\EddBookings\Cart\Module;

use Dhii\Config\ConfigFactoryInterface;
use Dhii\Data\Container\ContainerFactoryInterface;
use Dhii\Event\EventFactoryInterface;
use Dhii\Exception\InternalException;
use Dhii\Util\String\StringableInterface as Stringable;
use Psr\Container\ContainerInterface;
use Psr\EventManager\EventManagerInterface;
use RebelCode\Modular\Module\AbstractBaseModule;

/**
 * The EDD Bookings cart module class.
 *
 * @since [*next-version*]
 */
class EddBkCartModule extends AbstractBaseModule
{
    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable         $key                  The module key.
     * @param string[]|Stringable[]     $dependencies         The module dependencies.
     * @param ConfigFactoryInterface    $configFactory        The config factory.
     * @param ContainerFactoryInterface $containerFactory     The container factory.
     * @param ContainerFactoryInterface $compContainerFactory The composite container factory.
     * @param EventManagerInterface     $eventManager         The event manager.
     * @param EventFactoryInterface     $eventFactory         The event factory.
     */
    public function __construct(
        $key,
        $dependencies,
        $configFactory,
        $containerFactory,
        $compContainerFactory,
        $eventManager,
        $eventFactory
    ) {
        $this->_initModule($key, $dependencies, $configFactory, $containerFactory, $compContainerFactory);
        $this->_initModuleEvents($eventManager, $eventFactory);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     *
     * @throws InternalException If an error occurred while reading the config or services files.
     */
    public function setup()
    {
        return $this->_setupContainer(
            $this->_loadPhpConfigFile(EDDBK_CART_MODULE_CONFIG_FILE),
            $this->_loadPhpConfigFile(EDDBK_CART_MODULE_SERVICES_FILE)
        );
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function run(ContainerInterface $c = null)
    {
        if ($c === null) {
            return;
        }

        // Attach the handler that adds bookings to the cart after they complete a `cart` transition
        $this->_attach('after_booking_transition', $c->get('eddbk_add_booking_to_cart_handler'));

        // Attach the handler that deletes bookings when they are removed from the EDD cart
        $this->_attach('edd_pre_remove_from_cart', $c->get('eddbk_remove_booking_from_cart_handler'));

        // Attach the handler that schedules bookings when the payment for its purchase is complete
        $this->_attach('edd_update_payment_status', $c->get('eddbk_submit_booking_on_payment_handler'));

        // Attach the handler that validates bookings in the EDD cart when the cart is submitted for checkout
        $this->_attach('edd_checkout_error_checks', $c->get('eddbk_validate_cart_bookings_handler'));

        // Attach the handler that filters cart item names
        $this->_attach('edd_get_cart_item_name', $c->get('eddbk_filter_cart_item_name_handler'));

        // Attach the handler that filters cart item prices
        $this->_attach('edd_cart_item_price', $c->get('eddbk_filter_cart_item_price_handler'));

        // Attach the handler that renders booking into in the EDD cart
        $this->_attach('edd_checkout_cart_item_title_after', $c->get('eddbk_render_cart_booking_info_handler'));

        // Attach the handler that renders booking information in the purchase confirmation page
        $this->_attach('edd_payment_receipt_after_table', $c->get('eddbk_render_confirmation_bookings_handler'));
    }
}
