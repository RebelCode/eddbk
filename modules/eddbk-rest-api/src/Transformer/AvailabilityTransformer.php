<?php

namespace RebelCode\EddBookings\RestApi\Transformer;

use ArrayIterator;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeContainerCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Transformer\TransformerInterface;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use IteratorIterator;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;
use Traversable;

/**
 * An implementation of a full availability transformer.
 *
 * This differs from {@link ServiceAvailabilityTransformer} in that it transforms a full availability, rather than a
 * cut-down service-specific version of an availability.
 *
 * @since [*next-version*]
 */
class AvailabilityTransformer implements TransformerInterface
{
    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /* @since [*next-version*] */
    use NormalizeContainerCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The transformer to use for the availability's rules.
     *
     * @since [*next-version*]
     *
     * @var TransformerInterface
     */
    protected $ruleTransformer;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param TransformerInterface $ruleTransformer The transformer to use for the availability's rules.
     */
    public function __construct(TransformerInterface $ruleTransformer)
    {
        $this->ruleTransformer = $ruleTransformer;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function transform($source)
    {
        $container = $this->_normalizeContainer($source);

        try {
            $rules    = $this->_containerGet($container, 'rules');
        } catch (NotFoundExceptionInterface $exception) {
            $rules = [];
        }

        try {
            $timezone = $this->_containerGet($container, 'timezone');
        } catch (NotFoundExceptionInterface $exception) {
            $timezone = 'UTC';
        }

        return [
            'rules'    => $this->_transformRules($rules, $this->ruleTransformer),
            'timezone' => $timezone,
        ];
    }

    /**
     * Transforms all the rules in a given list.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $rules       The list of rules to transform.
     * @param TransformerInterface       $transformer The transformer to use for each rule.
     *
     * @return array The transformed rules.
     */
    protected function _transformRules($rules, TransformerInterface $transformer)
    {
        $result = [];

        foreach ($rules as $_rule) {
            $result[] = $transformer->transform($_rule);
        }

        return $result;
    }
}
