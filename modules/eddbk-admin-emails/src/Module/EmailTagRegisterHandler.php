<?php

namespace RebelCode\EddBookings\Emails\Module;

use Dhii\Data\Container\ContainerAwareTrait;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInternalExceptionCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\Exception\CreateOutOfRangeExceptionCapableTrait;
use Dhii\Exception\CreateRuntimeExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Invocation\InvocableInterface;
use Dhii\Util\Normalization\NormalizeIterableCapableTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Psr\Container\ContainerInterface;
use RebelCode\Modular\Events\EventsFunctionalityTrait;
use stdClass;
use Traversable;

/**
 * Handler that registers the EDD email tags and their handlers with EDD.
 *
 * @since [*next-version*]
 */
class EmailTagRegisterHandler implements InvocableInterface
{
    /* @since [*next-version*] */
    use ContainerAwareTrait;

    /* @since [*next-version*] */
    use ContainerGetCapableTrait;

    /* @since [*next-version*] */
    use NormalizeKeyCapableTrait;

    /* @since [*next-version*] */
    use NormalizeStringCapableTrait;

    /* @since [*next-version*] */
    use NormalizeIterableCapableTrait;

    /* @since [*next-version*] */
    use EventsFunctionalityTrait;

    /* @since [*next-version*] */
    use CreateContainerExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateNotFoundExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateInvalidArgumentExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateOutOfRangeExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateInternalExceptionCapableTrait;

    /* @since [*next-version*] */
    use CreateRuntimeExceptionCapableTrait;

    /* @since [*next-version*] */
    use StringTranslatingTrait;

    /**
     * The config for the email tags.
     *
     * @since [*next-version*]
     *
     * @var array|stdClass|Traversable
     */
    protected $tagsConfig;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $tagsConfig The list of configs for the email tags to register.
     * @param ContainerInterface         $container  The services container, for resolving tag handlers.
     */
    public function __construct($tagsConfig, ContainerInterface $container)
    {
        $this->_setTagsConfig($tagsConfig);
        $this->_setContainer($container);
    }

    /**
     * Retrieves the config for the email tags.
     *
     * @since [*next-version*]
     *
     * @return array|stdClass|Traversable
     */
    protected function _getTagsConfig()
    {
        return $this->tagsConfig;
    }

    /**
     * Sets the config for the email tags.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|Traversable $tagsConfig The config for the email tags.
     */
    protected function _setTagsConfig($tagsConfig)
    {
        $this->tagsConfig = $this->_normalizeIterable($tagsConfig);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function __invoke()
    {
        foreach ($this->_getTagsConfig() as $_tag) {
            \edd_add_email_tag(
                $this->_containerGet($_tag, 'tag'),
                $this->__($this->_containerGet($_tag, 'description')),
                $this->_getContainer()->get($this->_containerGet($_tag, 'handler'))
            );
        }
    }
}
