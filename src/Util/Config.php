<?php

namespace RebelCode\EddBookings\Core\Util;

use ArrayAccess;
use Dhii\Data\Container\ContainerAwareTrait;
use Dhii\Data\Container\ContainerGetCapableTrait;
use Dhii\Data\Container\ContainerHasCapableTrait;
use Dhii\Data\Container\CreateContainerExceptionCapableTrait;
use Dhii\Data\Container\CreateNotFoundExceptionCapableTrait;
use Dhii\Data\Container\NormalizeContainerCapableTrait;
use Dhii\Data\Object\DataStoreAwareContainerTrait;
use Dhii\Data\Object\GetDataCapableTrait;
use Dhii\Data\Object\HasDataCapableTrait;
use Dhii\Data\Object\NormalizeKeyCapableTrait;
use Dhii\Exception\CreateInvalidArgumentExceptionCapableTrait;
use Dhii\I18n\StringTranslatingTrait;
use Dhii\Util\Normalization\NormalizeStringCapableTrait;
use Dhii\Util\String\StringableInterface as Stringable;
use InvalidArgumentException;
use IteratorAggregate;
use Psr\Container\ContainerInterface;
use Psr\Container\NotFoundExceptionInterface;
use stdClass;
use Traversable;

/**
 * A container that recognizes hierarchy, can retrieve data using paths and can replace values.
 *
 * @since [*next-version*]
 */
class Config implements
    /* @since [*next-version*] */
    ContainerInterface,
    /* @since [*next-version*] */
    IteratorAggregate
{
    /*
     * @since [*next-version*]
     */
    use DataStoreAwareContainerTrait;

    /*
     * @since [*next-version*]
     */
    use GetDataCapableTrait;

    /*
     * @since [*next-version*]
     */
    use HasDataCapableTrait;

    /*
     * @since [*next-version*]
     */
    use ContainerAwareTrait;

    /*
     * @since [*next-version*]
     */
    use ContainerGetCapableTrait;

    /*
     * @since [*next-version*]
     */
    use ContainerHasCapableTrait;

    /*
     * @since [*next-version*]
     */
    use NormalizeKeyCapableTrait;

    /*
     * @since [*next-version*]
     */
    use NormalizeStringCapableTrait;

    /*
     * @since [*next-version*]
     */
    use NormalizeContainerCapableTrait;

    /*
     * @since [*next-version*]
     */
    use CreateInvalidArgumentExceptionCapableTrait;

    /*
     * @since [*next-version*]
     */
    use CreateContainerExceptionCapableTrait;

    /*
     * @since [*next-version*]
     */
    use CreateNotFoundExceptionCapableTrait;

    /*
     * @since [*next-version*]
     */
    use StringTranslatingTrait;

    /**
     * Constructor.
     *
     * @since [*next-version*]
     *
     * @param array|stdClass|ArrayAccess|ContainerInterface|null $data   The config data.
     * @param ContainerInterface|null                            $parent The parent container. Will be used to retrieve
     *                                                                   replacement values, if provided.
     */
    public function __construct($data, ContainerInterface $parent = null)
    {
        $this->_setDataStore($data);
        $this->_setContainer($parent ? $parent : $this);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function getIterator()
    {
        return $this->_getDataStore();
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function get($key)
    {
        $path     = $this->_parsePath($key);
        $data     = $this->_getDataStore();
        $previous = '';

        do {
            $head = array_shift($path);

            try {
                $data = $this->_containerGet($data, $head);
            } catch (InvalidArgumentException $invalidArgumentException) {
                throw $this->_createNotFoundException(
                    $this->__('Config node "%1$s" in path "%2$s" is not a valid container', [$previous, $key]),
                    null, $invalidArgumentException, $this, $key
                );
            } catch (NotFoundExceptionInterface $notFoundException) {
                throw $this->_createNotFoundException(
                    $this->__('Config value for path "%1$s" was not found', [$key]),
                    null, $notFoundException, $this, $key
                );
            }
            $previous = $head;
        } while (count($path) > 0);

        return $this->_prepareConfigValue($data);
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    public function has($key)
    {
        $path = $this->_parsePath($key);
        $data = $this->_getDataStore();

        try {
            do {
                $head = array_shift($path);
                $data = $this->_containerGet($data, $head);
            } while (count($path) > 0);
        } catch (NotFoundExceptionInterface $notFoundException) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @since [*next-version*]
     */
    protected function _parsePath($key)
    {
        $nKey  = $this->_normalizeString($key);
        $parts = explode('/', $nKey);

        return $parts;
    }

    /**
     * Prepares a config value, making any necessary changes prior to it being given to a consumer.
     *
     * @since [*next-version*]
     *
     * @param mixed $subject The subject value.
     *
     * @return mixed The prepared config value.
     */
    protected function _prepareConfigValue($subject)
    {
        if (is_array($subject) || $subject instanceof stdClass || $subject instanceof Traversable) {
            $result = [];
            foreach ($subject as $_k => $_v) {
                $result[$_k] = $this->_prepareConfigValue($_v);
            }

            return $result;
        }

        if (is_string($subject) || $subject instanceof Stringable) {
            return $this->_replaceStringValue($subject, $this->_getContainer());
        }

        return $subject;
    }

    /**
     * Replaces all tokens in the form `${key}` in a string with corresponding values retrieved from a container.
     *
     * @since [*next-version*]
     *
     * @param string|Stringable                             $subject      The subject string.
     * @param array|stdClass|ArrayAccess|ContainerInterface $replacements The container of replacement values.
     *
     * @return string The resulting string.
     */
    protected function _replaceStringValue($subject, $replacements)
    {
        $subject = $this->_normalizeString($subject);
        preg_match_all('/\${[^\}]+}/', $subject, $matches);
        $tokens = $matches[0];

        foreach ($tokens as $_token) {
            $_image  = substr($_token, 2, - 1);
            $_value  = $this->_containerGet($replacements, $_image);
            $subject = str_replace($_token, $_value, $subject);
        }

        return $subject;
    }
}
