<?php

namespace RebelCode\EddBookings\RestApi\Transformer;

use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Transformer\TransformerInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;

/**
 * A transformer that transformers the data for session types.
 *
 * @since [*next-version*]
 */
class SessionTypeDataTransformer implements TransformerInterface
{
    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The transformer for transforming resource IDs into full resource data.
     *
     * @since [*next-version*]
     *
     * @var TransformerInterface
     */
    protected $resourcesT9r;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param TransformerInterface $resourcesT9r The transformer for transforming resource IDs into full resource data.
     */
    public function __construct(TransformerInterface $resourcesT9r)
    {
        $this->resourcesT9r = $resourcesT9r;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function transform($source)
    {
        $result = $this->_normalizeArray($source);

        $result['resources'] = isset($result['resources'])
            ? $result['resources']
            : [];

        foreach ($result['resources'] as $_key => $resourceId) {
            $_transformed = $this->resourcesT9r->transform($resourceId);
            if ($_transformed !== null) {
                $result['resources'][$_key] = $_transformed;
            }
        }

        return $result;
    }
}
