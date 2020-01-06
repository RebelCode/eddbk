<?php

namespace RebelCode\Bookings\WordPress\Module;

use ArrayIterator;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Iterator\NormalizeIteratorCapableTrait;
use Dhii\Transformer\TransformerInterface;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use IteratorIterator;
use RebelCode\Transformers\TransformerIterator;
use Iterator;
use Traversable;

/**
 * A transformer for transforming lists of services.
 *
 * @since [*next-version*]
 */
class ServiceListTransformer implements TransformerInterface
{
    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIteratorCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The transformer for a single service.
     *
     * @since [*next-version*]
     *
     * @var TransformerInterface
     */
    protected $serviceT9r;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param TransformerInterface $serviceT9r The transformer for a single service.
     */
    public function __construct(TransformerInterface $serviceT9r)
    {
        $this->serviceT9r = $serviceT9r;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function transform($source)
    {
        $sourceIterator = $this->_normalizeIterator($source);
        $transIterator  = $this->_createTransformerIterator($sourceIterator, $this->serviceT9r);

        return $this->_normalizeArray($transIterator);
    }

    /**
     * Creates a transformer iterator.
     *
     * @since [*next-version*]
     *
     * @param Iterator             $iterator    The iterator.
     * @param TransformerInterface $transformer The transformer.
     *
     * @return TransformerIterator The created transformer iterator instance.
     */
    protected function _createTransformerIterator(Iterator $iterator, TransformerInterface $transformer)
    {
        return new TransformerIterator($iterator, $transformer);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _createArrayIterator(array $array)
    {
        return new ArrayIterator($array);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _createTraversableIterator(Traversable $traversable)
    {
        return new IteratorIterator($traversable);
    }
}
