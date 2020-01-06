<?php

namespace RebelCode\EddBookings\Sessions\Generator;

use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeContainerCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Factory\Exception\CreateCouldNotMakeExceptionCapableTrait;
use Dhii\Factory\Exception\CreateFactoryExceptionCapableTrait;
use Dhii\Factory\FactoryInterface;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeArrayCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Psr\Container\NotFoundExceptionInterface;
use RebelCode\Bookings\Sessions\FixedDurationSessionType;

/**
 * A factory that creates fixed duration session type instances.
 *
 * This implementation creates fixed duration session types that adds resource IDs to created sessions. The resource
 * IDs to add to the sessions may be passed in the config for {@link make()}.
 *
 * @since [*next-version*]
 */
class FixedDurationSessionTypeFactory implements FactoryInterface
{
    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use NormalizeArrayCapableTrait;

    /* @since [*next-version*] */
    use NormalizeContainerCapableTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateFactoryExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateCouldNotMakeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The key in the factory config from where to read the session type duration.
     *
     * @since [*next-version*]
     */
    const K_DURATION = 'duration';

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function make($config = null)
    {
        try {
            $duration = $this->_containerGet($config, static::K_DURATION);
        } catch (NotFoundExceptionInterface $exception) {
            throw $this->_createCouldNotMakeException(
                $this->__('A "duration" must be specified in the factory config under the "data" key'),
                null, $exception, $this, $config
            );
        }

        return new FixedDurationSessionType($duration);
    }
}
