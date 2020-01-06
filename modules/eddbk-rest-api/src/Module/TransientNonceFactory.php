<?php

namespace RebelCode\EddBookings\RestApi\Module;

use RebelCode\WordPress\Nonce\Factory\AbstractNonceFactory;
use RebelCode\WordPress\Nonce\Nonce;

/**
 * A nonce factory that also stored the created nonce in a transient.
 *
 * @since [*next-version*]
 */
class TransientNonceFactory extends AbstractNonceFactory
{
    /**
     * The name of the transient.
     *
     * @since [*next-version*]
     *
     * @var string
     */
    protected $transientName;

    /**
     * The expiry time of the transient, in seconds.
     *
     * @since [*next-version*]
     *
     * @var int
     */
    protected $transientExpiry;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string $transientName   The name of the transient.
     * @param int    $transientExpiry The expiry time of the transient, in seconds.
     */
    public function __construct($transientName, $transientExpiry)
    {
        $this->transientName   = $transientName;
        $this->transientExpiry = $transientExpiry;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function make($config = [])
    {
        return $this->_make($config);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _createNonceInstance($id, $code)
    {
        return new Nonce($id, $code);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _generateNonceCode($id)
    {
        $code = \wp_create_nonce($id);

        set_transient($this->transientName, $code, $this->transientExpiry);

        return $code;
    }
}
