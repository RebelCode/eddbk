<?php

namespace RebelCode\Bookings\WordPress\Module;

use Dhii\Data\Container\ContainerFactoryInterface;
use Dhii\Event\EventFactoryInterface;
use Psr\Container\ContainerInterface;
use Psr\EventManager\EventManagerInterface;
use RebelCode\Bookings\WordPress\Module\Handlers\ShortcodeParametersTransformHandler;
use RebelCode\Modular\Module\AbstractBaseModule;
use Dhii\Util\String\StringableInterface as Stringable;

/**
 * Handler for bookings shortcode that will insert client application
 * on page for booking appointments.
 *
 * @since [*next-version*]
 */
class WpBookingsShortcode extends AbstractBaseModule
{
    /**
     * The name of shortcode tag.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $shortcodeTag;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable         $key                  The module key.
     * @param string[]|Stringable[]     $dependencies         The module  dependencies.
     * @param ContainerFactoryInterface $configFactory        The config factory.
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
     */
    public function setup()
    {
        return $this->_setupContainer($this->_loadPhpConfigFile(RC_WP_BOOKINGS_SHORTCODE_MODULE_CONFIG), [
            /*
             * Transform shortcode parameters before sending them to the wizard.
             *
             * @since [*next-version*]
             */
            'eddbk_shortcode_wizard_parameters_transform_handler' => function (ContainerInterface $c) {
                return new ShortcodeParametersTransformHandler(
                    $c->get('eddbk_shortcode/edd_settings/purchase_page'),
                    $c->get('eddbk_services_manager'),
                    $c->get('eddbk_bookings_ui_service_transformer')
                );
            },
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function run(ContainerInterface $c = null)
    {
        $this->shortcodeTag = $c->get('eddbk_shortcode/shortcode_tag');
        $wizardBlockFactory = $c->get('eddbk_wizard_block_factory');
        $settingsContainer  = $c->get('eddbk_settings_container');

        $this->_attach('eddbk_shortcode_wizard_parameters_transform', $c->get('eddbk_shortcode_wizard_parameters_transform_handler'));

        add_shortcode($this->shortcodeTag, function ($attrs) use ($wizardBlockFactory, $settingsContainer) {
            $attrs = $this->_trigger('eddbk_shortcode_parameters', $attrs ? $attrs : [])->getParams();
            $attrs = $this->_trigger('eddbk_shortcode_wizard_parameters_transform', $attrs)->getParams();

            $wizardBlock = $wizardBlockFactory->make([
                'context' => [
                    'config'       => json_encode($attrs),
                    'color'        => $settingsContainer->get('booking_wizard_color'),
                    'weekStartsOn' => $settingsContainer->get('week_starts_on'),
                ],
            ]);

            return $wizardBlock->render();
        });
    }
}
