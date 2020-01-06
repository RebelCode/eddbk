<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers;

use Dhii\Cache\ContainerInterface;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Psr\EventManager\EventInterface;

/**
 * Handles assets enqueuing of client application.
 *
 * @since [*next-version*]
 */
class AssetsEnqueueHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /**
     * Container of assets urls.
     *
     * @since [*next-version*]
     *
     * @var ContainerInterface
     */
    protected $assetsUrlMap;

    /**
     * Container of application's assets.
     *
     * @since [*next-version*]
     *
     * @var ContainerInterface
     */
    protected $assets;

    /**
     * AssetsEnqueueHandler constructor.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $assetsUrlMap Container of assets urls.
     * @param ContainerInterface $assets       Container of application's assets.
     */
    public function __construct($assetsUrlMap, $assets)
    {
        $this->assetsUrlMap = $assetsUrlMap;
        $this->assets       = $assets;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        /* @var $event EventInterface */
        $event = func_get_arg(0);

        if (!($event instanceof EventInterface)) {
            throw $this->_createInvalidArgumentException(
                $this->__('Argument is not an event instance'), null, null, $event
            );
        }

        $this->_enqueueAssets($this->assetsUrlMap, $this->assets);
    }

    /**
     * Enqueue application's assets.
     *
     * @since [*next-version*]
     *
     * @param ContainerInterface $assetsUrlMap Container of assets urls.
     * @param ContainerInterface $assets       Container of application's assets.
     */
    protected function _enqueueAssets($assetsUrlMap, $assets)
    {
        /*
         * All application components located here
         */
        wp_enqueue_script('eddbk-wizard-app', $assetsUrlMap->get(
            $assets->get('wizard/app.min.js')
        ), [], false, true);

        /*
         * Enqueue all styles from assets URL map
         */
        $stylesMap = [];
        foreach ($assets->get('styles') as $styleDependency) {
            $stylesMap[] = $assetsUrlMap->get($styleDependency);
        }
        wp_localize_script('eddbk-wizard-app', 'EDDBK_WIZARD_REQUIRE_STYLES', $stylesMap);
    }
}
