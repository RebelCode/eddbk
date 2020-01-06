<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateRuntimeExceptionCapableTrait;
use Dhii\Exception\RuntimeException;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Transformer\TransformerInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeIntCapableTrait;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use Psr\EventManager\EventInterface;
use RebelCode\Entity\EntityManagerInterface;
use stdClass;
use Traversable;

/**
 * Handler for transforming shortcode parameters in format required by the front ui application.
 *
 * @since [*next-version*]
 */
class ShortcodeParametersTransformHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIntCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use CreateRuntimeExceptionCapableTrait;

    /**
     * Cart page ID.
     *
     * @since [*next-version*]
     *
     * @var int
     */
    protected $cartPageId;

    /**
     * Services entity manager.
     *
     * @since [*next-version*]
     *
     * @var EntityManagerInterface
     */
    private $servicesEntityManager;

    /**
     * Service transformer.
     *
     * @since [*next-version*]
     *
     * @var TransformerInterface
     */
    private $serviceTransformer;

    /**
     * MainComponentHandler constructor.
     *
     * @since [*next-version*]
     *
     * @param int|string|Stringable|float $cartPageId            Cart page ID.
     * @param EntityManagerInterface      $servicesEntityManager Services entity manager.
     * @param TransformerInterface        $serviceTransformer    Service transformer.
     */
    public function __construct($cartPageId, $servicesEntityManager, $serviceTransformer)
    {
        $this->cartPageId            = $this->_normalizeInt($cartPageId);
        $this->servicesEntityManager = $servicesEntityManager;
        $this->serviceTransformer    = $serviceTransformer;
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

        $event->setParams($this->_handleParameters($event->getParams()));
    }

    /**
     * Render booking holder.
     *
     * @since [*next-version*]
     *
     * @param array $params List of shortcode parameters.
     *
     * @return array Prepared parameters for wizard.
     */
    protected function _handleParameters($params = [])
    {
        $params['redirectUrl'] = $this->_getRedirectUrl();

        if (isset($params['service'])) {
            $service = $this->_getService((int) $params['service']);
            unset($params['service']);
            if ($service) {
                $params['service'] = $this->_normalizeArray($service);
            }
        }

        return $params;
    }

    /**
     * Get cart URL on which customer will be redirected after successful booking creation.
     *
     * @since [*next-version*]
     *
     * @throws RuntimeException If page post with cart page id doesn't exist.
     *
     * @return string Cart URL to redirect user on.
     */
    protected function _getRedirectUrl()
    {
        $pageUrl = get_permalink($this->cartPageId);

        if ($pageUrl === false) {
            throw $this->_createRuntimeException(
                $this->__('Page post with ID "%1$d" does not exist.', [$this->cartPageId])
            );
        }

        return $pageUrl;
    }

    /**
     * Get service by service ID.
     *
     * @since [*next-version*]
     *
     * @param int $serviceId Service ID.
     *
     * @return array|stdClass|Traversable|null Service data if service is found, `null` if service is not found.
     */
    protected function _getService($serviceId)
    {
        $service = $this->servicesEntityManager->get($serviceId);

        return $service ? $this->_normalizeIterable($this->serviceTransformer->transform($service)) : null;
    }
}
