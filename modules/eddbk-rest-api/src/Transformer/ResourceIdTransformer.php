<?php

namespace RebelCode\EddBookings\RestApi\Transformer;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Transformer\TransformerInterface;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Psr\Container\NotFoundExceptionInterface;
use RebelCode\Entity\GetCapableManagerInterface;

/**
 * A transformer that transforms a resource ID into the full transformed data for that resource.
 *
 * @since [*next-version*]
 */
class ResourceIdTransformer implements TransformerInterface
{
    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The transformer for transforming resources.
     *
     * @since [*next-version*]
     *
     * @var TransformerInterface
     */
    protected $resourceT9r;

    /**
     * The entity manager to retrieving resources.
     *
     * @since [*next-version*]
     *
     * @var GetCapableManagerInterface
     */
    protected $resourceManager;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param TransformerInterface       $resourceT9r     The transformer for transforming resources.
     * @param GetCapableManagerInterface $resourceManager The entity manager to retrieving resources.
     */
    public function __construct(TransformerInterface $resourceT9r, GetCapableManagerInterface $resourceManager)
    {
        $this->resourceT9r     = $resourceT9r;
        $this->resourceManager = $resourceManager;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function transform($source)
    {
        try {
            $resource = $this->resourceManager->get($source);
            $result   = $this->resourceT9r->transform($resource);

            return $result;
        } catch (NotFoundExceptionInterface $exception) {
            return null;
        }
    }
}
