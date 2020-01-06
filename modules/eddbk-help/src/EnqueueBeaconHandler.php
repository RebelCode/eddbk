<?php

namespace RebelCode\EddBookings\Help;

use Dhii\Invocation\InvocableInterface;
use Dhii\Util\String\StringableInterface as Stringable;

/**
 * The handler that enqueues the Beacon JS files.
 *
 * @since [*next-version*]
 */
class EnqueueBeaconHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use GetEddBkPageIdCapableTrait;

    /**
     * The URL to the Beacon JS file.
     *
     * @since [*next-version*]
     *
     * @var string|Stringable
     */
    protected $beaconJsUrl;

    /**
     * The URL to the Beacon CSS file.
     *
     * @since [*next-version*]
     *
     * @var string|Stringable
     */
    protected $beaconCssUrl;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $beaconJsUrl  The URL to the Beacon JS file.
     * @param string|Stringable $beaconCssUrl The URL to the Beacon CSS file.
     */
    public function __construct($beaconJsUrl, $beaconCssUrl)
    {
        $this->beaconJsUrl  = $beaconJsUrl;
        $this->beaconCssUrl = $beaconCssUrl;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        if ($this->_getEddBkPageId()) {
            // Enqueue beacon scripts
            wp_enqueue_script('eddbk_beacon_js', $this->beaconJsUrl);
            // Enqueue beacon styles
            wp_enqueue_style('eddbk_beacon_js', $this->beaconCssUrl);
        }
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _wpGetCurrentScreen()
    {
        return \get_current_screen();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _wpIsAdmin()
    {
        return \is_admin();
    }
}
