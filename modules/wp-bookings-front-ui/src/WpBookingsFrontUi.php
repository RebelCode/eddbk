<?php

namespace RebelCode\Bookings\WordPress\Module;

use Dhii\Data\Container\ContainerFactoryInterface;
use Dhii\Event\EventFactoryInterface;
use Dhii\Output\TemplateInterface;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Psr\Container\ContainerInterface;
use Psr\EventManager\EventInterface;
use Psr\EventManager\EventManagerInterface;
use RebelCode\Bookings\WordPress\Module\Handlers\AssetsEnqueueHandler;
use RebelCode\Bookings\WordPress\Module\Handlers\StateEnqueueHandler;
use RebelCode\Modular\Module\AbstractBaseModule;
use Dhii\Util\String\StringableInterface as Stringable;
use Dhii\Output\PlaceholderTemplateFactory;
use Dhii\Output\TemplateFactoryInterface;

class WpBookingsFrontUi extends AbstractBaseModule
{
    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /**
     * Template factory for creating template.
     *
     * @since [*next-version*]
     *
     * @var TemplateFactoryInterface
     */
    protected $templateFactory;

    /**
     * Is wizard assets attached already.
     *
     * @since [*next-version*]
     *
     * @var bool
     */
    protected $isAssetsAttached = false;

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
        return $this->_setupContainer($this->_loadPhpConfigFile(RC_BOOKINGS_FRONT_UI_MODULE_CONFIG), [
            /*
             * Templates factory.
             *
             * @since [*next-version*]
             */
            'eddbk_front_template_factory' => function (ContainerInterface $c) {
                return new PlaceholderTemplateFactory(
                    'Dhii\Output\PlaceholderTemplate',
                    $c->get('bookings_front_ui/templates_config/token_start'),
                    $c->get('bookings_front_ui/templates_config/token_end'),
                    $c->get('bookings_front_ui/templates_config/token_default')
                );
            },

            /*
             * Wizard block factory.
             *
             * @since [*next-version*]
             */
            'eddbk_wizard_block_factory' => function (ContainerInterface $c) {
                return new WizardBlockFactory(
                    $c->get('eddbk_front_app_holder_template'),
                    $c->get('event_manager'),
                    $c->get('event_factory')
                );
            },

            /*
             * Template for app holder.
             *
             * @since [*next-version*]
             */
            'eddbk_front_app_holder_template' => function (ContainerInterface $c) {
                return $this->_makeTemplate('application-holder.html');
            },

            /*
             * Template that holds all general components templates.
             *
             * @since [*next-version*]
             */
            'eddbk_front_general_components_templates' => function (ContainerInterface $c) {
                return $this->_makeTemplate('components/general-components-templates.html');
            },

            /*
             * Template that holds all components templates.
             *
             * @since [*next-version*]
             */
            'eddbk_front_components_templates' => function (ContainerInterface $c) {
                return $this->_makeTemplate('components/index.html');
            },

            /*
             * Main wizard template.
             *
             * @since [*next-version*]
             */
            'eddbk_front_wizard_template' => function (ContainerInterface $c) {
                return $this->_makeTemplate('components/eddbk-wizard.html');
            },

            /*
             * Template for confirmation step.
             *
             * @since [*next-version*]
             */
            'eddbk_front_confirmation_step_template' => function (ContainerInterface $c) {
                return $this->_makeTemplate('components/wizard-confirmation-step.html');
            },

            /*
             * Template for selecting session step.
             *
             * @since [*next-version*]
             */
            'eddbk_front_session_step_template' => function (ContainerInterface $c) {
                return $this->_makeTemplate('components/wizard-session-step.html');
            },

            /*
             * Template for selecting service.
             *
             * @since [*next-version*]
             */
            'eddbk_front_service_step_template' => function (ContainerInterface $c) {
                return $this->_makeTemplate('components/wizard-service-step.html');
            },

            /*
             * Template for session picker sub-components.
             *
             * @since [*next-version*]
             */
            'eddbk_front_session_selector_template' => function (ContainerInterface $c) {
                return $this->_makeTemplate('components/session-selector.html');
            },

            /*
             * Handling templates output for application.
             *
             * @since [*next-version*]
             */
            'eddbk_wizard_components_templates' => function (ContainerInterface $c) {
                return $c->get('eddbk_front_components_templates')->render([
                    'eddbkWizardTemplate'            => $c->get('eddbk_front_wizard_template')->render(),
                    'sessionSelectorTemplate'        => $c->get('eddbk_front_session_selector_template')->render(),
                    'wizardServiceStepTemplate'      => $c->get('eddbk_front_service_step_template')->render(),
                    'wizardSessionStepTemplate'      => $c->get('eddbk_front_session_step_template')->render(),
                    'wizardConfirmationStepTemplate' => $c->get('eddbk_front_confirmation_step_template')->render(),
                    'generalComponentsTemplates'     => $c->get('eddbk_front_general_components_templates')->render([
                        'timezoneOptions' => $this->_renderTimezoneOptions(),
                    ]),
                ]);
            },

            'eddbk_wizard_assets_urls_map' => function (ContainerInterface $c) {
                $containerFactory = $this->_getContainerFactory();
                $assetsUrlsMap = require_once $c->get('bookings_front_ui/assets_urls_map_path');

                return $containerFactory->make([
                    ContainerFactoryInterface::K_DATA => $assetsUrlsMap,
                ]);
            },

            'eddbk_wizard_assets' => function (ContainerInterface $c) {
                return $c->get('bookings_front_ui/assets');
            },

            /*
             * Handles assets enqueuing for application.
             *
             * @since [*next-version*]
             */
            'eddbk_wizard_enqueue_assets_handler' => function (ContainerInterface $c) {
                return new AssetsEnqueueHandler($c->get('eddbk_wizard_assets_urls_map'), $c->get('eddbk_wizard_assets'));
            },

            /*
             * Handles state output for client application.
             *
             * @since [*next-version*]
             */
            'eddbk_wizard_enqueue_app_state_handler' => function (ContainerInterface $c) {
                return new StateEnqueueHandler(
                    $c->get('bookings_front_ui/state_var_name'),
                    $c->get('bookings_front_ui/application_selector'),
                    $c->get('bookings_front_ui/api_endpoint_urls'),
                    $c->get('bookings_front_ui/booking_data_map'),
                    $c->get('bookings_front_ui/initial_booking_transition'),
                    $c->get('bookings_front_ui/formats/datetime'),
                    $c->get('eddbk_rest_api_wp_client_app_nonce'),
                    $c->get('event_manager'),
                    $c->get('event_factory')
                );
            },
        ]);
    }

    /**
     * Create template instance from file.
     *
     * @since [*next-version*]
     *
     * @param string $templateFile Template file for reading.
     *
     * @return TemplateInterface The created template.
     */
    protected function _makeTemplate($templateFile)
    {
        $templatePath = RC_BOOKINGS_FRONT_UI_TEMPLATES_DIR . DIRECTORY_SEPARATOR . $templateFile;

        $templateContent = file_get_contents($templatePath);

        return $this->templateFactory->make([
            TemplateFactoryInterface::K_TEMPLATE => $templateContent,
        ]);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function run(ContainerInterface $c = null)
    {
        $this->templateFactory = $c->get('eddbk_front_template_factory');

        $this->_attach('before_block_render_wizard', function (EventInterface $event) use ($c) {
            if ($this->isAssetsAttached) {
                return;
            }

            $assetsHandler = $c->get('eddbk_wizard_enqueue_assets_handler');
            $appStateHandler = $c->get('eddbk_wizard_enqueue_app_state_handler');

            $assetsHandler($event);
            $appStateHandler($event);

            $this->_attach('wp_footer', function () use ($c) {
                echo $c->get('eddbk_wizard_components_templates');
            });

            $this->isAssetsAttached = true;
        });
    }

    /**
     * Get HTML of all timezones.
     *
     * @since [*next-version*]
     *
     * @return string HTML containing list of timezones in `option` tags.
     */
    protected function _renderTimezoneOptions()
    {
        return wp_timezone_choice('UTC+0', get_user_locale());
    }
}
