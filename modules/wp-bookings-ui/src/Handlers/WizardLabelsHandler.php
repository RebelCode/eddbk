<?php

namespace RebelCode\Bookings\WordPress\Module\Handlers;

use Dhii\Collection\MapInterface;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use RebelCode\Bookings\WordPress\Module\IteratorToArrayRecursiveCapableTrait;
use stdClass;
use Traversable;

/**
 * Handler for normalizing and translating wizard labels.
 *
 * @since [*next-version*]
 */
class WizardLabelsHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /* @since [*next-version*] */
    use IteratorToArrayRecursiveCapableTrait;

    /**
     * List of default labels.
     *
     * @since [*next-version*]
     *
     * @var array|Traversable|stdClass
     */
    protected $defaultLabels;

    /**
     * WizardLabelsHandler constructor.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|MapInterface $defaultLabels Default front application labels.
     */
    public function __construct($defaultLabels)
    {
        $this->defaultLabels = $defaultLabels;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        return $this->_iteratorToArrayRecursive($this->defaultLabels, function ($value) {
            return is_string($value) ? $this->_translate($value) : $value;
        });
    }
}
