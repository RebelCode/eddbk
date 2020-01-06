<?php

namespace RebelCode\EddBookings\RestApi\Module;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Util\String\StringableInterface as Stringable;
use Psr\EventManager\EventInterface;
use WP_REST_Request;

/**
 * The handler that verifies a nonce in the header of requests and authorizes the client if valid.
 *
 * @since [*next-version*]
 */
class TransientNonceAuthHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The name of the header from where to get the nonce.
     *
     * @since [*next-version*]
     *
     * @var string|Stringable
     */
    protected $header;

    /**
     * The name of the nonce to verify.
     *
     * @since [*next-version*]
     *
     * @var string|Stringable
     */
    protected $nonce;

    /**
     * The key of the event param to filter.
     *
     * @since [*next-version*]
     *
     * @var string|Stringable
     */
    protected $paramKey;

    /**
     * The name of the transient.
     *
     * @since [*next-version*]
     *
     * @var string|Stringable
     */
    protected $transientName;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable $header        The name of the header from where to get the nonce.
     * @param string|Stringable $nonce         The name of the nonce to verify.
     * @param string|Stringable $paramKey      The key of the event param to filter.
     * @param string|Stringable $transientName The name of the transient.
     */
    public function __construct($header, $nonce, $paramKey, $transientName)
    {
        $this->header        = $header;
        $this->nonce         = $nonce;
        $this->paramKey      = $paramKey;
        $this->transientName = $transientName;
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

        $request = $event->getParam('request');
        if (!($request instanceof WP_REST_Request)) {
            throw $this->_createOutOfRangeException(
                $this->__('Request in event is not a valid request instance'), null, null, $request
            );
        }

        $nonce = $request->get_header($this->header);
        $valid = (bool) $this->_verifyNonce($nonce, $this->nonce);

        $event->setParams([$this->paramKey => $valid] + $event->getParams());
    }

    /**
     * Verifies that correct nonce was used and within its time limit.
     *
     * @since [*next-version*]
     *
     * @param string $nonce Nonce that was used in the request to verify.
     * @param string $name  Should give context to what is taking place and be the same when nonce was created.
     *
     * @return bool|int False if the nonce is invalid, 1 if the nonce is valid and generated between 0-12 hours ago,
     *                  2 if the nonce is valid and generated between 12-24 hours ago.
     */
    protected function _verifyNonce($nonce, $name)
    {
        $expected = (string) get_transient($this->transientName);
        $actual   = (string) $nonce;

        return $expected === $actual;
    }
}
