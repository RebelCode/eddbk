<?php

namespace RebelCode\EddBookings\RestApi\Auth;

use AppendIterator;
use ArrayIterator;
use Dhii\Iterator\NormalizeIteratorCapableTrait;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Validation\AbstractValidatorBase;
use Dhii\Validation\ChildValidatorsAwareTrait;
use Dhii\Validation\Exception\ValidationFailedExceptionInterface;
use Dhii\Validation\ValidatorInterface;
use Iterator;
use IteratorIterator;
use stdClass;
use Traversable;

/**
 * An implementation of a tolerant composite validator.
 *
 * When a child validator successfully validates the subject, validation is successful and no other child validators
 * are invoked. Validation errors are returned only if all validators fail.
 * Therefore, these validators are said to tolerate any validation errors until and if all validators fail.
 *
 * @since [*next-version*]
 */
class TolerantCompositeValidator extends AbstractValidatorBase
{
    /*
     * Adds iterator normalization functionality.
     *
     * @since [*next-version*]
     */
    use NormalizeIteratorCapableTrait;

    /* Awareness of child validators.
     *
     * @since [*next-version*]
     */
    use ChildValidatorsAwareTrait;

    /* Normalization for iterables.
     *
     * @since [*next-version*]
     */
    use NormalizeIterableCapableTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ValidatorInterface[]|stdClass|Traversable $validators A list of validators.
     */
    public function __construct($validators)
    {
        $this->_setChildValidators($validators);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _getValidationErrors($subject)
    {
        $errors = [];

        foreach ($this->_getChildValidators() as $_idx => $_validator) {
            try {
                // Check if item is a validator instance - if not, throw
                if (!($_validator instanceof ValidatorInterface)) {
                    throw $this->_createOutOfRangeException(
                        $this->__('Validator %1$s is invalid', [$_idx]),
                        null,
                        null,
                        $_validator
                    );
                }

                // Invoke validator
                $_validator->validate($subject);
                // On success, return no errors
                return [];
            } catch (ValidationFailedExceptionInterface $e) {
                // If validator failed, set errors
                $errors[] = $e->getValidationErrors();
            }
        }

        return $this->_normalizeErrorList($errors);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _normalizeErrorList($errorList)
    {
        $listIterator = new AppendIterator();
        foreach ($errorList as $_error) {
            $listIterator->append($this->_normalizeIterator($_error));
        }

        return $listIterator;
    }

    /**
     * Creates an iterator that will iterate over the given array.
     *
     * @param array $array The array to create an iterator for.
     *
     * @since [*next-version*]
     *
     * @return Iterator The iterator that will iterate over the array.
     */
    protected function _createArrayIterator($array)
    {
        return new ArrayIterator($array);
    }

    /**
     * Creates an iterator that will iterate over the given traversable.
     *
     * @param Traversable $traversable The traversable to create an iterator for.
     *
     * @since [*next-version*]
     *
     * @return Iterator The iterator that will iterate over the traversable.
     */
    protected function _createTraversableIterator($traversable)
    {
        return new IteratorIterator($traversable);
    }
}
