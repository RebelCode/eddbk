<?php

namespace RebelCode\EddBookings\Logic\Module;

use Dhii\Validation\AbstractCompositeValidatorBase;
use Dhii\Validation\ValidatorInterface;
use stdClass;
use Traversable;

/**
 * A booking validator implementation.
 *
 * @since [*next-version*]
 */
class BookingValidator extends AbstractCompositeValidatorBase
{
    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param ValidatorInterface[]|stdClass|Traversable $ruleValidators A list of rule validators.
     */
    public function __construct($ruleValidators)
    {
        $this->_setChildValidators($ruleValidators);
    }
}
