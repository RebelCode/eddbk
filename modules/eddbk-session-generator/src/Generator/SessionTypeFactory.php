<?php

namespace RebelCode\EddBookings\Sessions\Generator;

use ArrayAccess;
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
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;

/**
 * A factory for creating session types.
 *
 * This implementation delegates the creation of the session types to other factories, according to the type of
 * session type being created. The `type` is extracted from the config passed to {@link make()} and is used to
 * retrieve the corresponding child factory. The resulting session type instance is created using this child factory.
 *
 * @since [*next-version*]
 */
class SessionTypeFactory implements FactoryInterface
{
    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

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
     * The key from which to read the session type data from the config.
     *
     * @since [*next-version*]
     */
    const K_DATA = 'data';

    /**
     * The key from which the session type is read from config.
     *
     * @since [*next-version*]
     */
    const K_TYPE = 'type';

    /**
     * The factories, keyed by session type.
     *
     * @since [*next-version*]
     *
     * @var FactoryInterface[]|stdClass|ArrayAccess|ContainerInterface
     */
    protected $factories;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param FactoryInterface[]|stdClass|ArrayAccess|ContainerInterface $factories The factories to use for making
     *                                                                              session types, keyed by the type
     *                                                                              that they create.
     */
    public function __construct($factories)
    {
        $this->factories = $this->_normalizeContainer($factories);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function make($config = null)
    {
        try {
            $type = $this->_containerGet($config, static::K_TYPE);
        } catch (NotFoundExceptionInterface $exception) {
            throw $this->_createCouldNotMakeException(
                $this->__('A session "%s" must be specified in the factory config', [static::K_TYPE]),
                null, $exception, $this, $config
            );
        }

        try {
            $factory = $this->_containerGet($this->factories, $type);
        } catch (NotFoundExceptionInterface $exception) {
            throw $this->_createCouldNotMakeException(
                $this->__('Could not find session type factory for type "%s"', [$type]),
                null, $exception, $this, $config
            );
        }

        $data = $this->_containerGet($config, static::K_DATA);

        return $factory->make($data);
    }
}
